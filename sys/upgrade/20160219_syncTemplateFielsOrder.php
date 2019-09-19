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
use CB\DataModel as DM;

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

$fields = DM\TemplatesStructure::getFields();

DB\startTransaction();
//iterate and update each field

foreach ($fields as $f) {
    $o = Objects::getCachedObject($f['id']);
    $order = intval($o->getFieldValue('order', 0)['value']);
    DM\TemplatesStructure::update(
        array(
            'id' => $f['id']
            ,'order' => $order
        )
    );
    echo '.';
}
DB\commitTransaction();

echo "\nDone";
