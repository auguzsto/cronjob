<?php
namespace Auguzsto\Cronjob;

use Auguzsto\Job\Job;
use DateTime;
use Poliander\Cron\CronExpression;
use Auguzsto\Cronjob\TaskInterface;

class Scheduler implements SchedulerInterface
{
    private string $crontab;
    private TaskInterface $task;

    public function __construct(string $crontab, TaskInterface $task)
    {
        $this->set($crontab);
        $this->task = $task;
        $this->next();
    }

    private function set(string $crontab): void
    {
        $this->crontab = $crontab;
    }

    public function get(): string
    {
        return $this->crontab;
    }

    public function next(): void
    {
        $cronExpression = new CronExpression($this->get());
        $nextTimestamp = $cronExpression->getNext();

        $datetime = new DateTime();
        $datetime->setTimestamp($nextTimestamp);
        $this->saveNext($datetime);

    }

    public function saveNext(DateTime $datetime): void
    {
        $this->runScheduledTask($datetime);
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