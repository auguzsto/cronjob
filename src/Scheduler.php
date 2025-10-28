<?php
namespace Auguzsto\Cronjob;

use DateTime;
use Auguzsto\Job\Job;
use Auguzsto\Cronjob\CronParser;
use Auguzsto\Cronjob\TaskInterface;
use Auguzsto\Cronjob\CronParserInterface;


class Scheduler implements SchedulerInterface
{
    private CronParserInterface $cron;
    private TaskInterface $task;

    public function __construct(CronParserInterface $cron = new CronParser())
    {
        $this->cron = $cron;
    }

    public function on(string $cronExpression, TaskInterface $task): void
    {
        $this->cron->setExpression($cronExpression);
        $this->setTask($task);
        $this->runScheduledTask();
        $this->scheduleTask();
    }

    public function setCronParser(CronParserInterface $cronParserInterface): void
    {
        $this->cron = $cronParserInterface;
    }

    public function getCronParser(): CronParserInterface
    {
        return $this->cron;
    }

    public function setTask(TaskInterface $taskInterface): void
    {
        $this->task = $taskInterface;
    }

    public function getTask(): TaskInterface
    {
        return $this->task;
    }

    public static function all(string $task): array
    {
        $dirscheduled = SchedulerInterface::DIR;
        $agendas = "$dirscheduled/$task";

        if (!file_exists($agendas)) {
           return [];
        }

        $array = json_decode(file_get_contents($agendas));
        return $array;
    }

    public static function errors(): string | null
    {
        $erroslog = "/tmp/php-job-error.log";
        if (!file_exists($erroslog)) {
            return null;
        }

        $result = file_get_contents($erroslog);
        return $result;
    }

    public function scheduleTask(): void
    {
        $nextTimestamp = $this->cron->getNext();
        $datetime = new DateTime();
        $datetime->setTimestamp($nextTimestamp);
        $next = $datetime->format("Y-m-d H:i");
        $dir = self::DIR;

        if (!is_dir($dir)) {
            mkdir($dir);
        }

        $taskclass = $this->task::class;
        $filetasks = "$dir/$taskclass";

        if (!is_file($filetasks)) {
            $schedule = [
                [
                    "next" => $next,
                    "status" => self::STATUS_SCHEDULED
                ]
            ];
            file_put_contents($filetasks, json_encode($schedule));
            return;
        }

        $schedules = json_decode(file_get_contents($filetasks));
        $lastIndex = array_key_last($schedules);
        $lastScheduled = $schedules[$lastIndex];
        $now = date("Y-m-d H:i");

        if ($lastScheduled->status == self::STATUS_SCHEDULED && $now < $lastScheduled->next) {
            return;
        }

        if ($lastScheduled->status == self::STATUS_SCHEDULED && $now > $lastScheduled->next) {
            $schedules[$lastIndex]->status = self::STATUS_RESCHEDULED;
            array_push($schedules, [
                "next" => $next,
                "status" => self::STATUS_SCHEDULED
            ]);

            file_put_contents($filetasks, json_encode($schedules));
            return;
        }

        if ($lastScheduled->status == self::STATUS_DONE) {
            array_push($schedules, [
                "next" => $next,
                "status" => self::STATUS_SCHEDULED
            ]);

            file_put_contents($filetasks, json_encode($schedules));
        }
    }

    public function runScheduledTask(): void
    {
        $taskclass = $this->task::class;
        $dir = self::DIR;
        $filetask = "/$dir/$taskclass";

        if (!is_file($filetask)) {
            return;
        }

        $include = $_SERVER["CRONJOB_TASKS_DIR"] . "/$taskclass.php";
        $schedules = json_decode(file_get_contents($filetask));
        $lastIndex = array_key_last($schedules);
        $now = date("Y-m-d H:i");
        $isRun = $schedules[$lastIndex]->status == self::STATUS_SCHEDULED && $schedules[$lastIndex]->next == $now;

        if ($isRun) {
            $job = new Job($taskclass, "onTask");
            $job->include($include);
            $job->execute();
            $lastIndex = array_key_last($schedules);
            $schedules[$lastIndex]->status = self::STATUS_DONE;
            file_put_contents($filetask, json_encode($schedules));
        }
    }
}