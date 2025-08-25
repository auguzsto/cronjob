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

    public function __construct(string $cronExpression, TaskInterface $task)
    {
        $this->setCronParser(new CronParser($cronExpression));
        $this->setTask($task);
        $this->next();
    }

    public function setTask(TaskInterface $taskInterface): void
    {
        $this->task = $taskInterface;
    }

    public function getTask(): TaskInterface
    {
        return $this->task;
    }

    public function setCronParser(CronParserInterface $cronParserInterface): void
    {
        $this->cron = $cronParserInterface;
    }

    public function getCronParser(): CronParserInterface
    {
        return $this->cron;
    }

    public function next(): void
    {
        $nextTimestamp = $this->cron->getNext();
        $datetime = new DateTime();
        $datetime->setTimestamp($nextTimestamp);
        $this->runScheduledTask($datetime);
        $this->saveNext($datetime);

    }

    public function saveNext(DateTime $datetime): void
    {
       
        $dir = __DIR__ . "/.scheduler";
        if (!is_dir($dir)) {
            mkdir($dir);
        }

        $taskclass = $this->task::class;
        $filetasks = "$dir/$taskclass";

        if (is_file($filetasks)) {
            return;
        }

        $next = $datetime->format("Y-m-d H:i") . PHP_EOL;
        file_put_contents($filetasks, $next, FILE_APPEND | LOCK_EX);
    }

    private function runScheduledTask(DateTime $datetime): void
    {
        $taskclass = $this->task::class;
        $filetask = __DIR__ . "/.scheduler/$taskclass";

        if (!is_file($filetask)) {
            return;
        }

        $fopen = @fopen($filetask, "r");
        if ($fopen) {
            while (($buffer = fgets($fopen, 4096)) !== false) {
                $now = date("Y-m-d H:i");
                if ($now == trim($buffer)) {
                    $job = new Job($taskclass, "onTask");
                    $job->execute();
                    unlink($filetask);
                }

                $nextExpected = $datetime->format("Y-m-d H:i");
                if ($buffer != $nextExpected) {
                    unlink($filetask);
                }
            }

            fclose($fopen);
        }
    }
}