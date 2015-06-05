#!/usr/bin/php
<?php
namespace CB;

/*
    Create prepared solr data in sys_data['solr'] for each object

    Script params:
    -c, --core  - required, core name

    example: php -f upgrade_task_data.php -- -c dev
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

//select all objects that have data in "objects" table
$res = DB\dbQuery(
    'SELECT count(*) `nr`
    FROM objects'
) or die(DB\dbQueryError());
if ($r = $res->fetch_assoc()) {
    echo "Total objects: ".$r['nr'] . "\n";
}
$res->close();

//iterate and update each object
$i = 0;
$res = DB\dbQuery(
    'SELECT id
    FROM objects'
) or die(DB\dbQueryError());
while ($r = $res->fetch_assoc()) {
    if ($i > 100) {
        $i = 0;
        echo '.';
    }

    $obj = Objects::getCustomClassByObjectId($r['id']);
    if (!empty($obj)) {
        $obj->updateSolrData();
    }

    $i++;
}
$res->close();

echo "\nDone";
