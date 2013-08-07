<?php
/**
*	initialization file
*	@author Ţurcanu Vitalie <vitalie.turcanu@gmail.com>
*	@access private
*	@package CaseBox
*	@copyright Copyright (c) 2010, CaseBox
**/
namespace CB;

include dirname(__FILE__).'/config.php';
require_once 'lib/Util.php';

//Starting Session
session_start();

/* store last 10 sessions and delete older ones */
/*if(empty($_SESSION['last_sessions']) || !in_array(session_id(), $_SESSION['last_sessions'])) $_SESSION['last_sessions'][] = session_id();
while(sizeof($_SESSION['last_sessions']) > 25) @unlink(session_save_path().DIRECTORY_SEPARATOR.'sess_'.array_shift($_SESSION['last_sessions']));
/* end of store last 10 sessions and delete older ones */

//session_regenerate_id(false);

/* check if loged in correctly, comparing with the key and ips */
$arr = explode('/', $_SERVER['SCRIPT_NAME']);
$script = array_pop($arr);
if (!in_array(
    $script,
    array(
        'login.php'
        ,'router.php'
        ,'preview.php'
        ,'recover_password.php'
        ,'download.php'
        )
)) {
    $ref = @explode('/', $_SERVER['HTTP_REFERER']);
    $ref = @$ref[2];
    if ($ref != $_SERVER['SERVER_NAME']) {
        header('Location: /login.php');
        exit(0);
    }
    if (($_SERVER['SCRIPT_NAME'] != '/auth.php') && !User::isLoged()) {
        header('Location: /login.php');
        exit(0);
    }
}
/* end of check if loged in correctly, comparing with the key and ips */

/* define user_language constant /**/
$user_language = LANGUAGE;
if (!empty($_COOKIE['L']) && (strlen($_COOKIE['L']) == 2)) {
    $user_language = strtolower($_COOKIE['L']);
}
if (!empty($_GET['l']) && (strlen($_GET['l']) == 2)) {
    $user_language = strtolower($_GET['l']);
}

/*if we do not have a tanslation file for users language, we use global core language. If there is no translation file for global set language then we use english by default */
if (isset($_SESSION['user']['language']) &&
    isset($GLOBALS['language_settings'][$_SESSION['user']['language']])
    ) {
    $user_language = $_SESSION['user']['language'];
} elseif (!isset($GLOBALS['language_settings'][@$_SESSION['user']['language']])) {
    $user_language = 'en';
}
define('CB\\USER_LANGUAGE', $user_language);

/* end of define user_language constant /**/

// connecting to DB
require_once 'lib/DB.php';
DB\connect();

// include languages and define Language constants and translations
require_once 'language.php';

define('CB\\LANGUAGE_INDEX', L\getIndex(LANGUAGE));
define('CB\\USER_LANGUAGE_INDEX', L\getIndex(USER_LANGUAGE));
define('CB\\CONFIG\\LANGUAGE_FIELDS', L\languageStringToFieldNames(CONFIG\LANGUAGES));

L\initTranslations();
