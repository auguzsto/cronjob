<?php
namespace Auguzsto\Cronjob\Tasks;

use Auguzsto\Cronjob\TaskInterface;
use Auguzsto\Cronjob\SchedulerInterface;

class MyTaskExample implements TaskInterface
{
    public static function toScheduler(SchedulerInterface $scheduler): void 
    {
        $scheduler->on("* * * * *", new self);
    }

    public static function onTask(): void
    {
        file_put_contents("backup.txt", "backup");
    }
}