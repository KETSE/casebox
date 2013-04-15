#!/usr/bin/php
<?php
$cron_id = 'send_log_notifications';
//$execution_skip_times = 1; //default is 1

include 'crons_init.php';
foreach($CB_cores as $core){
	echo "\nProcessing core \"".$core['db_name']."\" ...";
	mysqli_query_params('use `'.$core['db_name'].'`') or die(mysqli_query_error());

	$cd = prepare_cron($cron_id);
	if(!$cd['success']) exit(1);

	$users = array();
	// $log_groups = array(
	// 		1 => 'activity' //login
	// 		,2 => 'activity' //logout
	// 		,3 => 'Cases'// Add case
	// 		,4 => 'Cases' // update case
	// 		,5 => 'Cases' // delete case
	// 		,6 => 'Cases'// open case
	// 		,7 => 'Cases'// close case
	// 		,8 => 'Cases objects'// add object
	// 		,9 => 'Cases objects'// update object
	// 		,10 => 'Cases objects'// delete object
	// 		,11 => 'Cases objects'// open object
	// 		,12 => 'Cases objects'// get objects info
	// 		,13 => 'Cases files'// add file
	// 		,14 => 'Cases files'// download file
	// 		,15 => 'Cases files'// delete file	
	// 		,16 => 'Cases'// Update case security roghts
	// 		,17 => 'Cases access'// add access to case
	// 		,18 => 'Cases access'// remove access from case
	// 		,19 => 'Cases access'// grant access to case
	// 		,20 => 'Cases access'// close access to case
	// 		,21 => 'Deadlines'// add deadline
	// 		,22 => 'Deadlines'// update deadline
	// 		,23 => 'Deadlines'// complete deadline
	// 		,24 => 'Deadlines'// remove deadline
	// 		,25 => 'Deadlines'// remove deadline
	// 		,26 => 'Deadlines'// remove deadline
	// 		,27 => 'Deadlines'// remove deadline
	// );
	initLanguages();
	initTranslations();
	$res = mysqli_query_params('SELECT action_type, user_id, subject, message FROM notifications WHERE `time` < CURRENT_TIMESTAMP '.(empty($cd['last_start_time']) ? '' : ' and `time` > \''.$cd['last_start_time'].'\' ').' and user_id is not null order by user_id, `time` desc') or die(mysqli_query_error());
	while($r = $res->fetch_assoc()){
		$remind_users = array($r['user_id']);
		foreach($remind_users as $u){
			if(!isset($users[$u])){
				$res2 = mysqli_query_params('select id, sex, email, language_id, '.$_SESSION['languages']['string'].' from users_groups where id = $1', $u) or die(mysqli_query_error());
				if($r2 = $res2->fetch_assoc()) $users[$u] = $r2;
				$res2->close();
			}
			$users[$u]['mails'][] = array($r['subject'], stripslashes($r['message']));
		}
	}
	$res->close();
	foreach($users as $u){
		if(empty($u['email'])) continue;
		@$l = 'l'.$u['language_id'];
		if(!$l) $l = $GLOBALS['CB_LANGUAGE'];
		$lang = $_SESSION['languages']['per_id'][$u['language_id']]['abreviation'];
		if(filter_var($u['email'], FILTER_VALIDATE_EMAIL)){
			foreach($u['mails'] as $m){
				$message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml" lang="'.$lang.'" xml:lang="'.$lang.'">'.
					'<head><title>CaseBox</title><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /></head>'.
					'<body style="font: normal 11px tahoma,arial,helvetica,sans-serif; line-height: 18px">'.$m[1].'</body></html>';
				echo $u['email'].': '.$m[0]."\n";
				mail( $u['email'], $m[0], $message, "Content-type: text/html; charset=utf-8\r\nFrom: ".PROJ_SENDER_EMAIL . "\r\n" );
			}
		}
	}
	mysqli_query_params('update crons set last_end_time = CURRENT_TIMESTAMP, execution_info = $2 where cron_id = $1', array($cron_id, 'ok') ) or die(mysqli_query_error());
	echo " Done";
}
