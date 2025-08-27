<?php
namespace Auguzsto\Cronjob;

interface TaskInterface
{
    public static function toScheduler(SchedulerInterface $scheduler): void;
    public static function onTask(): void;
}