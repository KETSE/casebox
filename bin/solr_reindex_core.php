<?php
/*
    Reindex solr core

    Script params:
    -c, --core  - required, core name or "all"
    -a, --all   - reindex all items. Solr will be cleared and all records
                    from tree table will bemarked as updated.
    -l, --nolimit - reindex script indexes, by default, a limited number of items.

    example: php -f solr_reindex_core.php -c dev
        php -f solr_reindex_core.php --core=dev -a -l
*/

ini_set('max_execution_time', 0);

$path = realpath(
    dirname(__FILE__) . DIRECTORY_SEPARATOR .
    '..' . DIRECTORY_SEPARATOR .
    'sys' . DIRECTORY_SEPARATOR .
    'crons' . DIRECTORY_SEPARATOR
);

$cmd = 'php "'.$path.DIRECTORY_SEPARATOR.'run_cron.php" -n solr_update_tree ';

//check script options
if (empty($options)) {
    $options = getopt('c:al', array('core', 'all', 'nolimit'));
}

$core = empty($options['c'])
    ? @$options['core']
    : $options['c'];

if (empty($core)) {
    echo 'no core specified or invalid options set.';
    return;
}

$all = isset($options['a']) || isset($options['all']);

$nolimit = isset($options['l']) || isset($options['nolimit']);
//end of check script options

//add params to command
$cmd .= '-c ' . $core;

if ($all) {
    $cmd .= ' -a';
}
if ($nolimit) {
    $cmd .= ' -l';
}

$cmd .= "  -f\n";

echo shell_exec($cmd);
