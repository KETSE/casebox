#!/usr/bin/php
<?php
$cron_id = 'check_deadlines'; // to finish
//$execution_skip_times = 1; //default is 1

include 'crons_init.php';
foreach($CB_cores as $core){
	$res = mysqli_query_params('use `'.$core['db_name'].'`');
	if(!$res) continue;
	echo "\nProcessing core \"".$core['db_name']."\" ...";

	$cd = prepare_cron($cron_id);
	if(!$cd['success']){
		echo "\nerror preparing cron\n";
		exit(1);
	}
	initLanguages();
	$res = mysqli_query_params('SELECT id, `title`, cid, responsible_user_ids, '.
		'CASE WHEN allday=1 THEN date_end < CURRENT_DATE ELSE date_end <= date_end END `expired` '.
		'FROM tasks WHERE `type` = 6 AND `status` IN (2, 4) AND has_deadline = 1') or die(mysqli_query_error());
	while($r = $res->fetch_assoc()){
		echo " task ".$r['id'].': '.$r['expired'];
		if($r['expired'] == 1){
			mysqli_query_params('update tasks set status = 1 where id = $1', $r['id']) or die(mysqli_query_error());
			Log::add(Array('action_type' => 28, 'task_id' => $r['id'], 'remind_users' => $r['cid'].','.$r['responsible_user_ids'], 'info' => 'title: '.$r['title']));
		}
	}
	$res->close();

	mysqli_query_params('update crons set last_end_time = CURRENT_TIMESTAMP, execution_info = $2 where cron_id = $1', array($cron_id, 'ok') ) or die(mysqli_query_error());
	echo " Done";
	/* prepare solr params */
	$solr_params = array(
		'host' => coalesce(@$core['solr_host'], '127.0.0.1')
		,'port' => coalesce(@$core['solr_port'], 8983)
		,'core' => coalesce(@$core['solr_core'], $core['db_name'])

	);
	$solrClient =  new SolrClient($solr_params);
	$solrClient->connect();
	$solrClient->updateTree();
	unset($solrClient);
}

?>