<?php
namespace Auguzsto\Cronjob;

use Poliander\Cron\CronExpression;

class CronParser implements CronParserInterface
{
    private string $expression;

    public function getExpression(): string
    {
        return $this->expression;
    }

    public function setExpression(string $expression): void
    {
        $this->expression = $expression;
    }

    public function getNext(): int|bool
    {
        $cronExpression = new CronExpression($this->getExpression());
        return $cronExpression->getNext();
    }
}