<?php
namespace Auguzsto\Cronjob;

use Auguzsto\Job\Job;

class Cronjob
{
    public static function up(string $config): never
    {
        $task = json_decode($config);
        while (true) {
            $classTasks = scandir($task->pathAboslute);
            $files = array_diff($classTasks, [".", ".."]);
            
            foreach ($files as $key => $class) {
                $classWithoutExtension = str_replace(".php", "", $class);
                $classNamespace = "\\{$task->namespace}\\$classWithoutExtension";
                
                $instance = new $classNamespace();
                $job = new Job($instance::class, "toScheduler");
                $job->execute();
            }
            sleep(1);
        }
    }
}