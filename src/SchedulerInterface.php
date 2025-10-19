<?php
namespace Auguzsto\Cronjob;

use Auguzsto\Cronjob\TaskInterface;

interface SchedulerInterface
{
    public const string DIR = __DIR__ . "/.scheduler";
    public const string STATUS_SCHEDULED = "scheduled";
    public const string STATUS_DONE = "done";
    public function setTask(TaskInterface $taskInterface): void;
    public function getTask(): TaskInterface;
    public function scheduleTask(): void;
    public function runScheduledTask(): void;
    public function setCronParser(CronParserInterface $cronParserInterface): void;
    public function getCronParser(): CronParserInterface;
    public function on(string $cronExpression, TaskInterface $task): void;
    public static function all(string $task): array;
    public static function erros(): string | null;
}