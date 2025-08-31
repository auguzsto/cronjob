<?php
namespace Auguzsto\Cronjob;

use DateTime;
use Auguzsto\Job\Job;
use Auguzsto\Cronjob\Process;
use Auguzsto\Cronjob\CronParser;
use Auguzsto\Cronjob\TaskInterface;
use Auguzsto\Cronjob\CronParserInterface;


class Scheduler implements SchedulerInterface
{
    private CronParserInterface $cron;
    private TaskInterface $task;

    public function __construct()
    {
    }

    public function on(string $cronExpression, TaskInterface $task, CronParserInterface $cron = new CronParser()): void
    {
        $this->cron = $cron;
        $this->cron->setExpression($cronExpression);
        $this->setTask($task);
        $this->runScheduledTask();
        $this->scheduleTask();
    }

    public function setTask(TaskInterface $taskInterface): void
    {
        $this->task = $taskInterface;
    }

    public function getTask(): TaskInterface
    {
        return $this->task;
    }

    public function scheduleTask(): void
    {
        $nextTimestamp = $this->cron->getNext();
        $datetime = new DateTime();
        $datetime->setTimestamp($nextTimestamp);
        $next = $datetime->format("Y-m-d H:i");

        $dir = __DIR__ . "/.scheduler";
        if (!is_dir($dir)) {
            mkdir($dir);
        }

        $taskclass = $this->task::class;
        $filetasks = "$dir/$taskclass";

        $schedule = [
            [
                "next" => $next,
                "process" => Process::SCHEDULED
            ]
        ];

        if (!is_file($filetasks)) {
            file_put_contents($filetasks, json_encode($schedule));
            return;
        }

        if (is_file($filetasks)) {
            $schedules = json_decode(file_get_contents($filetasks));
            $lastIndex = array_key_last($schedules);
            $lastScheduled = $schedules[$lastIndex];

            if ($lastScheduled->process == Process::SCHEDULED) {
                return;
            }

            if ($lastScheduled->process == Process::DONE) {
                array_push($schedules, [
                    "next" => $next,
                    "process" => "scheduled"
                ]);

                file_put_contents($filetasks, json_encode($schedules));
            }
        }
    }

    public function runScheduledTask(): void
    {
        $taskclass = $this->task::class;
        $filetask = __DIR__ . "/.scheduler/$taskclass";

        if (!is_file($filetask)) {
            return;
        }

        $schedules = json_decode(file_get_contents($filetask));
        $lastIndex = array_key_last($schedules);

        if ($schedules[$lastIndex]->process == Process::SCHEDULED) {
            $now = date("Y-m-d H:i");
            if ($schedules[$lastIndex]->next == $now) {
                $job = new Job($taskclass, "onTask");
                $job->execute();
                $lastIndex = array_key_last($schedules);
                $schedules[$lastIndex]->process = Process::DONE;
                file_put_contents($filetask, json_encode($schedules));
            }
            
        }
    }
}