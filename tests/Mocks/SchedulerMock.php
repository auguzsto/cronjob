<?php
namespace Auguzsto\Cronjob\Tests\Mocks;

use Auguzsto\Cronjob\CronParser;
use Auguzsto\Cronjob\Scheduler;
use Auguzsto\Cronjob\TaskInterface;
use Auguzsto\Cronjob\SchedulerInterface;
use Auguzsto\Cronjob\CronParserInterface;

class SchedulerMock
{
    public SchedulerInterface $scheduler;

    public function __construct()
    {
        $this->scheduler = new Scheduler();
    }
}