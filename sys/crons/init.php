<?php
/**
*	Initialization file for crons
* 
*	@author Țurcanu Vitalie <vitalie.turcanu@gmail.com>
*	@access private
*	@package CaseBox
*	@copyright Copyright (c) 2011, CaseBox
**/
	namespace CB;
	
	if(empty($argv[1])) exit(0); // if no corename argument passed then exit

	ini_set('allow_url_fopen', true);
	error_reporting(E_ALL);

	$_SERVER['SERVER_NAME'] = $argv[1];
	$_SERVER['REMOTE_ADDR'] = 'localhost';
	session_start();
	$_SESSION['user'] = array('id' => 1, 'name' => 'system');
	
	$site_path = realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'httpsdocs').DIRECTORY_SEPARATOR;
	include $site_path.DIRECTORY_SEPARATOR.'config.php';
	
	require_once LIB_DIR.'Util.php';
	
	define('CB\\USER_LANGUAGE', config\default_language);
	
	require_once(DOC_ROOT.'language.php');

	define('CB\\LANGUAGE_INDEX', L\getIndex( LANGUAGE) );
	define('CB\\USER_LANGUAGE_INDEX', L\getIndex( USER_LANGUAGE) );
	define('CB\\config\\language_fields', L\languageStringToFieldNames(config\languages));

	//L\initTranslations(); // would be called from inside crons that need translations 

	//--------------------------------------------------- functions
	function prepare_cron($cron_id, $execution_timeout = 60, $info = ''){
		$rez = array('success' => false);
		$res = DB\mysqli_query_params('select id, cron_id, last_start_time, last_end_time, (DATE_ADD(last_action, INTERVAL '.$execution_timeout.' SECOND) < CURRENT_TIMESTAMP) `timeout` from crons where cron_id = $1', array($cron_id)) or die( DB\mysqli_query_error() );
		if($r = $res->fetch_assoc()){
			if(empty($r['last_end_time'])){
				if($r['timeout'] == 0){ // seems that a cron instance is running
					echo "another cron is running\n";
					$res->close();
					return $rez;
				}else{ //timeout ocured of script cron execution
					notify_admin('CaseBox cron notification ('.$cron_id.'), timeout occured.', $info."\n\rCore name: ".CORENAME.print_r($r, 1));
				}
			}else{ //no cron is currently running

			}
			
			$rez = $r;
			$rez['success'] = true;
		}else{
			global $cron_id;
			$rez['success'] = true;
			$t = debug_backtrace();
			DB\mysqli_query_params('insert into crons (cron_id, cron_file) values($1, $2)', Array($cron_id, $t[0]['file'])) or die( DB\mysqli_query_error() );
			$rez['id'] = DB\last_insert_id();
		}
		$res->close();
		DB\mysqli_query_params('update crons set last_start_time = CURRENT_TIMESTAMP, last_end_time = NULL, last_action = CURRENT_TIMESTAMP, execution_info=NULL where id = '.$rez['id']) or die('error');
		
		return $rez;
	}
	
	function notify_admin($subject, $message){
		echo 'Notifying admin: '.ADMIN_EMAIL;
		mail(ADMIN_EMAIL, $subject, $message, 'From: '.SENDER_EMAIL. "\r\n");
	}
