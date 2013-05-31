#!/usr/bin/php
<?php

namespace CB;

require_once 'init.php';

$cron_id = 'solr_update_tree';

$cd = prepare_cron($cron_id, 5);
if(!$cd['success']){
	echo "\nerror preparing cron\n";
	exit(1);
}
$solr = new SolrClient();
try {
	if(@$argv[2] == 'all'){
		echo "deleting all\n";
		$solr->deleteByQuery('*:*');
		echo "updating tree\n";
		$solr->updateTree(true);
		echo "optimizing\n";
		$solr->optimize();
	}else{
		$solr->updateTree();
	}
	
} catch (\Exception $e) {
	notify_admin('CaseBox cron execution exception ('.$solr->core.')', $e->getMessage() );		
}

unset($solr);

DB\mysqli_query_params('update crons set last_end_time = CURRENT_TIMESTAMP, execution_info = \'ok\' where cron_id = $1', array($cron_id) ) or die( DB\mysqli_query_error() );
