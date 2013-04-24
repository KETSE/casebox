<?php
namespace CB\L;
use CB\config as config;
use CB\DB as DB;

/* function to get the translation value, if defined, for custom specified language.
	If langiage not specified we return the translation for current user language  /**/
function get($name = false, $language = false){ //
	if(empty($name)) return null;
	if(empty($language)) return (defined('CB\\L\\'.$name) ? constant('CB\\L\\'.$name) : null);
	else{
		if(($language[0] == 'l') && (is_numeric($language[1]))) $language = substr($language, 1); // case when we receive laguage as "l{index}"
		if(is_numeric($language)) $language = $GLOBALS['languages'][ $language -1 ];
		return (isset($GLOBALS['TRANSLATIONS'][$language][$name]) ? $GLOBALS['TRANSLATIONS'][$language][$name] : null);
	}
}

// get index for user language in core defined languages
function getIndex($language_abrev = false){
	$rez = null;
	if(empty($language_abrev)) $language_abrev = \CB\USER_LANGUAGE;
	$idx = array_search($language_abrev, $GLOBALS['languages']);
	if($idx !== false) $rez = $idx + 1; 
	return $rez;
}
/**
 * Translate comma separated languages string to corresponding field names from database
 */
function languageStringToFieldNames($languagesString){
	$rez = array();
	$languages = explode(',', $languagesString);
	for ($i=0; $i < sizeof($languages); $i++) { 
		$rez[] = 'l'.getIndex(trim($languages[$i]));
	}
	return implode(',', $rez);
}
/**
 * function for defining translations into $GLOBAL['TRANSLATIONS'] and recreating language files if updated
 */
function initTranslations(){
	// if already defined translations then exit
	if(isset($GLOBALS['TRANSLATIONS'])) return;
	
	$languages = defined( 'CB\\config\\languages' )  ? config\languages : \CB\USER_LANGUAGE;
	
	/* reading global translations table from casebox database*/
	$res = DB\mysqli_query_params('select name, '.$languages.' from `casebox`.translations where `type` < 2') or die( DB\mysqli_query_error() );
	while($r = $res->fetch_assoc()){
		reset($r);
		$name = current($r);
		while($v = next($r)) $GLOBALS['TRANSLATIONS'][key($r)][$name] = $v;
	}
	$res->close();
	
	
	/* reading specific translations of core */
	$res = DB\mysqli_query_params('select name, '.config\language_fields.' from translations where `type` < 2') or die( DB\mysqli_query_error() );
	while($r = $res->fetch_assoc()){
		reset($r);
		$name = current($r);
		while( ($v = next($r)) !== false ){
			$l = substr(key($r), 1);
			$GLOBALS['TRANSLATIONS'][$GLOBALS['languages'][$l-1]][$name] = $v;
		}
	}
	$res->close();

	foreach($GLOBALS['TRANSLATIONS'][\CB\USER_LANGUAGE] as $k => $v) define('CB\\L\\'.$k, $v);
}

function checkTranslationsUpToDate(){
	/* verifying if localization JS file for current user language is up to date */
	$last_translations_update_date = null;
	$sql = 'SELECT MAX(udate) FROM (SELECT MAX(udate) `udate` FROM casebox.translations UNION SELECT MAX(udate) FROM translations) t';
	$res = DB\mysqli_query_params($sql) or die( DB\mysqli_query_error() );
	if($r = $res->fetch_row()) $last_translations_update_date = strtotime($r[0]);
	$res->close();

	if(!empty($last_translations_update_date)){
		$locale_filename = \CB\DOC_ROOT.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'locale'.DIRECTORY_SEPARATOR.\CB\USER_LANGUAGE.'.js';
		$create_locale_files = file_exists($locale_filename) ? (filemtime($locale_filename) < $last_translations_update_date) : true;	
		if($create_locale_files) updateTranslationsFiles();
	}
	/* end of verifying if localization JS file for current user language is up to date */
}

function updateTranslationsFiles(){
	$rez = array();
	$res = DB\mysqli_query_params('select name, '.\CB\LANGUAGES.' from `casebox`.translations where `type` in (0,2)') or die( DB\mysqli_query_error() );
	while($r = $res->fetch_assoc()){
		reset($r);
		$name = current($r);
		while(($v = next($r)) !== false) $rez[key($r)][] = "'".$name."':'".addcslashes($v,"'")."'";
	}
	$res->close();
	foreach($rez as $l => $v){
		$filename = \CB\DOC_ROOT.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'locale'.DIRECTORY_SEPARATOR.$l.'.js' ;
		if(file_exists($filename)) unlink($filename);
		file_put_contents($filename, 'L = {'.implode(',', $v).'}');
	}
}