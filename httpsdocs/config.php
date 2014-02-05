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
    1. Detect core name
    2. Define main paths (for configuration, files, data folder, sessions path)
    3. Read platform config.ini file
    4. read core config.ini & system.ini files
    5. based on loaded configs set casebox php options, session lifetime, error_reporting and define required casebox constants

*/

/* detecting core name (project name) from SERVER_NAME */
if (isset($_GET['c'])) {
    define('CB\\CORE_NAME', $_GET['c']);
    define('CB\\URI_PREFIX', '/'.CORE_NAME.'/');
} else {
    $arr = explode('.', $_SERVER['SERVER_NAME']);
    // remove www, ww2 and take the next parameter as the $coreName
    if (in_array($arr[0], array( 'www', 'ww2' ))) {
        array_shift($arr);
    }
    $arr = explode('-', $arr[0]);
    if (in_array($arr[sizeof($arr)-1], array('local', 'd'))) {
        array_pop($arr);
    }
    $arr = implode('-', $arr);
    $arr = explode('_', $arr);

    define('CB\\CORE_NAME', $arr[0]);
    define('CB\\URI_PREFIX', '/');
}
/* end of detecting core name (project name) from SERVER_NAME */

/* define main paths /**/
define('CB\\DOC_ROOT', dirname(__FILE__).DIRECTORY_SEPARATOR);
define('CB\\APP_DIR', dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR);
define('CB\\PLUGINS_DIR', DOC_ROOT.'plugins'.DIRECTORY_SEPARATOR);
define('CB\\CRONS_DIR', APP_DIR.'sys'.DIRECTORY_SEPARATOR.'crons'.DIRECTORY_SEPARATOR);
define('CB\\LOGS_DIR', APP_DIR.'logs'.DIRECTORY_SEPARATOR);
define('CB\\DATA_DIR', APP_DIR.'data'.DIRECTORY_SEPARATOR);
define('CB\\TEMP_DIR', DATA_DIR.'tmp'.DIRECTORY_SEPARATOR);
define('CB\\UPLOAD_TEMP_DIR', TEMP_DIR.CORE_NAME.DIRECTORY_SEPARATOR);
define('CB\\MINIFY_CACHE_DIR', TEMP_DIR.'minify'.DIRECTORY_SEPARATOR);
define('ERROR_LOG', LOGS_DIR.'cb_'.CORE_NAME.'_error_log');

/* end of define main paths /**/

/* update include_path and include global script */
set_include_path(
    DOC_ROOT.'libx'.PATH_SEPARATOR.
    DOC_ROOT.'libx'.DIRECTORY_SEPARATOR.'min'.DIRECTORY_SEPARATOR.'lib'. PATH_SEPARATOR.
    DOC_ROOT.'classes'.PATH_SEPARATOR.
    PLUGINS_DIR.PATH_SEPARATOR.
    get_include_path()
);

include 'global.php';

/* end of update include_path and include global script */

//load main config so that we can connect to casebox db and read configuration for core
$cfg = Config::loadConfigFile(DOC_ROOT.'config.ini');

require_once 'lib/DB.php';
DB\connect($cfg);

//get platform default config
$cfg = array_merge($cfg, Config::loadConfigFile(DOC_ROOT.'system.ini'));

//define default values for some params
$cfg['core_dir'] = DOC_ROOT.'cores'.DIRECTORY_SEPARATOR.CORE_NAME.DIRECTORY_SEPARATOR;
$cfg['db_name'] = 'cb_'.CORE_NAME;

//loading core defined params
$cfg = array_merge($cfg, Config::getPlatformConfigForCore());

DB\connectWithParams($cfg);

//loading full config of the core
require_once 'lib/Util.php';
$config = Config::load($cfg);

define('CB\\CORE_DIR', $config['core_dir']);
set_include_path(
    get_include_path().PATH_SEPARATOR.
    CORE_DIR
);

/* Define folder templates */

if (!empty($config['folder_templates'])) {
    $GLOBALS['folder_templates'] = explode(',', $config['folder_templates']);
    unset($config['folder_templates']);
} else {
    $GLOBALS['folder_templates'] = array();
}

if (empty($config['default_folder_template'])) {
    $config['default_folder_template'] = empty($GLOBALS['folder_templates']) ? 0 : $GLOBALS['folder_templates'][0];
}

if (empty($config['default_file_template'])) {
    $res = DB\dbQuery(
        'SELECT id
        FROM templates
        WHERE `type` = $1',
        'file'
    ) or die( DB\dbQueryError() );

    if ($r = $res->fetch_assoc()) {
        $config['default_file_template'] = $r['id'];
    } else {
        $config['default_file_template'] = 0;
    }

    $res->close();
}

/*
    store fetched config in CB\CONFIG namespace
    TODO: remove constants declaration in CB namespace and migrate to Config class usage
*/
foreach ($config as $k => $v) {
    if (( strlen($k) == 11 ) && ( substr($k, 0, 9) == 'language_')) {
        $GLOBALS['language_settings'][substr($k, 9)] = Util\toJSONArray($v);
    } elseif (is_scalar($v)) {
        define('CB\\CONFIG\\'.strtoupper($k), $v);
    }
}
define('CB\\LANGUAGES', implode(',', array_keys($GLOBALS['language_settings'])));

/* Define Core available languages in $GLOBALS */
if (defined('CB\\CONFIG\\LANGUAGES')) {
    $GLOBALS['languages'] = explode(',', CONFIG\LANGUAGES);
    for ($i=0; $i < sizeof($GLOBALS['languages']); $i++) {
        $GLOBALS['languages'][$i] = trim($GLOBALS['languages'][$i]);
    }
}

if (defined('CB\\CONFIG\\MAX_FILES_VERSION_COUNT')) {
    __autoload('CB\\Files');
    Files::setMFVC(CONFIG\MAX_FILES_VERSION_COUNT);
}
/* end of store fetched config in CB\CONFIG namespace /**/

/* So, we have defined main paths and loaded configs. Now define and configure all other options (for php, session, etc) */

/* setting php configuration options, session lifetime and error_reporting level */
ini_set('max_execution_time', 300);
ini_set('short_open_tag', 'off');

// upload params
ini_set('upload_tmp_dir', UPLOAD_TEMP_DIR);
ini_set('upload_max_filesize', '200M');
ini_set('post_max_size', '200M');
ini_set('max_file_uploads', '20');
ini_set('memory_limit', '200M');

// session params

$sessionLifetime = (isDebugHost()
        ? 0
        : Config::get('session.lifetime', 180)
    ) * 60;

ini_set("session.gc_maxlifetime", $sessionLifetime);
ini_set("session.gc_divisor", "100");
ini_set("session.gc_probability", "1");

session_set_cookie_params($sessionLifetime, '/', $_SERVER['SERVER_NAME'], !empty($_SERVER['HTTPS']), true);
session_name(
    str_replace(
        array(
            '.casebox.org'
            ,'.'
            ,'-'
        ),
        '',
        $_SERVER['SERVER_NAME']
    )
);

//error reporting params
error_reporting(isDebugHost() ? E_ALL : 0);
ini_set('error_log', ERROR_LOG);

// mb encoding config
mb_internal_encoding("UTF-8");
mb_detect_order('UTF-8,UTF-7,ASCII,EUC-JP,SJIS,eucJP-win,SJIS-win,JIS,ISO-2022-JP,WINDOWS-1251,WINDOWS-1250');
mb_substitute_character("none");

// timezone
date_default_timezone_set(
    empty($config['timezone'])
    ?
    'UTC'
    :
    $config['timezone']
);

/* end of setting php configuration options, session lifetime and error_reporting level */

/* define other constants used in casebox */

//relative path to ExtJs framework. Used in index.php
const EXT_PATH = '/libx/ext';
//templates folder. Basicly used for email templates. Used in Tasks notifications and password recovery processes.
define('CB\\TEMPLATES_DIR', APP_DIR.'sys'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR);

//used to include DB.php into PreviewExtractor scripts and in Files.php to start the extractors.
define('CB\\LIB_DIR', DOC_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR);

// Default row count limit used for solr results
if (!defined('CB\\CONFIG\\MAX_ROWS')) {
    define('CB\\CONFIG\\MAX_ROWS', 50);
}

// custom Error log per Core, use it for debug/reporting purposes
define('DEBUG_LOG', LOGS_DIR.'cb_'.CORE_NAME.'_debug_log');
//clear debug_log for each request
@unlink(DEBUG_LOG);

// define solr_core as db_name if none is specified in config
if (!defined('CB\\CONFIG\\SOLR_CORE')) {
    define('CB\\CONFIG\\SOLR_CORE', '/solr/'.CONFIG\DB_NAME);
}

// path to photos folder
define('CB\\PHOTOS_PATH', DOC_ROOT.'photos'.DIRECTORY_SEPARATOR.CORE_NAME.DIRECTORY_SEPARATOR);
// path to files folder
define('CB\\FILES_DIR', DATA_DIR.'files'.DIRECTORY_SEPARATOR.CORE_NAME.DIRECTORY_SEPARATOR);

/* path to incomming folder. In this folder files are stored when just uploaded
and before checking existance in target.
If no user intervention is required then files are stored in db. */
define('CB\\INCOMMING_FILES_DIR', UPLOAD_TEMP_DIR.'incomming'.DIRECTORY_SEPARATOR);
/* path to preview folder. Generated previews are stored for some filetypes */
define('CB\\FILES_PREVIEW_DIR', FILES_DIR.'preview'.DIRECTORY_SEPARATOR);

// define default core language constant
define(
    'CB\\LANGUAGE',
    (defined('CB\\CONFIG\\DEFAULT_LANGUAGE')
        ? CONFIG\DEFAULT_LANGUAGE
        : $GLOBALS['languages'][0]
    )
);
/* USER_LANGUAGE is defined after starting session */

/* config functions section */

/**
 * Check server side operation system
 */
function isWindows()
{
    return (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN');
}

/**
 * returns true if scripts run on a Devel server
 * @return boolean
 */
function isDevelServer()
{
    return (
        (strpos($_SERVER['SERVER_NAME'], '.d.') !== false)
        || ($_SERVER['SERVER_ADDR'] == '46.165.252.15')
    );
}

/**
 * Check if the client machine is debuging host
 * @return boolean
 */
function isDebugHost()
{
    $debugHosts = Config::get('debug_hosts');
    $debugHosts = explode(',', $debugHosts);

    return (
        empty($_SERVER['SERVER_NAME'])
        ||
        in_array(
            $_SERVER['REMOTE_ADDR'],
            $debugHosts
        )
    );
}

function debug($msg)
{
    error_log($msg."\n", 3, DEBUG_LOG);
}

/**
 * Fire server side event
 *
 * This function calls every defined listener for fired event
 */
function fireEvent($eventName, &$params)
{
    //skip trigering events from other triggers
    if (!empty($GLOBALS['running_trigger'])) {
        return;
    }

    $listeners = Config::getListeners();
    if (empty($listeners[$eventName])) {
        return;
    }

    foreach ($listeners[$eventName] as $className => $methods) {
        $className = str_replace('_', '\\', $className);
        $class = new $className();
        if (!is_array($methods)) {
            $methods = array($methods);
        }
        foreach ($methods as $method) {
            $GLOBALS['running_trigger'] = true;
            try {
                $class->$method($params);
            } catch (\Exception $e) {
                debug(
                    'Event Exception for '.$className.'->'.$method."\n".
                    $e->getMessage()."\n".
                    $e->getTraceAsString()
                );
            }
            unset($GLOBALS['running_trigger']);
        }
        unset($class);
    }
}

/**
 * get an option value from config
 *
 * config options could be defined in:
 *     user config
 *     core config
 *     default casebox config
 *
 * user config is stored in session
 *
 * default casebox config is merged with core config file and
 *     with database configuration values from config table
 * The meged result is declared in CB\CONFIG namespace
 *
 * there are also some configuration variables stored in $GLOBALS
 * (because there are no scalar values) like:
 *    language_settings - settings if defined for each language
 *    folder_templates - array of folder templates
 *    languages - avalilable languages for core
 *
 * so the value of specified option is returned from first config where is defined
 *     user config form session
 *     merged config from CB\CONFIG namespace
 *     $GLOBALS
 * If not defined in any config then null is returned
 *
 * @param  varchar $optionName name of the option to get
 * @return variant | null
 */
function getOption($optionName, $defaultValue = null)
{
    if (!empty($_SESSION['user']['cfg'][$optionName])) {
        return $_SESSION['user']['cfg'][$optionName];
    }
    if (defined('CB\\CONFIG\\'.mb_strtoupper($optionName))) {
        return constant('CB\\CONFIG\\'.mb_strtoupper($optionName));
    }
    if (!empty($GLOBALS[$optionName])) {
        return $GLOBALS[$optionName];
    }

    return $defaultValue;
}
