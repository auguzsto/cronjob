<?php
namespace Auguzsto\Cronjob;

use DateTime;
use Auguzsto\Cronjob\TaskInterface;
use Auguzsto\Cronjob\CronParserInterface;

interface SchedulerInterface
{
    public function setTask(TaskInterface $taskInterface): void;
    public function getTask(): TaskInterface;
    public function setCronParser(CronParserInterface $cronParserInterface): void;
    public function getCronParser(): CronParserInterface;
    public function next(): void;
    public function saveNext(DateTime $datetime): void;
}