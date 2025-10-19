<?php

use Auguzsto\Cronjob\Scheduler;
use DateTime;
use PHPUnit\Framework\TestCase;
use Auguzsto\Cronjob\CronParser;

class TaskTest extends TestCase
{
    private const string SCHEDULED_TASK_FOLDER = __DIR__ . "/../src/.scheduler";
   private const string CRONJOB_TASKS_DIR = "CRONJOB_TASKS_DIR";

    public function testTaskScheduling(): void
    {
        $dirScheduled = self::SCHEDULED_TASK_FOLDER;
        $dir = $_SERVER[self::CRONJOB_TASKS_DIR];
        require_once "$dir/ExampleTask.php";
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