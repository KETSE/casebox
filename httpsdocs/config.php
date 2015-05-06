<?php
/**
*	configuration file
*	@author Țurcanu Vitalie <vitalie.turcanu@gmail.com>
*	@access private
*	@package CaseBox
*	@copyright Copyright (c) 2013, HURIDOCS, KETSE
*	@version 2.0 refactoring 17 april 2013. Introduce CB namespace for casebox platform scripts
**/
namespace CB;

/*
    steps:
    1. include platform config
    4. Detect core name and initialize by defining specific params
    5. based on loaded configs set casebox php options, session lifetime, error_reporting and define required casebox constants

*/

require_once 'config_platform.php';

$cfg = Cache::get('platformConfig');

//detect core and define core specific params
$cfg['core_name'] = detectCore() or die('Cannot detect core');

//set default database name
$cfg['db_name'] = PREFIX . $cfg['core_name'];

//loading core defined params
try {
    $cfg = array_merge($cfg, Config::getPlatformConfigForCore($cfg['core_name']));
} catch (\Exception $e) { //return http "not found" if cant load core config
    header(@$_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
    exit();
}

DB\connectWithParams($cfg);

//loading full config of the core
$config = Config::load($cfg);

//analize core status and display corresponding message if not active
$status = Config::getCoreStatus();

if ($status != Config::$CORESTATUS_ACTIVE) {
    echo Config::getCoreStatusMessage($status);
    exit();
}

//connect other database if specified in config for core
DB\connectWithParams($config);

/**
*   So, we have defined main paths and loaded configs.
*   Now define and configure all other options (for php, session, etc)
**/

/* setting php configuration options, session lifetime and error_reporting level */
ini_set('max_execution_time', 500);
ini_set('short_open_tag', 'off');

// upload params
ini_set('upload_max_filesize', '200M');
ini_set('post_max_size', '200M');
ini_set('max_file_uploads', '20');
ini_set('memory_limit', '400M');

// session params
$sessionLifetime = (
    IS_DEBUG_HOST
        ? 0
        : Config::get('session.lifetime', 180)
) * 60;

ini_set("session.gc_maxlifetime", $sessionLifetime);
ini_set("session.gc_divisor", "100");
ini_set("session.gc_probability", "1");

session_set_cookie_params($sessionLifetime, '/' . $cfg['core_name'] . '/', $_SERVER['SERVER_NAME'], !empty($_SERVER['HTTPS']), true);
session_name(
    str_replace(
        array(
            '.casebox.org'
            ,'.'
            ,'-'
        ),
        '',
        $_SERVER['SERVER_NAME']
    ).$cfg['core_name']
);

//error reporting params
error_reporting(IS_DEBUG_HOST ? E_ALL : E_ERROR);

// mb encoding config
mb_internal_encoding("UTF-8");
mb_detect_order('UTF-8,UTF-7,ASCII,EUC-JP,SJIS,eucJP-win,SJIS-win,JIS,ISO-2022-JP,WINDOWS-1251,WINDOWS-1250');
mb_substitute_character("none");

// timezone
date_default_timezone_set('UTC');

/* end of setting php configuration options, session lifetime and error_reporting level */

//clear debug_log for each request when on debug host
if (IS_DEBUG_HOST) {
    // @unlink(Config::get('debug_log'));
}
