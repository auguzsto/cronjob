## Initialize
vendor/bin/cronjob init

## Creates an environment variable CRONJOB_TASKS_DIR with the absolute path of the tasks folder
CRONJOB_TASKS_DIR=/example/app/tasks

## Create a task
vendor/bin/cronjob create BackupTask

## Run the cron
vendor/bin/cronjob start