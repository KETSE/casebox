#!/usr/bin/php
<?php
/*
	Reindex solr core
	this script can be called with parametter - <core_name> (without prefix)
	if no argument is specified or argument = "all", then all solr cores will be reindexed

	example: php -f solr_reindex_core.php dev
*/
	include '../crons/crons_init.php';
	require_once CB_SOLR_CLIENT;
	echo CB_SOLR_HOST.':'.CB_SOLR_PORT;
	foreach($CB_cores as $core){
		echo 'Processing core "'.$core['db_name'].'" ... ';
		$solr = new Apache_Solr_Service(CB_SOLR_HOST, CB_SOLR_PORT, '/solr/'.$core['db_name']);
		if(! $solr->ping()) die('Can\'t connect to SOLR ('.$core['db_name'].')');
		$solr->deleteByQuery('*:*');
		unset($solr);
		echo "Ok\n";
	}
	global $update_all;
	$update_all = true;
	include '../crons/cron_solr_update_tree.php';
	include '../crons/cron_solr_optimize.php';
?>