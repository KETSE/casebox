<?php
namespace Utils;

use CB\L as L;

/**
 * script for creating/syncing thesauri in tree
 *
 * params:
 *     core_name
 *     target_id - folder where templates structure should be created.
 *                 If no target id is specified then new thesauri
 *                 will be created in /Thesauri folder
 */

// check params
if (empty($argv[1])) {
    die('Please specify a core as first argument');
}

$_SERVER['SERVER_NAME'] = $argv[1].'.casebox.local';
$_SESSION['user']['id'] = 1;
$pid = null;

if (!empty($argv[2]) && is_numeric($argv[2])) {
    $pid = $argv[2];
}

require_once '../crons/init.php';

L\initTranslations();
$tagsSyncClass =  new \Util\Tags\TreeSync($pid);

$tagsSyncClass->execute();
