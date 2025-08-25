<?php
namespace Auguzsto\Cronjob;

interface CronParserInterface
{
    public function setExpression(string $expression): void;
    public function getExpression(): string;

    /**
     * From the cron expression, it calculates a record of the next 
     * execution and returns the result in timestamp (int) format.
     * @return int|bool
     */
    public function getNext(): int|bool;
}