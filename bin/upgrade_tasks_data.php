#!/usr/bin/php
<?php
/*
    Upgrade task data from using specific tables for tasks
    to using sys_data from objects table

    Script params:
    -c, --core  - required, core name

    example: php -f upgrade_task_data.php -- -c dev

    Note: you should run core indexing script after this one
        or just wait a minute for reindex cron to start
*/
use CB\DB;

ini_set('max_execution_time', 0);

$path = realpath(
    dirname(__FILE__) . DIRECTORY_SEPARATOR .
    '..' . DIRECTORY_SEPARATOR .
    'sys' . DIRECTORY_SEPARATOR .
    'crons' . DIRECTORY_SEPARATOR
) . DIRECTORY_SEPARATOR;

//check script options
$options = getopt('c:', array('core'));

$core = empty($options['c'])
    ? @$options['core']
    : $options['c'];

if (empty($core)) {
    die('no core specified or invalid options set.');
}

$cron_id = 'dummy';

include $path.'init.php';

\CB\Config::setFlag('disableActivityLog', true);

//select tasks count
$res = DB\dbQuery(
    'SELECT count(*) `nr`
    FROM tree t
    JOIN tasks ta
        ON t.id = ta.id'
) or die(DB\dbQueryError());
if ($r = $res->fetch_assoc()) {
    echo "Total tasks: ".$r['nr'] . "\n";
}
$res->close();

$taskClass = new \CB\Objects\Task();

//iterate and upgrade each task
$i = 0;
$res = DB\dbQuery(
    'SELECT t.id
    FROM tree t
    JOIN tasks ta
        ON t.id = ta.id'
) or die(DB\dbQueryError());
while ($r = $res->fetch_assoc()) {
    if ($i > 100) {
        $i = 0;
        echo '.';
    }

    $taskClass->load($r['id']);
    $taskClass->update();

    $i++;
}
$res->close();

echo "\nDone";
