<?php
namespace CB;

use CB\DB;

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
    die('Not enough parameters specified. Use run_cron.php <cron_name> <core_name>|all ');
}

$cron_file = explode('/', $argv[1]);
$cron_file = array_pop($cron_file);

$cron_file = explode('\\', $cron_file);
$cron_file = 'cron_'.array_pop($cron_file).'.php';
$cron_path = dirname(__FILE__).DIRECTORY_SEPARATOR;
$docRootPath = realpath($cron_path.'../../httpsdocs/') . DIRECTORY_SEPARATOR;

if (!file_exists($cron_path.$cron_file)) {
    die('cannot find cron '.$cron_path.$cron_file);
}

require_once $docRootPath . 'config_platform.php';

ini_set('max_execution_time', 0);

DB\connect($cfg);

$cores = array();
$res = DB\dbQuery(
    'SELECT name, active
    FROM ' . PREFIX . '_casebox.cores
    WHERE active <> 0'
) or die(DB\dbQueryError());

while ($r = $res->fetch_assoc()) {
    if (empty($argv[2]) ||
        ($argv[2] == $r['name']) ||
        (($argv[2] == 'all') && ($r['active'] > 0))
    ) {
        $cores[] = $r['name'];
    }
}
$res->close();

if (empty($cores)) {
    echo "Core not found or inactive.\n";
} else {
    foreach ($cores as $core) {
        // echo "\nProcessing core $core ...";
        echo shell_exec('php -f '.$cron_path.$cron_file.' '.$core.' '.@$argv[3].' '.@$argv[4]);
    }
    // echo "\nDone\n";
}
