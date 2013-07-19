#!/usr/bin/php
<?php

namespace CB;

require_once 'init.php';

$cron_id = 'solr_update_tree';
$execution_timeout = 60; //default is 60 seconds

$cd = prepare_cron($cron_id, $execution_timeout);
if(!$cd['success']){
	echo "\nerror preparing cron\n";
	exit(1);
}
$solr = new SolrClient();
try {
	if(@$argv[2] == 'all'){
		echo "deleting all\n";
		$solr->deleteByQuery('*:*');
		DB\mysqli_query_params('update crons set last_action = CURRENT_TIMESTAMP where cron_id = $1', $cron_id) or die('error updating crons last action');
		echo "updating tree\n";
		$solr->updateTree(true, $cron_id);
		DB\mysqli_query_params('update crons set last_action = CURRENT_TIMESTAMP where cron_id = $1', $cron_id) or die('error updating crons last action');
		echo "optimizing\n";
		$solr->optimize();
		DB\mysqli_query_params('update crons set last_action = CURRENT_TIMESTAMP where cron_id = $1', $cron_id) or die('error updating crons last action');
	}else{
		$solr->updateTree();
	}
	
} catch (\Exception $e) {
	notify_admin('CaseBox cron execution exception ('.$solr->core.')', $e->getMessage() );		
}

unset($solr);

DB\mysqli_query_params('update crons set last_end_time = CURRENT_TIMESTAMP, execution_info = \'ok\' where cron_id = $1', array($cron_id) ) or die( DB\mysqli_query_error() );
