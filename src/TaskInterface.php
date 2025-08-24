<?php
namespace Auguzsto\Cronjob;

interface TaskInterface
{
    public static function toScheduler(): SchedulerInterface;
    public static function onTask(): void;
}