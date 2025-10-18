<?php

use Auguzsto\Cronjob\Scheduler;
use DateTime;
use PHPUnit\Framework\TestCase;
use Auguzsto\Cronjob\CronParser;
use Auguzsto\Cronjob\TaskInterface;
use Auguzsto\Cronjob\SchedulerInterface;

class TaskTest extends TestCase
{
    private const string SCHEDULED_TASK_FOLDER = "/usr/src/myapp/src/.scheduler";

    public function testTaskScheduling(): void
    {
        $dirScheduled = self::SCHEDULED_TASK_FOLDER;
        require_once __DIR__ . "/Mocks/ExampleTask.php";
        ExampleTask::toScheduler(new Scheduler());

        $taskScheduled = file_exists("$dirScheduled/ExampleTask");
        $this->assertTrue($taskScheduled);

    }

    public function testNextDateTimeForTaskRun(): void
    {
        $taskExample = "ExampleTask";
        $dirTaskScheduled = self::SCHEDULED_TASK_FOLDER;
        $cron = "0 0 * * *";

        $cronExpression = new CronParser();
        $cronExpression->setExpression($cron);
        $datetime = new DateTime();
        $datetime->setTimestamp($cronExpression->getNext());
        $next = $datetime->format("Y-m-d H:i");
        $scheduled = json_decode(file_get_contents("$dirTaskScheduled/$taskExample"))[0];
        
        $this->assertEquals($next, $scheduled->next);

    }
}