## Setting up
Install package
```
composer require auguzsto/cronjob
```
Create the folder for the tasks, preferably at the root of your project.
```
mkdir tasks
```

Next step is to set the CRONJOB_TASKS_DIR environment variable with the absolute path of the task path.
```
CRONJOB_TASKS_DIR=/example/app/tasks
```

## Creating task
```
vendor/bin/cronjob create ExampleTask
```
This command will create a class in your tasks folder with a skeleton already in place.

```php
<?php

use Auguzsto\Cronjob\TaskInterface;
use Auguzsto\Cronjob\SchedulerInterface;

class ExampleTask implements TaskInterface
{
    /**
     * Schedule
     * $scheduler->on("* * * * *", new self);
     */
    public static function toScheduler(SchedulerInterface $scheduler): void 
    {
        $scheduler->on("0 0 * * *", new self);
    }

    /**
     * Implement the task to be performed
     */
    public static function onTask(): void
    {
        file_put_contents("backup.txt", "backup");
    }
}
```

toScheduler() is used to set the task's period in cron format (* * * * *).

onTask() is the method that will be executed.

## Turn on the service
```
vendor/bin/cronjob start
```
Done! Your scheduling service is up and running.

# Commands
## Turn off the service
```
vendor/bin/cronjob stop
```

## View schedules by task
```
vendor/bin/cronjob schedules ExampleTask
```

## View all scheduler errors
```
vendor/bin/cronjob errros
```