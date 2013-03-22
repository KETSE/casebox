#!/usr/bin/php
<?php
require_once 'crons_init.php';
require_once CB_SOLR_CLIENT;
$cron_id = 'solr_update_tree';
foreach($CB_cores as $core){
	$GLOBALS['CB_LANGUAGE'] = $core['default_language'];
	try {
		connect2DB($core);//$res = mysqli_query_params('use `'.$core['db_name'].'`');
	} catch (Exception $e) {
		continue;
	}
	
	echo 'Processing core '.$core['db_name'].' ... ';
	if(empty($update_all)){
		$cd = prepare_cron($cron_id, 2, 'core: '.$core['db_name']);
		if(!$cd['success']) exit(1);
	}//$solr variable created outside by manual update all script
	/* unset specific core globals */
	unset($GLOBALS['EVERYONE_GROUP_ID']);

	$solr = new SolrClient(array('core' => '/solr/'.$core['db_name'] ));
	$solr->connect();
	initLanguages();
	$rez = $solr->updateTree(!empty($update_all));
	echo "OK ... ";
	mysqli_query_params('update crons set last_end_time = CURRENT_TIMESTAMP, execution_info = $2 where cron_id = $1', array($cron_id, json_encode($rez)) ) or die(mysqli_query_error()."\n".$sql);
	unset($solr);
	echo "Done\n";
}
?>