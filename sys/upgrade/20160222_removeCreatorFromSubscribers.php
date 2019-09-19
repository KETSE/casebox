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

DB\startTransaction();

$res = DB\dbQuery(
    'SELECT o.id, t.cid, o.sys_data
    FROM tree t
    JOIN objects o ON t.id = o.id AND o.sys_data LIKE \'%"wu"%\'
    JOIN templates tt ON t.template_id = tt.id AND tt.type <> \'task\''
);

while ($r = $res->fetch_assoc()) {
    $sd = Util\jsonDecode($r['sys_data']);
    $a = array_diff($sd['wu'], [$r['cid']]);

    if (empty($a)) {
        unset($sd['wu']);
    } else {
        $sd['wu'] = $a;
    }
    DB\dbQuery(
        'UPDATE objects
        SET sys_data = $2
        WHERE id = $1',
        [
            $r['id'],
            Util\jsonEncode($sd)
        ]
    );
    echo '.';
}
$res->close();
DB\commitTransaction();

echo "\nDone";
