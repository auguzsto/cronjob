<?php

use PHPUnit\Framework\TestCase;

class BinTest extends TestCase
{
    private const string BIN = __DIR__ . "/../bin/cronjob";
    private const string BIN_MOCK = __DIR__ . "/../bin/cronjob.mock";
    private const string CRONJOB_TASKS_DIR = "CRONJOB_TASKS_DIR";
    private const string CRONJOB_TASKS_DIR_MOCK = "CRONJOB_TASKS_DIR_MOCK";
    private const string VALUE_TASKS_DIR_MOCK = __DIR__ . "/Mocks/Tasks";
    private const string TASK_EXAMPLE = "ExampleCreateTask";

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

}