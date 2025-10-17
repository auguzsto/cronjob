<?php

use Auguzsto\Cronjob\TaskInterface;
use Auguzsto\Cronjob\SchedulerInterface;

class MyTaskExample implements TaskInterface
{
    /**
     * Schedule
     * $scheduler->on("* * * * *", new self);
     */
    public static function toScheduler(SchedulerInterface $scheduler): void 
    {
        $scheduler->on("* * * * *", new self);
    }

    /**
     * Implement the task to be performed
     */
    public static function onTask(): void
    {
        file_put_contents("backup.txt", "backup");
    }
}