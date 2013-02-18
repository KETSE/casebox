<?php
/**
*	initialization file for crons
*	@author Țurcanu Vitalie <vitalie.turcanu@gmail.com>
*	@access private
*	@package CaseBox CMS
*	@copyright Copyright (c) 2011, CaseBox
**/
	ini_set('mbstring.substitute_character', "none");
	ini_set('memory_limit', '200M');
	ini_set('allow_url_fopen', true);

	date_default_timezone_set( 'UTC' );
	
	define('PROJ_PRIVATE_PATH', realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.'..').DIRECTORY_SEPARATOR);
	define('CB_CRONS_PATH', realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR);
	
	$sp = realpath(PROJ_PRIVATE_PATH.'..'.DIRECTORY_SEPARATOR.'html');
	if($sp == false) $sp = realpath(PROJ_PRIVATE_PATH.'..'.DIRECTORY_SEPARATOR.'httpsdocs');
	if($sp == false) die('cannot locate site path');
	define('PROJ_SITE_PATH', $sp . DIRECTORY_SEPARATOR );
	define('PROJ_LIB_DIR', PROJ_SITE_PATH.'lib'.DIRECTORY_SEPARATOR);
	define('PROJ_LIBX_DIR', PROJ_SITE_PATH.'libx'.DIRECTORY_SEPARATOR);
	define('PROJ_CLASSES_DIR', PROJ_SITE_PATH.'remote'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR);

	define('CB_TEMPLATES_PATH', PROJ_PRIVATE_PATH.'templates'.DIRECTORY_SEPARATOR);
	define('PROJ_ADMIN_EMAIL', 'vitalie.turcanu@gmail.com');
	define('PROJ_SENDER_EMAIL', 'admin@casebox.org');
	define('PROJ_CONFIGS_PATH', realpath(PROJ_SITE_PATH.'cores').DIRECTORY_SEPARATOR);
	define('PROJ_FILES_PATH', realpath(PROJ_PRIVATE_PATH.'..').DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR);
	
	define('ZEND_PATH', PROJ_LIBX_DIR.'ZendFramework-minimal-2.0.1'.DIRECTORY_SEPARATOR.'library');
	define('TIKA_APP', PROJ_LIBX_DIR.'tika-app-1.2.jar');

	//define('CB_SOLR_CLIENT', file_exists('/var/lib/Apache/SolrPhpClient/Apache/Solr/Service.php') ? '/var/lib/Apache/SolrPhpClient/Apache/Solr/Service.php' : 'd:\devel\www\lib\SolrPhpClient\Apache\Solr\Service.php');
	define('CB_SOLR_CLIENT', PROJ_LIBX_DIR.'Solr/Service.php');
	define('CB_SOLR_HOST', '127.0.0.1' );
	define('CB_SOLR_PORT', 8983 );
	define('CB_SOLR_CORE', 'variable' ); //core will be passed to solr client as parameter to constructor

	set_include_path(get_include_path() . PATH_SEPARATOR . '/var/lib'. PATH_SEPARATOR . PROJ_SITE_PATH.'remote'.DIRECTORY_SEPARATOR.'classes' );
	//die(get_include_path());
	$CB_cores = Array();
	foreach (new DirectoryIterator(PROJ_CONFIGS_PATH) as $file){
	   $name =$file->getFilename();
	   if($name == 'sample') continue;
	   if ( !$file->isDot() && $file->isDir() && (empty($argv[1]) || ( $argv[1] == $name ) ) ){
	   	$config = array('timezone' => 'UTC', 'default_language' => 'en', 'personal_tags' => false, 'system_tags' => true);
	   	$fn = PROJ_CONFIGS_PATH.$name.DIRECTORY_SEPARATOR.'system.ini';
	   	if(file_exists($fn)) $config = array_merge($config, parse_ini_file($fn));
	   	$fn = PROJ_CONFIGS_PATH.$name.DIRECTORY_SEPARATOR.'config.ini';
	   	if(file_exists($fn)) $config = array_merge($config, parse_ini_file($fn));
	   	$config['name'] = $name;
	   	$CB_cores[$name] = $config;
	   }
	}

	if(empty($CB_cores)) die(' no cores found');

	global $CB_settings;
	$CB_settings = current($CB_cores);
	if(!function_exists('CB_get_param')){
		function CB_get_param($name){
			global $CB_settings;
			if(empty($name)) return false;
			if(!isset($CB_settings[$name])) return '';
			return $CB_settings[$name];
		}
	}

	$GLOBALS['CB_LANGUAGE'] = CB_get_param('default_language');

	set_include_path(get_include_path() . PATH_SEPARATOR . '/var/lib');
	$_SESSION['user'] = array('id' => 1, 'name' => 'system', 'language_id' => 1);
	require_once PROJ_LIB_DIR.'Util.php';
	global $dbh;
	include PROJ_LIB_DIR.'DB.php';
	//include DIRECTORY_SEPARATOR.'SolrClient.php';
	$dbh = connect2DB();
	//---------------------------------------------------
	function prepare_cron($cron_id, $execution_skip_times = 1, $info = ''){
		global $dbh;
		$rez = array('success' => false);
		$res = mysqli_query_params('select id, cron_id, last_start_time, last_end_time, execution_skip_times from crons where cron_id = $1', array($cron_id)) or die(mysqli_query_error());
		if($r = $res->fetch_assoc()){
			if(empty($r['last_end_time']) && ($r['execution_skip_times'] < $execution_skip_times)){
			  // seems that last execution of cron did not finish yet 
			  mysqli_query_params('update crons set execution_skip_times = COALESCE(execution_skip_times, 0) + 1 where id = '.$r['id']) or die(mysqli_query_error());

			  notify_admin('CaseBox cron notification ('.$cron_id.'), '.($r['execution_skip_times'] + 1).' skipping(s).', $info."\n\r".print_r($r, 1));
			  return $rez;
			}
			$rez = $r;
			$rez['success'] = true;
		}else{
			global $cron_id;
			global $update_all;
			$update_all = 1;
			$rez['success'] = true;
			$t = debug_backtrace();
			mysqli_query_params('insert into crons (cron_id, cron_file) values($1, $2)', Array($cron_id, $t[0]['file'])) or die(mysqli_query_error());
			$rez['id'] = $dbh->insert_id;
		}
		$res->close();
		mysqli_query_params('update crons set last_start_time = CURRENT_TIMESTAMP, last_end_time = NULL, execution_skip_times = 0, execution_info=NULL where id = '.$rez['id']) or die('error');
		
		return $rez;
	}
	
	// function formatMysqlDate($d, $format){
	// 	if(empty($d)) return '';
	// 	$d = explode('-',array_shift(explode(' ', $d)));
	// 	return utf8_encode(ucfirst(strftime($format, mktime(0, 0, 0, $d[1], $d[2], $d[0]))));
	// }	
	
	function notify_admin($subject, $message){
		echo 'notifying admin: '.PROJ_ADMIN_EMAIL;
		mail(PROJ_ADMIN_EMAIL, $subject, $message, 'From: casebox@burlaca.com' . "\r\n");
	}
	function is_debug_host(){return true;}

	function L($name = false, $language = false){ //
		if(empty($name)) return null;
		if(empty($language)) return (defined('L\\'.$name) ? constant('L\\'.$name) : null);
		else{
			if(($language[0] == 'l') && (is_numeric(@$language[1]))) $language = substr($language, 1); // case when we receive laguage as "l{id}"
			if(is_numeric($language)) $language = $GLOBALS['languages']['per_id'][$language]['abreviation'];
			return (isset($GLOBALS['TRANSLATIONS'][$language][$name]) ? $GLOBALS['TRANSLATIONS'][$language][$name] : null);
		}
	}

	function initLanguages(){
		/*fetching core languages and store them in the session for any use */
		$GLOBALS['languages'] = array('per_id' => array(), 'per_abrev' => array(), 'string' => '', 'count' => 0);
		$sql = 'SELECT id, name, abreviation, locale, long_date_format, short_date_format, time_format FROM languages order by name';
		$res = mysqli_query_params($sql) or die(mysqli_query_error());
		while($r = $res->fetch_assoc()){
			$GLOBALS['languages']['per_id'][$r['id']] = $r;
			$GLOBALS['languages']['per_abrev'][$r['abreviation']] = &$GLOBALS['languages']['per_id'][$r['id']];
		}
		$res->close();
		$GLOBALS['languages']['count'] = sizeof($GLOBALS['languages']['per_id']);
		$GLOBALS['languages']['string'] = 'l'.implode(',l', array_keys($GLOBALS['languages']['per_id']));
		$_SESSION['languages'] = &$GLOBALS['languages'];
		/*end of fetching core languages and store them in the session for any use */
	}
	function initTranslations(){
		if(isset($GLOBALS['TRANSLATIONS'])) return;
		$res = mysqli_query_params('select name, '.implode(',',array_keys($GLOBALS['languages']['per_abrev'])).' from casebox.translations where `type` < 2') or die(mysqli_query_error());
		while($r = $res->fetch_assoc()){
			reset($r);
			$name = current($r);
			while($v = next($r)) $GLOBALS['TRANSLATIONS'][key($r)][$name] = $v;
		}
		$res->close();
		/* reading specific translations of core */
		$res = mysqli_query_params('select name, '.$GLOBALS['languages']['string'].' from translations where `type` < 2') or die(mysqli_query_error());
		while($r = $res->fetch_assoc()){
			reset($r);
			$name = current($r);
			while($v = next($r)){
				$l = substr(key($r), 1);
				$l = $GLOBALS['languages']['per_id'][$l]['abreviation'];
				$GLOBALS['TRANSLATIONS'][$l][$name] = $v;
			}
		}
		$res->close();
	}
	
	// function coalesce() {
	// 	$args = func_get_args();
	// 	foreach ($args as $arg)
	// 		if (!empty($arg)) return $arg;
	// 	return $args[0];
	// }
	function __autoload($class_name) {
    		require_once $class_name . '.php';
	}
?>