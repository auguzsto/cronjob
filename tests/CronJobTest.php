<?php

use Auguzsto\Cronjob\CronParser;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;
use Auguzsto\Cronjob\Scheduler;
use PHPUnit\Framework\TestCase;

class CronJobTest extends TestCase
{
    private const string BIN = "/usr/src/myapp/bin/cronjob";
    private const string BIN_MOCK = "/usr/src/myapp/bin/cronjob.mock";
    private const string CRONJOB_TASKS_DIR = "CRONJOB_TASKS_DIR";
    private const string CRONJOB_TASKS_DIR_MOCK = "CRONJOB_TASKS_DIR_MOCK";
    private const string VALUE_TASKS_DIR_MOCK = "/usr/src/myapp/tests/Mock/Tasks";
    private const string TASK_EXAMPLE = "ExampleTask";
    private const string SCHEDULED_TASK_FOLDER = "/usr/src/myapp/src/.scheduler";

    public function setUp(): void
    {
        $bin = file_get_contents(self::BIN);
        $binMock = str_replace(self::CRONJOB_TASKS_DIR, self::CRONJOB_TASKS_DIR_MOCK, $bin);

        file_put_contents(self::BIN_MOCK, $binMock);

    }

    public static function tearDownAfterClass(): void
    {
        $dirtask = self::VALUE_TASKS_DIR_MOCK;
        $task = self::TASK_EXAMPLE;
        unlink("$dirtask/$task.php");
    }

    public function testReturnErrorEnvironmentVariableIsNotSetWhenStart(): void
    {
        $bin = self::BIN_MOCK;
        exec("php $bin start", $output);

        $this->assertEquals("Error: Environment variable 'CRONJOB_TASKS_DIR_MOCK' is not set.", $output[0]);

    }

    public function testReturnErrorTaskFolderNotFoundWhenStart(): void
    {
        $bin = self::BIN_MOCK;
        exec("export CRONJOB_TASKS_DIR_MOCK='/not/exists'&& php $bin start", $output);

        $this->assertEquals("Error: Task folder not found.", $output[0]);

    }

    public function testReturnErrorEnvironmentVariableIsNotSetWhenCreate(): void
    {
        $bin = self::BIN_MOCK;
        exec("php $bin create TestTask", $output);

        $this->assertEquals("Error: Environment variable 'CRONJOB_TASKS_DIR_MOCK' is not set.", $output[0]);

    }

    public function testReturnErrorTaskFolderNotFoundWhenCreate(): void
    {
        $bin = self::BIN_MOCK;
        exec("export CRONJOB_TASKS_DIR_MOCK='/not/exists'&& php $bin create TestTask", $output);

        $this->assertEquals("Error: Task folder not found.", $output[0]);

    }

    public function testCreateScaffoldTask(): void
    {
        $bin = self::BIN_MOCK;
        $task = self::TASK_EXAMPLE;
        $dirtask = self::VALUE_TASKS_DIR_MOCK;
        exec("export CRONJOB_TASKS_DIR_MOCK='$dirtask' && php $bin create $task");

        $this->assertTrue(file_exists("$dirtask/$task.php"));
    }

    public function testScheduleTaskForMidnight(): void
    {
        $dirtask = self::VALUE_TASKS_DIR_MOCK;
        $taskExample = self::TASK_EXAMPLE;
        $dirTaskScheduled = self::SCHEDULED_TASK_FOLDER;
        $cron = "0 0 * * *";

        $content = file_get_contents("$dirtask/$taskExample.php");
        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $ast = $parser->parse($content);
        
        // Adicionando agendador em runtime.
        foreach ($ast as $node) {
            if ($node instanceof Stmt\Class_) {
                foreach ($node->stmts as $stmt) {
                    if ($stmt instanceof Stmt\ClassMethod && $stmt->name->toString() === 'toScheduler') {
                        $stmt->stmts = [
                            new Stmt\Expression(
                                new Expr\FuncCall(
                                    new Node\Name('$scheduler->on'),
                                    [
                                        new Node\Arg(new Node\Scalar\String_($cron)),
                                        new Node\Arg(new Expr\New_(new Node\Name('self')))
                                    ]
                                )
                            ),
                        ];
                    }

                    if ($stmt instanceof Stmt\ClassMethod && $stmt->name->toString() === 'onTask') {
                        $stmt->stmts = [
                            new Stmt\Expression(
                                new Expr\FuncCall(
                                    new Node\Name('file_put_contents'),
                                    [
                                        new Node\Arg(new Node\Scalar\String_("example_tests.txt")),
                                        new Node\Arg(new Node\Scalar\String_("testing")),
                                    ]
                                )
                            ),
                        ];
                    }
                }
            }
        }

        $printer = new PrettyPrinter\Standard();
        $newCode = $printer->prettyPrintFile($ast);

        file_put_contents("$dirtask/$taskExample.php", $newCode);
        require_once "$dirtask/$taskExample.php";

        self::TASK_EXAMPLE::toScheduler(new Scheduler());
        $this->assertTrue(file_exists("$dirTaskScheduled/$taskExample"));
        
    }

    public function testNextDateTimeForTaskRun(): void
    {
        $dirtask = self::VALUE_TASKS_DIR_MOCK;
        $taskExample = self::TASK_EXAMPLE;
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