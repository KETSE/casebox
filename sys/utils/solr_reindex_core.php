#!/usr/bin/php
<?php
/*
    Reindex solr core
    this script can be called with parametter - <core_name> (without prefix)
    if core_name = "all", then all solr cores will be reindexed

    Second argument can be specified: all
    If second parameter is specified (all) then all tree nodes are reindexed in solr,
    otherwise only updated nodes from DB are reindexed in solr.

    example: php -f solr_reindex_core.php dev
        php -f solr_reindex_core.php dev all
*/

ini_set('max_execution_time', 0);
$path = realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'crons'.DIRECTORY_SEPARATOR);
$cmd = 'php -f "'.$path.DIRECTORY_SEPARATOR.'run_cron.php" solr_update_tree '.@$argv[1].' '.@$argv[2]." force\n";

echo shell_exec($cmd);
