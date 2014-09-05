#!/usr/bin/php
<?php
ini_set('max_execution_time', 0);

$path = realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'crons'.DIRECTORY_SEPARATOR);
echo shell_exec('php -f "'.$path.DIRECTORY_SEPARATOR.'run_cron.php" extract_files_content '.@$argv[1].' '.@$argv[2]);
