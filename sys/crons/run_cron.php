<?php
namespace CB;

use CB\DB;

/**
 * Running crons script.
 *
 * All crons can be started through this script only.
 * This script will parse input params and start corresponding cron for each requested core.
 *
 * acceptable common params are:
 * -n, --name      cron name to run, applicable for run_cron.php
 * -c, --core      core name or "all"
 * -a, --all       all records
 * -l, --nolimit   skip items limit on indexing core
 * -f, --force     skip other same cron running check
 *                 when force mode also cores under maintainance are processed
 *
 * @author Turcanu Vitalie, 22 april, 2013
 *
 */

//check script options
$options = getopt('n:c:alf', array('name', 'core', 'all', 'nolimit', 'force'));

$cronName = empty($options['n'])
    ? @$options['name']
    : $options['n'];

if (empty($cronName)) {
    die('no cron name specified or invalid options set.');
}

$core = empty($options['c'])
    ? @$options['core']
    : $options['c'];

if (empty($core)) {
    die('no core specified or invalid options set.');
}

$all = isset($options['a']) || isset($options['all']);

$nolimit = isset($options['l']) || isset($options['nolimit']);

$force = isset($options['f']) || isset($options['force']);
//end of check script options

$cron_file = explode('/', $cronName);
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
    WHERE ((active > 0) AND (active < $1))',
    $force ? 3 : 2
) or die(DB\dbQueryError());

while ($r = $res->fetch_assoc()) {
    if (empty($argv[2]) ||
        ($core == $r['name']) ||
        (($core == 'all') && ($r['active'] > 0))
    ) {
        $cores[] = $r['name'];
    }
}
$res->close();

if (empty($cores)) {
    echo "Core not found or inactive.\n";
} else {
    foreach ($cores as $core) {
        $cmd = 'php -f '.$cron_path.$cron_file.' -- -c '.$core;

        if ($all) {
            $cmd .= ' -a';
        }

        if ($nolimit) {
            $cmd .= ' -l';
        }

        if ($force) {
            $cmd .= ' -f';
        }

        $rez = shell_exec($cmd);

        if (!empty($rez)) {
            $rez = "\n" . date('Y-m-d H:i:s'). " Processing core $core ... " . $rez;
        }
        echo $rez;
    }
}
