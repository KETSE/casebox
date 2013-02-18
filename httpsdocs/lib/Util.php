<?php
function getIP() {
	$ip = false;
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) $ip = $_SERVER['HTTP_CLIENT_IP'];

	if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ips = explode (', ', $_SERVER['HTTP_X_FORWARDED_FOR']);
		if ($ip != false) {
			array_unshift($ips,$ip);
			$ip = false;
		}
		$count = count($ips);
		// Exclude IP addresses that are reserved for LANs
		for ($i = 0; $i < $count; $i++) {
			if (!preg_match("/^(10|172\.16|192\.168)\./i", $ips[$i])) {
				$ip = $ips[$i];
				break;
			}
		}
	}
	if (false == $ip AND isset($_SERVER['REMOTE_ADDR'])) $ip = $_SERVER['REMOTE_ADDR'];
	return $ip;
}

function getIPs() {
	$ips = array();
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) $ips[] = $_SERVER['HTTP_CLIENT_IP'];

	if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))  $ips = array_merge($ips, explode (', ', $_SERVER['HTTP_X_FORWARDED_FOR']));
	if (isset($_SERVER['REMOTE_ADDR'])) $ips[] = $_SERVER['REMOTE_ADDR'];
	return implode('|', $ips);
}

function coalesce() {
	$args = func_get_args();
	foreach($args as $a) if (!empty($a)) return $a;
	return '';
}

function formatPastTime($mysqlTime) {
	if(empty($mysqlTime)) return '';
	$time = strtotime($mysqlTime);

	$time__ = date('j n Y', $time);

	if ($time__ == date('j n Y', time())) return L\todayAt.' '.date('H:i', $time);
	elseif ($time__ == date('j n Y', time()-3600 * 24)) return L\yesterdayAt.' '.date('H:i', $time);
	elseif ($time__ == date('j n Y', time()-3600 * 24 * 2)) return L\beforeYesterdayAt.' '.date('H:i', $time);
	else return strtr(date('j M Y', $time).' '.L\at.' '.date(' H:i', $time), monthsShort());
}

function formatAgoTime($mysqlTime) {
	if(empty($mysqlTime)) return '';
	/*
	same day: few seconds ago/10 min ago /3 hours 30 min ago
	privous day: yesterday at 15:30
	same week: Tuesday at 12:20
	same year: November 8
	else: 2011, august 5

	 */

	$AHOUR = 3600; // 60 seconds * 60 minutes
	$TODAY_START = strtotime('today');
	$YESTERDAY_START = strtotime('yesterday');
	$WEEK_START = strtotime('last Sunday');
	$YEAR_START = strtotime('1 January');

	$time = strtotime($mysqlTime);
	$interval = strtotime('now') - $time;//11003
	//if(is_debug_host()) echo 'interval: '.$interval.' ('.$mysqlTime.') '.strtotime('now').'('.date('Y-m-d H:i:s').')   '.$time;
	if($interval < 0) return ''; //it's a foture time

	if($interval < $AHOUR){
		$m = intval($interval / 60);
		if($m == 0) return L\fewSecondsAgo;
		if($m < 2) return $m.' '.L\minute.' '.L\ago;
		return $m.' '.L\minutes.' '.L\ago;
	}
	if($interval < ($time - $TODAY_START) ){
		$H = intval($interval/$AHOUR);
		if($H < 2) return $H.' '.L\hour.' '.L\ago;
		return $H.' '.L\ofHours.' '.L\ago;
	}
	if($interval < ($time - $YESTERDAY_START) ){
		return L\Yesterday.' '.L\at.' '.date('H:i', $time);
	}
	if($interval < ($time - $WEEK_START) ){
		return strtr(date('l', $time), days()).' '.L\at.' '.date('H:i', $time);;
	}
	if($interval < ($time - $YEAR_START) ){
		return strtr(date('d F', $time), months());
	}
	//else 
	return strtr(date('Y, F d', $time), months());
}


function formatSpentTime($time) {
	if (empty($time)) return '';
	$t = '';
	if (!empty($time['hours'])) {
		$t = $time['hours'];
		switch ($time['hours']) {
		case 1: $t = $t.' '.L\hour; break;
		case 2:
		case 3:
		case 4: $t = $t.' '.L\hours; break;
		default: $t = $t.' '.L\ofHours; break;
		}
	}
	if (!empty($time['minutes'])) {
		$t = $t.' '.$time['minutes'];
		switch (substr($time['minutes'], -1, 1)) {
		case 1: $t = $t.' '.L\minute; break;
		case 2:
		case 3:
		case 4: $t = $t.' '.L\minutes; break;
		default: $t = $t.' '.L\ofMinutes; break;
		}
	}
	return $t;
}

function formatTaskTime($mysqlTime) {
	$time = strtotime($mysqlTime);

	$time__ = date('j n Y', $time);
	$today = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
	
	if ($time == $today) return '<span class="cM fwB">'.L\today.'</span>';
	elseif ($today - $time > 3600 * 24 * 2) return strtr(date('j M Y', $time), monthsShort());
	elseif ($today - $time > 3600 * 24) return L\beforeYesterday;
	elseif ($today - $time > 0) return L\yesterday;
	//elseif ($time - $today < 3600 * 24) return '<span class="cM">'.L\today.'</span>';
	elseif ($time - $today < 3600 * 24 * 2) return '<span class="cM fwB">'.L\tomorow.'</span>';
	elseif ($time - $today < 3600 * 24 * 6) return '<span class="cM fwB">'.(($time - $today) / (3600 * 24) ).' '.L\ofDays.'</span>';
	else return strtr(date('j M Y', $time), monthsShort());
}
function formatLeftDays($days_difference) {
	if ($days_difference == 0) return L\today;
	if($days_difference < 0) return '';
	elseif($days_difference == 1) return L\tomorow;
	elseif ($days_difference <21) return $days_difference.' '.L\ofDays;
	return '';
}

function formatMysqlDate($date, $format = false) {
	if (empty($date)) return '';
	if($format == false) $format = $_SESSION['user']['short_date_format'];
	return date(str_replace('%', '', $format), strtotime($date));
	//return implode('.', array_reverse(explode('-', substr($date, 0, 10))));
}

function formatMysqlTime($date, $format = false) {
	if (empty($date)) return '';
	if($format == false) $format = $_SESSION['user']['short_date_format'].' '.$_SESSION['user']['time_format'];
	return date(str_replace('%', '', $format), strtotime($date));
	//return implode('.', array_reverse(explode('-', substr($date, 0, 10))));
}

function clientToMysqlDate($date) {
	if (empty($date)) return null;
	$d = date_parse_from_format (str_replace('%', '', $_SESSION['user']['short_date_format']), $date);
	return $d['year'].'-'.$d['month'].'-'.$d['day'];
}
function solrToMysqlDate($date) {
	if (empty($date)) return null;
	return str_replace(array('T', 'Z'), array(' ', ''), $date);
}

function formatFileSize($v) {
	if (!is_numeric($v)) return '';
	if ($v <= 0) return  '0 KB';
	elseif($v < 1024) return '1 KB';
	elseif($v < 1024 * 1024) return round($v / 1024).' KB';
	else {
		$n = $v / (1024 * 1024);
		return number_format($n, 2).' MB';
	}
}

function validId($id = false){ return (!empty($id) && is_numeric($id) && ($id > 0)); }

function getLanguagesParams($post_params, &$result_params_array, &$values_string, &$on_duplicate_string, $default_text_value = null){
	if(is_array($post_params)) $p = &$post_params; else $p = (array)$post_params; 
	$i = sizeof($result_params_array)+1;
	foreach($_SESSION['languages']['per_id'] as $k => $v){
		$k = 'l'.$k;
		$values_string .= (empty($values_string) ? '' : ',').'$'.$i;
		$on_duplicate_string .= (empty($on_duplicate_string) ? '' : ',').'`'.$k.'`=$'.$i++;
		$result_params_array[$k] = empty($p[$k]) ? $default_text_value: $p[$k];
	}
}

function getThesauriTitles($ids_string, $language_id = false){
	if(empty($ids_string)) return '';
	if($language_id === false) $language_id = UL_ID();
	if(!is_array($ids_string)) $a = explode(',',$ids_string); else $a = &$ids_string;
	$a = array_filter($a, 'is_numeric');
	if(empty($a)) return '';
	$rez = array();
	foreach($a as $id){
		if(isset($GLOBALS['TH'][$id])) $rez[] = $GLOBALS['TH'][$id];
		else{
			$res = mysqli_query_params('select l'.$language_id.' from tags where id = $1', $id) or die(mysqli_query_error());
			if($r = $res->fetch_row()){
				$GLOBALS['TH'][$id] = $r[0];
				$rez[] = $r[0];
			}
			$res->close();
		}
	}
	if(sizeof($rez) == 1) return $rez[0];
	return $rez;
}
function getThesauryIcon($id){
	if(!is_numeric($id)) return '';
	$rez = '';
	if(isset($GLOBALS['TH_ICONS'][$id])) $rez = $GLOBALS['TH_ICONS'][$id];
	else{
		$res = mysqli_query_params('select iconCls from tags where id = $1', $id) or die(mysqli_query_error());
		if($r = $res->fetch_row()){
			$GLOBALS['TH_ICONS'][$id] = $r[0];
			$rez = $r[0];
		}
		$res->close();
	}
	return $rez;
}

function getFileExtension($filename){
	$ext = explode('.', $filename);
	if(sizeof($ext) <2 ) return '';
	$ext = array_pop($ext);
	$ext = trim($ext);
	return mb_strtolower($ext);
}
function getFileIconFile($filename){
	$ext = getFileExtension($filename);
	switch($ext){
		case 'docx':
		case 'rtf': $ext = 'doc'; break;
		case 'pptx': $ext = 'ppt'; break;
		case 'txt': $ext = 'text'; break;
		case 'html': $ext = 'htm'; break;
		case 'rm': $ext = 'mp3'; break;
		case 'gif':
		case 'jpg':
		case 'jpeg':
		case 'tif':
		case 'bmp':
		case 'png': $ext = 'img'; break;
	}
	$filename = 'document-'.$ext.'.png';
	$dir = (defined('CB_SITE_PATH') ? CB_SITE_PATH : PROJ_SITE_PATH).'css/i/ext/';
	if(file_exists($dir.$filename)) return $filename; else return 'document.png';
}
function getUsername($id){
	if(!is_numeric($id)) return '';
	$rez = '';
	$res = mysqli_query_params('select l'.UL_ID().' from users where id = $1', $id) or die(mysqli_query_error());
	if($r = $res->fetch_row()) $rez = $r[0];
	$res->close();
	return $rez;
}

function date_iso_to_mysql($date_string){
	if(empty($date_string)) return null;
	//$date_string = '2004-02-12T15:19:21+00:00';
	$d = strtotime($date_string);
	return date('Y-m-d H:i:s.u', $d);
}

function date_mysql_to_iso($date_string){
	if(empty($date_string)) return null;
	//$date_string = '2004-02-12T15:19:21+00:00';
	$d = strtotime($date_string);
	return date('Y-m-d\TH:i:s.u\Z', $d);
}

function getCoreHost($db_name = false){
	if($db_name == false) $db_name = CB_get_param('db_name');
	$core = $db_name;
	if(substr($db_name, 0, 3) == 'cb_') $core = substr($db_name, 3);
	switch($core){
		case 'cb2': $core = 'http://cb2.vvv.md/'; break;
		default: $core = 'https://'.$core.'.casebox.org/'; break;
	}
	return $core;
}

?>