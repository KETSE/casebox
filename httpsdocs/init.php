<?php
/**
*	initialization file
*	@author Ţurcanu Vitalie <vitalie.turcanu@gmail.com>
*	@access private
*	@package CaseBox
*	@copyright Copyright (c) 2010, CaseBox
**/
namespace CB;

require_once dirname(__FILE__).'/config.php';
require_once 'lib/Util.php';
require_once 'lib/DB.php';

// connect to DB
DB\connect();

//Starting Session
$sessionHandler = new Session();
session_set_save_handler($sessionHandler, true);
session_start();

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
        ,'api.php'
        )
)) {
    if (($_SERVER['SCRIPT_NAME'] != '/auth.php') && !User::isLoged()) {
        header('Location: /login.php');
        exit(0);
    }
}
/* end of check if loged in correctly, comparing with the key and ips */

// regenerate session id
session_regenerate_id(false);

/* end of regenerate session id*/

/* define user_language constant /**/
$user_language = LANGUAGE;
if (!empty($_COOKIE['L']) && (strlen($_COOKIE['L']) == 2)) {
    $user_language = strtolower($_COOKIE['L']);
}
if (!empty($_GET['l']) && (strlen($_GET['l']) == 2)) {
    $user_language = strtolower($_GET['l']);
}

/*  If we do not have a tanslation file for users language, we use global core language.
    If there is no translation file for global set language then we use english by default */
if (isset($_SESSION['user']['language']) &&
    isset($GLOBALS['language_settings'][$_SESSION['user']['language']])
    ) {
    $user_language = $_SESSION['user']['language'];
} elseif (!isset($GLOBALS['language_settings'][@$_SESSION['user']['language']])) {
    $user_language = 'en';
}
define('CB\\USER_LANGUAGE', $user_language);

/* end of define user_language constant /**/

// include languages and define Language constants and translations
require_once 'language.php';

L\initTranslations();
