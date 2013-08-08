<?php

/**
 * Running crons script.
 *
 * All crons can be started through this script only.
 * This script will parse input params and start corresponding cron for each requested core.
 *
 * @author Turcanu Vitalie, 22 april, 2013
 *
 */

if (sizeof($argv) < 3) {
    die('Not enough parameters specified. Use run_cron.php <cron_name> <core_name>/all ');
}

$cron_file = explode('/', $argv[1]);
$cron_file = array_pop($cron_file);

$cron_file = explode('\\', $cron_file);
$cron_file = 'cron_'.array_pop($cron_file).'.php';
$cron_path = dirname(__FILE__).DIRECTORY_SEPARATOR;

if (!file_exists($cron_path.$cron_file)) {
    die('cannot find cron '.$cron_path.$cron_file);
}

define(
    'CORES_DIR',
    realpath(
        dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.
        DIRECTORY_SEPARATOR.'httpsdocs'.DIRECTORY_SEPARATOR.'cores'.DIRECTORY_SEPARATOR
    )
);

$cores = array();
foreach (new DirectoryIterator(CORES_DIR) as $file) {
    $name =$file->getFilename();
    if ($name == 'sample') {
        continue;
    }
    if (!$file->isDot() &&
        $file->isDir() &&
        (empty($argv[2]) || ( $argv[2] == $name ) || ( $argv[2] == 'all' ) )) {
        $cores[] = $name;
    }
}
if (sizeof($cores) > 1) {
    echo sizeof($cores)." cores found.\n";
}

foreach ($cores as $core) {
    echo "\nProcessing core $core ...";
    echo shell_exec('php -f '.$cron_path.$cron_file.' '.$core.' '.@$argv[3]);
}
echo "\nDone\n";
