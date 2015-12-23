#!/usr/bin/php
<?php
namespace CB;

/*
    Create prepared solr data in sys_data['solr'] for each object
    By default only records marked as updated will be processed

    Script params:
    -c, --core  <core_name> - required, core name
    -a, --all   - process all records, by default only updated are processed
    -t, --template <id_or_type> - template name or template type

    example: php -f upgrade_task_data.php -- -c dev -a -t 23
*/
use CB\DB;
use CB\DataModel as DM;

ini_set('max_execution_time', 0);

$path = realpath(
    dirname(__FILE__) . DIRECTORY_SEPARATOR .
    '..' . DIRECTORY_SEPARATOR .
    'sys' . DIRECTORY_SEPARATOR .
    'crons' . DIRECTORY_SEPARATOR
) . DIRECTORY_SEPARATOR;

//check script options
$options = getopt('c:at:', array('core', 'all', 'template'));

$core = empty($options['c'])
    ? @$options['core']
    : $options['c'];

if (empty($core)) {
    die('no core specified or invalid options set.');
}

$all = isset($options['a']) || isset($options['all']);

$template = empty($options['t'])
    ? @$options['template']
    : $options['t'];

//init
$cron_id = 'dummy';

include $path.'init.php';

\CB\Config::setFlag('disableActivityLog', true);

//create query filter
$where = empty($all)
    ? ' AND t.updated = 1'
    : '';

if (!empty($template)) {
    $template = is_numeric($template)
        ? array($template)
        : DM\Templates::getIdsByType($template);

    if (!empty($template)) {
        $where .= ' AND t.template_id in (' . implode(',', $template) . ') ';
    }
}

// join with tree table if filter not empty
if (!empty($where)) {
    $where = ' JOIN tree t ON o.id = t.id ' . $where;
}

//start the process

//select all objects that have data in "objects" table
$res = DB\dbQuery(
    'SELECT count(*) `nr`
    FROM objects o' . $where
);
if ($r = $res->fetch_assoc()) {
    echo "Total objects: ".$r['nr'] . "\n";
}
$res->close();

//iterate and update each object
$i = 0;

DB\startTransaction();

$res = DB\dbQuery(
    'SELECT o.id
    FROM objects o' . $where
);
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

DB\commitTransaction();

echo "\nDone";
