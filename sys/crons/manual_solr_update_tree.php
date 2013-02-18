#!/usr/bin/php
<?php
	include 'crons_init.php';
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
	include 'cron_solr_update_tree.php';
	include 'cron_solr_optimize.php';
?>