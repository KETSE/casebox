#!/usr/bin/php
<?php
/*
	Reindex solr core
	this script can be called with parametter - <core_name> (without prefix)
	if argument = "all", then all solr cores will be reindexed

	example: php -f solr_reindex_core.php dev
*/

$path = realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'crons'.DIRECTORY_SEPARATOR);
$cmd = 'php -f "'.$path.DIRECTORY_SEPARATOR.'run_cron.php" solr_update_tree '.@$argv[1].' '.@$argv[2]."\n";
echo shell_exec($cmd);
