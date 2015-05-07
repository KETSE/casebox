<?php
/**
*	configuration file
*	@package CaseBox
*	@copyright Copyright (c) 2014, HURIDOCS, KETSE
**/
namespace CB;

/* define main paths/**/
define('CB\\DOC_ROOT', dirname(__FILE__).DIRECTORY_SEPARATOR);
define('CB\\APP_DIR', dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR);
define('CB\\PLUGINS_DIR', DOC_ROOT.'plugins'.DIRECTORY_SEPARATOR);
define('CB\\SYS_DIR', APP_DIR.'sys'.DIRECTORY_SEPARATOR);
define('CB\\CRONS_DIR', SYS_DIR.'crons'.DIRECTORY_SEPARATOR);
define('CB\\LOGS_DIR', APP_DIR.'logs'.DIRECTORY_SEPARATOR);
define('CB\\DATA_DIR', APP_DIR.'data'.DIRECTORY_SEPARATOR);
define('CB\\TEMP_DIR', DATA_DIR.'tmp'.DIRECTORY_SEPARATOR);
define('CB\\MINIFY_CACHE_DIR', TEMP_DIR.'minify'.DIRECTORY_SEPARATOR);
//templates folder. Basicly used for email templates. Used in Tasks notifications and password recovery processes.
define('CB\\TEMPLATES_DIR', SYS_DIR.'templates'.DIRECTORY_SEPARATOR);
//used to include DB.php into PreviewExtractor scripts and in Files.php to start the extractors.
define('CB\\LIB_DIR', DOC_ROOT.'lib'.DIRECTORY_SEPARATOR);
define('CB\\ZEND_PATH', DOC_ROOT.'libx'.DIRECTORY_SEPARATOR.'ZF'.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR);

define('CB\\IS_WINDOWS', strtoupper(substr(PHP_OS, 0, 3)) == 'WIN');

// define casebox include path
// This path contains only CaseBox platform inclusion paths
//  and do not contain core specific paths
define(
    'CB\\INCLUDE_PATH',
    // DOC_ROOT.PATH_SEPARATOR.
    DOC_ROOT.'libx'.PATH_SEPARATOR.
    DOC_ROOT.'libx'.DIRECTORY_SEPARATOR.'min'.DIRECTORY_SEPARATOR.'lib'. PATH_SEPARATOR.
    ZEND_PATH. PATH_SEPARATOR.
    DOC_ROOT.'classes'.PATH_SEPARATOR.
    PLUGINS_DIR.PATH_SEPARATOR.
    get_include_path()
);

//relative path to ExtJs framework. Used in index.php
define('CB\\EXT_PATH', '/libx/ext');

/* end of define main paths /**/

/* update include_path and include scripts */
set_include_path(INCLUDE_PATH);

include LIB_DIR . 'global.php';
require_once LIB_DIR . 'Util.php';
require_once LIB_DIR . 'DB.php';

/* end of update include_path and include scripts */

//load main config so that we can connect to casebox db and read configuration for core
$cfg = Config::loadConfigFile(DOC_ROOT.'config.ini');

//define global prefix used
define(
    'CB\\PREFIX',
    (
        empty($cfg['prefix'])
            ? 'cb'
            : $cfg['prefix']
    ) . '_'
);

define(
    'CB\\IS_DEBUG_HOST',
    (
        empty($_SERVER['SERVER_NAME']) ||
        (!empty($cfg['debug_hosts']) && Util\isInValues($_SERVER['REMOTE_ADDR'], $cfg['debug_hosts']))
    )
);

define(
    'CB\\IS_DEVEL_SERVER',
    (
        !empty($cfg['_dev_mode']) &&
        (
            (strpos($_SERVER['SERVER_NAME'], '.d.') !== false) ||
            (!empty($cfg['_dev_hosts']) && Util\isInValues($_SERVER['REMOTE_ADDR'], $cfg['_dev_hosts']))
        )
    )
);

//analize python option
if (empty($cfg['PYTHON'])) {
    $cfg['PYTHON'] = 'python';
}

//set unoconv path
$cfg['UNOCONV'] = '"' . $cfg['PYTHON'] . '" "' . DOC_ROOT . 'libx' . DIRECTORY_SEPARATOR . 'unoconv"';

$cfg['HTML_PURIFIER'] = 'htmlpurifier/library/HTMLPurifier.auto.php';
$cfg['SOLR_CLIENT'] = 'Solr/Service.php';
$cfg['MINIFY_PATH'] = DOC_ROOT . 'libx/min/';
$cfg['TIKA_SERVER'] = DOC_ROOT . 'libx/tika-server.jar';

Cache::set('platformConfig', $cfg);

//conect to db using global params from config.ini
DB\connect($cfg);

/* config functions section */

/**
 * detect core from enviroment
 * @return varchar | false
 */
function detectCore()
{
    $rez = false;

    if (isset($_GET['core'])) {
        $rez = preg_replace('/[^\w]\-_/i', '', $_GET['core']);
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

        $rez = $arr[0];
    }

    return $rez;
}

/**
 * debug message to DBUG_LOG file
 * @param  variant $msg
 * @return void
 */
function debug($msg)
{
    $msg = '';
    $args = func_get_args();

    foreach ($args as $arg) {
        $msg .= is_scalar($arg)
            ? $arg
            : var_export($arg, 1);
        $msg .= "\n";
    }

    $debugFile = Config::get('debug_log');

    if (empty($debugFile)) {
        $debugFile = LOGS_DIR.'cb_debug_log';
    }

    error_log(date('Y-m-d H:i:s').': '.$msg."\n", 3, $debugFile);
}

/**
 * Fire server side event
 *
 * This function calls every defined listener for fired event
 */
function fireEvent($eventName, &$params)
{
    //check if triggers not disabled
    if (Config::getFlag('disableTriggers')) {
        return;
    }

    $triggerDepth = Config::get('runningTriggerDepth', 0);

    // dont allow triggers run deeper then 3rd level
    if ($triggerDepth > 3) {
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
            Config::setEnvVar('runningTriggerDepth', $triggerDepth + 1);
            try {
                $class->$method($params);

            } catch (\Exception $e) {
                debug(
                    'Event Exception for '.$className.'->'.$method."\n".
                    $e->getMessage()."\n".
                    $e->getTraceAsString()
                );
            }
            Config::setEnvVar('runningTriggerDepth', $triggerDepth);
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
 * The merged result is managed by Config class
 *
 * @param  varchar $optionName name of the option to get
 * @return variant | null
 */
function getOption($optionName, $defaultValue = null)
{
    if (!empty($_SESSION['user']['cfg'][$optionName])) {
        return $_SESSION['user']['cfg'][$optionName];
    }

    return Config::get($optionName, $defaultValue);
}
