#!/usr/bin/php
<?php

namespace CB;

/*
    Move active subscribers from objects that have subscribers set
    to new followers mechanism

    Script params:
    -c, --core  - required, core name

    example: php -f subscribers_to_followers.php -- -c dev

    Note: there is no need to run any reindexing
        All new actions will generate notifications based on followers data
*/
use CB\DB;

ini_set('max_execution_time', 0);

$path = realpath(
    dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR .
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
    FROM objects
    WHERE `sys_data` LIKE \'%"subscribers"%\''
);
if ($r = $res->fetch_assoc()) {
    echo "Total objects: ".$r['nr'] . "\n";
}
$res->close();

DB\startTransaction();
//iterate and upgrade each object
$i = 0;
$res = DB\dbQuery(
    'SELECT id, sys_data
    FROM objects
    WHERE `sys_data` LIKE \'%"subscribers"%\''
);

while ($r = $res->fetch_assoc()) {
    if ($i > 100) {
        $i = 0;
        echo '.';
    }

    $d = Util\toJSONArray($r['sys_data']);
    if (empty($d['fu'])) {
        $d['fu'] = array();
    }
    $su = $d['subscribers']['on'];
    unset($d['subscribers']);
    $d['fu'] = array_unique(Util\toNumericArray(array_merge($d['fu'], $su)));

    DB\dbQuery(
        'UPDATE objects
        SET `sys_data` = $2
        WHERE id = $1',
        array(
            $r['id'],
            Util\jsonEncode($d)
        )
    );

    $i++;
}
$res->close();
DB\commitTransaction();

echo "\nDone";
