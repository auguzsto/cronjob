<?php
namespace Auguzsto\Cronjob\Tasks;

use Auguzsto\Cronjob\Scheduler;
use Auguzsto\Cronjob\TaskInterface;
use Auguzsto\Cronjob\SchedulerInterface;

class MyTaskExample implements TaskInterface
{
    public static function toScheduler(): SchedulerInterface
    {
        return new Scheduler("* * * * *", new self);
    }

    public static function onTask(): void
    {
        file_put_contents("backup.txt", "backup");
    }
}