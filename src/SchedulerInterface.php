<?php
namespace Auguzsto\Cronjob;

use Auguzsto\Cronjob\TaskInterface;
use Auguzsto\Cronjob\CronParserInterface;

interface SchedulerInterface
{
    public function setTask(TaskInterface $taskInterface): void;
    public function getTask(): TaskInterface;
    public function scheduleTask(): void;
    public function runScheduledTask(): void;
}