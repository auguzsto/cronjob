<?php
namespace Auguzsto\Cronjob;

use DateTime;

interface SchedulerInterface 
{
    public function next(): void;
    public function saveNext(DateTime $datetime): void;
}