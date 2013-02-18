<?php
/**
*	configuration file
*	@author Țurcanu Vitalie <vitalie.turcanu@gmail.com>
*	@access private
*	@package CaseBox
*	@copyright Copyright (c) 2013, HURIDOCS, KETSE
**/
	/* get the host name to include its config */
	require_once('lib/Util.php');

	ini_set('max_execution_time', 300);
	ini_set('short_open_tag', 'off');

	ini_set('upload_max_filesize', '200M');
	ini_set('post_max_size', '200M');
	ini_set('max_file_uploads', '20');
	ini_set('memory_limit', '200M');
	
	$sessionLifetime = is_debug_host() ? 0: 43200;
	ini_set("session.gc_maxlifetime", $sessionLifetime);
	ini_set("session.gc_divisor", "1000");
	ini_set("session.gc_probability", "1");
	ini_set("session.cookie_lifetime", "0");
	session_set_cookie_params($sessionLifetime, '/', $_SERVER['SERVER_NAME'], !empty($_SERVER['HTTPS']), true);

	error_reporting(is_debug_host() ? E_ALL : 0);
	
	mb_internal_encoding("UTF-8");
	mb_detect_order('UTF-8,UTF-7,ASCII,EUC-JP,SJIS,eucJP-win,SJIS-win,JIS,ISO-2022-JP,WINDOWS-1251,WINDOWS-1250');

	$a = explode('.', $_SERVER['SERVER_NAME']);
	# remove www, ww2 and take the next parameter as the $coreName
	if (($a[0] == 'www') || ($a[0] == 'ww2')) array_shift($a);
	define('CB_PROJ', $a[0]);
	// define('CB_PROJ', ( ((sizeof($a)<3) || ($a[0] == 'ww2') || ($a[0] == 'www') || ($a[0] == 'casebox') ) ? '' : $a[0] ) );
	define('CB_ADMIN_EMAIL', 'support@casebox.org');

	$this_file_dir = dirname(__FILE__);
	$a = explode(DIRECTORY_SEPARATOR, $this_file_dir);
	array_pop($a);
	$parent_dir = implode(DIRECTORY_SEPARATOR, $a).DIRECTORY_SEPARATOR;

	define('CB_EXT_FOLDER', '/libx/ext');
	define('CB_SYS_PATH', $parent_dir.'sys'.DIRECTORY_SEPARATOR);
	define('CB_DATA_PATH', $parent_dir.'data'.DIRECTORY_SEPARATOR);
	define('CB_CONFIG_PATH', $this_file_dir.DIRECTORY_SEPARATOR.'cores'.DIRECTORY_SEPARATOR.CB_PROJ.DIRECTORY_SEPARATOR);
	define('CB_CRONS_PATH', CB_SYS_PATH.'crons'.DIRECTORY_SEPARATOR);
	define('CB_SITE_PATH', $this_file_dir.DIRECTORY_SEPARATOR);
	define('CB_LIB_DIR', $this_file_dir.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR);
	define('CB_LIBX_DIR', $this_file_dir.DIRECTORY_SEPARATOR.'libx'.DIRECTORY_SEPARATOR);
	define('CB_JS_LOCALE_PATH', $this_file_dir.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'locale');
	define('CB_TEMPLATES_PATH', CB_SYS_PATH.'templates'.DIRECTORY_SEPARATOR);
	define('CB_PERSONAL_TAGS', false); /* Default property for using personal tags or not in this core */
	define('CB_SYSTEM_TAGS', true); /* Default property for using personal tags or not in this core */
	define('CB_DEFAULT_MAX_ROWS', 50); /* Default row count limit used in grids */
	
	/* reading core settings */
	global $CB_settings;
	$CB_settings = array('timezone' => 'UTC', 'default_language' => 'en', 'personal_tags' => false, 'system_tags' => true);

	if(file_exists(CB_CONFIG_PATH.'system.ini')){
		$CB_settings = array_merge( $CB_settings, parse_ini_file(CB_CONFIG_PATH.'system.ini'));
	}else die(header('location: http://www.casebox.org'));
	
	if(file_exists(CB_CONFIG_PATH.'config.ini')){
		$CB_settings = array_merge( $CB_settings, parse_ini_file(CB_CONFIG_PATH.'config.ini') );
	}/* end of reading core settings */

	// custom Error log per Core, use it for debug/reporting purposes
	define('CB_ERRORLOG', $parent_dir.'logs'.DIRECTORY_SEPARATOR.'cb_'.CB_PROJ.'_log');

	// UNIX: '/usr/local/sbin/unoconv'
	define('CB_UNOCONV', '"c:\\Program Files (x86)\\LibreOffice 4.0\\program\\python.exe" c:\\opt\\unoconv\\unoconv'); 

	define('CB_HTML_PURIFIER', CB_LIBX_DIR.'htmlpurifier'.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.'HTMLPurifier.auto.php');
	define('CB_PDF2SWF_PATH', file_exists('d:\\soft\\SWFTools\\') ? 'd:\\soft\\SWFTools\\' : '/usr/local/bin/');

	define('CB_SOLR_CLIENT', CB_LIBX_DIR.'Solr/Service.php');
	define('CB_SOLR_HOST', coalesce(CB_get_param('solr_host'), '127.0.0.1' ) );
	define('CB_SOLR_PORT', coalesce(CB_get_param('solr_port'), 8983 ) );
	define('CB_SOLR_CORE', coalesce(CB_get_param('solr_core'), '/solr/'.$CB_settings['db_name'] ) );
	define('CB_MAX_ROWS', coalesce(CB_get_param('max_rows'), CB_DEFAULT_MAX_ROWS ) );
	
	
	define('CB_SESSION_PATH', CB_DATA_PATH.'sessions'.DIRECTORY_SEPARATOR.CB_PROJ.DIRECTORY_SEPARATOR);

	define('CB_PHOTOS_PATH', CB_SITE_PATH.'photos'.DIRECTORY_SEPARATOR.CB_PROJ.DIRECTORY_SEPARATOR);
	if(!defined('CB_FILES_PATH')) define('CB_FILES_PATH', CB_DATA_PATH.'files'.DIRECTORY_SEPARATOR.CB_PROJ.DIRECTORY_SEPARATOR);
	define('CB_FILES_INCOMMING_PATH', CB_FILES_PATH.'incomming'.DIRECTORY_SEPARATOR);
	define('CB_FILES_PREVIEW_PATH', CB_FILES_PATH.'preview'.DIRECTORY_SEPARATOR);
	define('CB_FILES_DELETED_PATH', CB_FILES_PATH.'deleted'.DIRECTORY_SEPARATOR);

	if(!file_exists(CB_SESSION_PATH)) @mkdir(CB_SESSION_PATH, 0755, true);
	// if(is_debug_host()){
	// 	if(!file_exists(CB_PHOTOS_PATH)) @mkdir(CB_PHOTOS_PATH, 0755, true);
	// 	if(!file_exists(CB_FILES_INCOMMING_PATH)) @mkdir(CB_FILES_INCOMMING_PATH, 0777, true);
	// 	if(!file_exists(CB_FILES_PREVIEW_PATH)) @mkdir(CB_FILES_PREVIEW_PATH, 0777, true);
	// 	if(!file_exists(CB_FILES_DELETED_PATH)) @mkdir(CB_FILES_DELETED_PATH, 0755, true);
	// 	die(CB_PHOTOS_PATH);
	// }
	if(!file_exists(CB_PHOTOS_PATH)) @mkdir(CB_PHOTOS_PATH, 0755, true);
	if(!file_exists(CB_FILES_INCOMMING_PATH)) @mkdir(CB_FILES_INCOMMING_PATH, 0777, true);
	if(!file_exists(CB_FILES_PREVIEW_PATH)) @mkdir(CB_FILES_PREVIEW_PATH, 0777, true);
	if(!file_exists(CB_FILES_DELETED_PATH)) @mkdir(CB_FILES_DELETED_PATH, 0755, true);

	$GLOBALS['CB_LANGUAGE'] = CB_get_param('default_language');
	$GLOBALS['USER_LANGUAGE'] = $GLOBALS['CB_LANGUAGE'];
	if(!empty($_COOKIE['L']) && (strlen($_COOKIE['L']) == 2)) $GLOBALS['USER_LANGUAGE'] = strtolower($_COOKIE['L']);
	if(!empty($_GET['l']) && (strlen($_GET['l']) == 2)) $GLOBALS['USER_LANGUAGE'] = strtolower($_GET['l']);
	
	function CB_get_param($name){
		global $CB_settings;
		if(empty($name)) return false;
		if(!isset($CB_settings[$name])) return null;
		return $CB_settings[$name];
	}
	/* function to get the translation value, if defined, for custom specified language. if langiage not specified we return the translation for current user language  /**/
	function L($name = false, $language = false){ //
		if(empty($name)) return null;
		if(empty($language)) return (defined('L\\'.$name) ? constant('L\\'.$name) : null);
		else{
			if(($language[0] == 'l') && (is_numeric($language[1]))) $language = substr($language, 1); // case when we receive laguage as "l{id}"
			if(is_numeric($language)) $language = $_SESSION['languages']['per_id'][$language]['abreviation'];
			return (isset($GLOBALS['TRANSLATIONS'][$language][$name]) ? $GLOBALS['TRANSLATIONS'][$language][$name] : null);
		}
	}
	function UL(){ //return user language
		//if(isset($_SESSION['languages']['per_id'])) return $_SESSION['languages']['per_abrev']['id'];
		return $GLOBALS['USER_LANGUAGE'];
	}
	function UL_ID($language_abrev = false){
		if(empty($language_abrev)) $language_abrev = UL();
		if(!isset($_SESSION['languages']['per_abrev'][$language_abrev]['id'])){
			$lang_id = null;
			$sql = 'select id from languages where abreviation = $1';
			$res = mysqli_query_params($sql, $language_abrev) or die(mysqli_query_error());
			if($r = $res->fetch_row()) $lang_id = $r[0];
			$res->close();
			return $lang_id;
		}
		return $_SESSION['languages']['per_abrev'][$language_abrev]['id'];
	}
	function is_debug_host(){
		return (empty($_SERVER['SERVER_NAME']) || ($_SERVER['SERVER_NAME'] == 'casebox.vvv.md') || in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1','46.55.49.126', '93.116.243.178', '195.22.253.6', '193.226.64.181', '188.240.73.107', '109.185.172.018')));
	}
	function is_loged(){
		return ( !empty($_COOKIE['key']) && !empty($_SESSION['key']) && !empty($_SESSION['ips']) && !empty($_SESSION['user']) &&  ($_COOKIE['key'] == $_SESSION['key']) && ('|'.getIPs().'|' == $_SESSION['ips']) );
	}
	function is_windows(){
		return (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN');
	}
	function monthsShort($language = false){
		if($language === false) $language = $GLOBALS['USER_LANGUAGE'];
		switch($language){
			case 'ro': return array('Jan' => 'Ian', 'Feb' => 'Feb', 'Mar' => 'Mar', 'Apr' => 'Apr', 'May' => 'Mai', 'Jun' => 'Iun', 'Jul' => 'Iul', 'Aug' => 'Aug', 'Sep' => 'Sep', 'Oct' => 'Oct', 'Nov' => 'Noi', 'Dec' => 'Dec'); break;
			case 'ru': return array('Jan' => 'Янв', 'Feb' => 'Фев', 'Mar' => 'Марта', 'Apr' => 'Апр', 'May' => 'Мая', 'Jun' => 'Июня', 'Jul' => 'Июля', 'Aug' => 'Авг', 'Sep' => 'Сент', 'Oct' => 'Окт', 'Nov' => 'Ноября', 'Dec' => 'Дек'); break;
			case 'hy': return array('Jan' => 'Հուն', 'Feb' => 'Փետ', 'Mar' => 'ապականել', 'Apr' => 'Ապ', 'May' => 'մայիս', 'Jun' => 'Հուն', 'Jul' => 'հլս', 'Aug' => 'օգս', 'Sep' => 'սեպ', 'Oct' => 'հոկ', 'Nov' => 'նոյ', 'Dec' => 'դեկ'); break;
			default : return array('Jan' => 'Jan', 'Feb' => 'Feb', 'Mar' => 'Mar', 'Apr' => 'Apr', 'May' => 'May', 'Jun' => 'Jun', 'Jul' => 'Jul', 'Aug' => 'Aug', 'Sep' => 'Sep', 'Oct' => 'Oct', 'Nov' => 'Nov', 'Dec' => 'Dec');
		}
	}
	function months($language = false){
		if($language === false) $language = $GLOBALS['USER_LANGUAGE'];
		switch($language){
			case 'ro': return array('January' => 'Ianuarie', 'February' => 'Februarie', 'March' => 'Martie', 'April' => 'Aprilie', 'May' => 'Mai', 'June' => 'Iunie', 'July' => 'Iulie', 'August' => 'August', 'September' => 'Septembrie', 'October' => 'Octombrie', 'November' => 'Noiembrie', 'December' => 'Dec'); break;
			case 'ru': return array('January' => 'Января', 'February' => 'Февраля', 'March' => 'Марта', 'April' => 'Апреля', 'May' => 'Мая', 'June' => 'Июня', 'July' => 'Июля', 'August' => 'Августа', 'September' => 'Сентября', 'October' => 'Октября', 'November' => 'Ноября', 'December' => 'Декабря'); break;
			case 'hy': return array('January' => 'Հուն', 'February' => 'Փետ', 'March' => 'ապականել', 'April' => 'Ապ', 'May' => 'մայիս', 'June' => 'Հուն', 'July' => 'հլս', 'August' => 'օգս', 'September' => 'սեպ', 'October' => 'հոկ', 'November' => 'նոյ', 'December' => 'դեկ'); break;
			default : return array('January' => 'January', 'February' => 'February', 'March' => 'March', 'April' => 'April', 'May' => 'May', 'June' => 'June', 'July' => 'July', 'August' => 'August', 'September' => 'September', 'October' => 'October', 'November' => 'November', 'December' => 'December');
		}
	}

	function daysShort($language = false){
		if($language === false) $language = $GLOBALS['USER_LANGUAGE'];
		switch($language){
			case 'ro': return array('Mon' => 'Luni', 'Tue' => 'Mar', 'Wed' => 'Mer', 'Thu' => 'Joi', 'Fri' => 'Vin', 'Sat' => 'Sâm', 'Sun' => 'Dum'); break;
			case 'ru': return array('Mon' => 'Пон', 'Tue' => 'Вт', 'Wed' => 'Ср', 'Thu' => 'Чет', 'Fri' => 'Пят', 'Sat' => 'Суб', 'Sun' => 'Вос'); break;
			//case 'hy': return array('Mon' => 'Mon', 'Tue' => 'Tue', 'Wed' => 'Wed', 'Thu' => 'Thu', 'Fri' => 'Fri', 'Sat' => 'Sat', 'Sun' => 'Sun'); break;
			default : return array('Mon' => 'Mon', 'Tue' => 'Tue', 'Wed' => 'Wed', 'Thu' => 'Thu', 'Fri' => 'Fri', 'Sat' => 'Sat', 'Sun' => 'Sun');
		}
	}

	function days($language = false){
		if($language === false) $language = $GLOBALS['USER_LANGUAGE'];
		switch($language){
			case 'ro': return array('Monday' => 'Luni', 'Tuesday' => 'Marţi', 'Wednesday' => 'Mercuri', 'Thursday' => 'Joi', 'Friday' => 'Vineri', 'Saturday' => 'Sâmbătă', 'Sunday' => 'Duminică'); break;
			case 'ru': return array('Monday' => 'Понедельник', 'Tuesday' => 'Вторник', 'Wednesday' => 'Среда', 'Thursday' => 'Четверг', 'Friday' => 'Пятница', 'Saturday' => 'Субота', 'Sunday' => 'Воскресенье'); break;
			//case 'hy': return array('Monday' => 'Monday', 'Tuesday' => 'Tuesday', 'Wednesday' => 'Wednesday', 'Thursday' => 'Thursday', 'Friday' => 'Friday', 'Saturday' => 'Saturday', 'Sunday' => 'Sunday'); break;
			default : return array('Monday' => 'Monday', 'Tuesday' => 'Tuesday', 'Wednesday' => 'Wednesday', 'Thursday' => 'Thursday', 'Friday' => 'Friday', 'Saturday' => 'Saturday', 'Sunday' => 'Sunday');
		}
	}

	function initTranslations(){
		if(isset($GLOBALS['TRANSLATIONS'])) return;
		$lstr_abrev = isset($_SESSION['languages']['per_abrev'])  ? implode(',',array_keys($_SESSION['languages']['per_abrev'])) : $GLOBALS['USER_LANGUAGE'];
		/* reading global translations */
		$res = mysqli_query_params('select name, '.$lstr_abrev.' from `casebox`.translations where `type` < 2') or die(mysqli_query_error());
		while($r = $res->fetch_assoc()){
			reset($r);
			$name = current($r);
			while($v = next($r)) $GLOBALS['TRANSLATIONS'][key($r)][$name] = $v;
		}
		$res->close();
		/* reading specific translations of core */
		$lstr = '';
		if(!isset($_SESSION['languages'])){
			$res = mysqli_query_params('select id from languages where `abreviation` = $1', $GLOBALS['USER_LANGUAGE']) or die(mysqli_query_error());
			if($r = $res->fetch_row()) $lstr = 'l'.$r[0];
			$res->close();
		}else $lstr = $_SESSION['languages']['string'];
		
		$res = mysqli_query_params('select name, '.$lstr.' from translations where `type` < 2') or die(mysqli_query_error());
		while($r = $res->fetch_assoc()){
			reset($r);
			$name = current($r);
			while( ($v = next($r)) !== false ){
				$l = substr(key($r), 1);
				$l = isset($_SESSION['languages']['per_id'][$l]['abreviation']) ? $_SESSION['languages']['per_id'][$l]['abreviation'] : $GLOBALS['USER_LANGUAGE'];
				$GLOBALS['TRANSLATIONS'][$l][$name] = $v;
			}
		}
		$res->close();
		/* verifying if localization JS file for current user language is up to date */
		$last_translations_update_date = null;
		$sql = 'SELECT MAX(udate) FROM (SELECT MAX(udate) `udate` FROM casebox.translations UNION SELECT MAX(udate) FROM translations) t';
		$res = mysqli_query_params($sql) or die(mysqli_query_error());
		if($r = $res->fetch_row()) $last_translations_update_date = strtotime($r[0]);
		$res->close();

		if(!empty($last_translations_update_date)){
			$locale_filename = CB_JS_LOCALE_PATH.DIRECTORY_SEPARATOR.UL().'.js';
			$create_locale_files = file_exists($locale_filename) ? (filemtime($locale_filename) < $last_translations_update_date) : true;
			if($create_locale_files){
				$rez = array();
				$res = mysqli_query_params('select name, en, fr, ro, ru, hy from `casebox`.translations where `type` in (0,2)') or die(mysqli_query_error());
				while($r = $res->fetch_assoc()){
					reset($r);
					$name = current($r);
					while(($v = next($r)) !== false) $rez[key($r)][] = "'".$name."':'".addcslashes($v,"'")."'";
				}
				$res->close();
				foreach($rez as $l => $v){
					$filename = CB_JS_LOCALE_PATH.DIRECTORY_SEPARATOR.$l.'.js' ;
					if(file_exists($filename)) unlink($filename);
					file_put_contents($filename, 'L = {'.implode(',', $v).'}');
				}
			}
		}
		/* end of verifying if localization JS file for current user language is up to date */
	}
	
	function getMFVC($filename){//get Max File Version Count for an extension
		$ext = getFileExtension($filename) || mb_strtolower( $filename);
		$ext = trim($ext);
		$rez = 0;
		if(empty($_SESSION['mfvc'])) return $rez;
		$ext = mb_strtolower($ext);
		if(isset($_SESSION['mfvc'][$ext])) return $_SESSION['mfvc'][$ext];
		if(isset($_SESSION['mfvc']['*'])) return $_SESSION['mfvc']['*'];
		return $rez;
	}

	function __autoload($class_name) {
    		require_once $class_name . '.php';
	}
?>