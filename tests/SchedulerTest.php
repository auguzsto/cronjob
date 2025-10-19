<?php

use Auguzsto\Cronjob\Scheduler;
use Auguzsto\Cronjob\Tests\Mocks\SchedulerMock;
use PHPUnit\Framework\TestCase;
use Auguzsto\Cronjob\TaskInterface;
use Auguzsto\Cronjob\SchedulerInterface;

class SchedulerTest extends TestCase
{
    private const string CRONJOB_TASKS_DIR = "CRONJOB_TASKS_DIR";

    public static function setUpBeforeClass(): void
    {
        $dir = SchedulerInterface::DIR;
        $files = array_diff(scandir($dir), [".", ".."]);
        foreach ($files as $file) {
            unlink("$dir/$file");
        }
        
        rmdir($dir);
    }

    public function testCreatesAndPersistsTaskSchedulingInDirectory(): void
    {
        $dir = $_SERVER[self::CRONJOB_TASKS_DIR];
        require_once "$dir/ConsumerTask.php";
        $dir = SchedulerInterface::DIR;
        $task = new ConsumerTask();
        $schedulerMock = new SchedulerMock();
        $taskName = $task::class;
        
        $schedulerMock->scheduler->setTask($task);
        $schedulerMock->scheduler->getCronParser()->setExpression("* * * * *");
        $schedulerMock->scheduler->scheduleTask();

        $this->assertTrue(file_exists("$dir/$taskName"));

        $agendas = json_decode(file_get_contents("$dir/$taskName"));
        $schedule = $agendas[0];
        
        $this->assertEquals($schedule->status, SchedulerInterface::STATUS_SCHEDULED);
    }

    public function testChangeStatusToDoneWhenRunningScheduledTask(): void
    {
        $CRONJOB_TASKS_DIR = $_SERVER[self::CRONJOB_TASKS_DIR];
        require_once "$CRONJOB_TASKS_DIR/ConsumerTask.php";
        
        $dir = SchedulerInterface::DIR;
        $task = new ConsumerTask();
        $schedulerMock = new SchedulerMock();
        $taskName = $task::class;
        $agendas = json_decode(file_get_contents("$dir/$taskName"));
        $agendas[0]->next = date("Y-m-d H:i");
        file_put_contents("$dir/$taskName", json_encode($agendas));
        
        $schedulerMock->scheduler->setTask($task);
        $schedulerMock->scheduler->runScheduledTask();

        $agendas = json_decode(file_get_contents("$dir/$taskName"));
        $schedule = $agendas[0];

        sleep(2);
        $this->assertTrue(file_exists("consumer_task.txt"));
        $this->assertEquals($schedule->status, SchedulerInterface::STATUS_DONE);
    }

    public function testRescheduleTaskAfterExecutionWithScheduledStatus(): void
    {
        $dir = $_SERVER[self::CRONJOB_TASKS_DIR];
        require_once "$dir/ConsumerTask.php";
        $dir = SchedulerInterface::DIR;
        $task = new ConsumerTask();
        $schedulerMock = new SchedulerMock();
        $taskName = $task::class;
        
        $schedulerMock->scheduler->setTask($task);
        $schedulerMock->scheduler->getCronParser()->setExpression("* * * * *");
        $schedulerMock->scheduler->scheduleTask();

        $this->assertTrue(file_exists("$dir/$taskName"));

        $agendas = json_decode(file_get_contents("$dir/$taskName"));
        $schedule = $agendas[1];
        
        $this->assertEquals($schedule->status, SchedulerInterface::STATUS_SCHEDULED);
    }
}