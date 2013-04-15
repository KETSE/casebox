<?php
	/**
	*	initialization file
	*	@author Ţurcanu Vitalie <vitalie.turcanu@gmail.com>
	*	@access private
	*	@package CaseBox
	*	@copyright Copyright (c) 2010, CaseBox
	**/
	include dirname(__FILE__).'/config.php';
	$timezone = CB_get_param('timezone');
	date_default_timezone_set( empty($timezone) ? 'Europe/Chisinau' : $timezone );

	//Starting Session
	session_save_path(CB_SESSION_PATH);
	session_name(str_replace( array('.casebox.org', '.', '-'), '', $_SERVER['SERVER_NAME']).( empty($_SESSION['user']['id']) ? '0': $_SESSION['user']['id']) );
	session_start();

	/* store last 10 sessions and delete older ones */
	/*if(empty($_SESSION['last_sessions']) || !in_array(session_id(), $_SESSION['last_sessions'])) $_SESSION['last_sessions'][] = session_id();
	while(sizeof($_SESSION['last_sessions']) > 25) @unlink(session_save_path().DIRECTORY_SEPARATOR.'sess_'.array_shift($_SESSION['last_sessions']));
	/* end of store last 10 sessions and delete older ones */

	//session_regenerate_id(false);
	/* check if loged in correctly, comparing with the key and ips */
	$a = explode('/', $_SERVER['SCRIPT_NAME']);
	$script = array_pop($a);
	if( !in_array($script, array('login.php', 'router.php', 'preview.php', 'recover_password.php') ) ){
		$ref = @explode('/', $_SERVER['HTTP_REFERER']);
		$ref = @$ref[2];
		if( $ref != $_SERVER['SERVER_NAME'] ){
			//die('here redirecting to login');
			header('Location: /login.php');
			exit(0);
		}
		if( ($_SERVER['SCRIPT_NAME'] != '/auth.php') && !is_loged()){
			//die('Script name: here redirecting to login');
			header('Location: /login.php');
			exit(0);
		}
	}
	/* end of check if loged in correctly, comparing with the key and ips */
	/* Minify */
	set_include_path(CB_SITE_PATH.'min/lib'. PATH_SEPARATOR . CB_SITE_PATH.'remote'.DIRECTORY_SEPARATOR.'classes'.PATH_SEPARATOR.CB_CONFIG_PATH.'php'. PATH_SEPARATOR.get_include_path());
	require_once('libx/min/utils.php');
	//echo get_include_path();
	/*if we do not have a tanslation file for users language, we use global core language. If there is no translation file for global set language then we use english by default */
	if( (isset($_SESSION['user']['language'])) && (isset($_SESSION['languages']['per_abrev'][$_SESSION['user']['language']])) ) $GLOBALS['USER_LANGUAGE'] = $_SESSION['user']['language'];
	elseif(!isset($_SESSION['languages']['per_abrev'][$GLOBALS['USER_LANGUAGE']])) $GLOBALS['USER_LANGUAGE'] = 'en';
	/* firstly we will include all other languages to copy them into another variable (OL = other languages) to use for log tanslation */

	/* retreiving server translations from database and defining corresponding constants /**/
	require_once('lib/DB.php');
	connect2DB();
	initTranslations();
	foreach($GLOBALS['TRANSLATIONS'][$GLOBALS['USER_LANGUAGE']] as $k => $v) define('L\\'.$k, $v);
?>