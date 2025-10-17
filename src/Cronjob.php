<?php
namespace Auguzsto\Cronjob;

use Auguzsto\Job\Job;
use Auguzsto\Cronjob\Scheduler;

class Cronjob
{
    public static function up(): never
    {
        $dirtasks = $_SERVER["CRONJOB_TASKS_DIR"];
        while (true) {
            $classTasks = scandir($dirtasks);
            $files = array_diff($classTasks, [".", ".."]);
            
            foreach ($files as $key => $file) {
                $include = "$dirtasks/$file";
                require_once $include;

                $classWithoutExtension = str_replace(".php", "", $file);
                $job = new Job($classWithoutExtension, "toScheduler", [new Scheduler()]);
                $job->include($include);
                $job->execute();
            }
            sleep(60);
        }
    }
}