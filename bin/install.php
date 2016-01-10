<?php

namespace CB\Install;

/**
 * install CaseBox script designed to help first configuration of casebox
 *
 * this script can be run in interactive mode (default)
 * or specify an input ini file using -f option
 *
 * For tests this script can be included and $options variable
 * can be predefined before include.
 *
 * $options can contain (f or file) property to indicate configuration ini file used
 * or directly a 'config' array property that will have all needed params set
 *
 * Requirements:
 *     on Windows platform path to mysql/bin should be added to "Path" environment variable
 *
 * php install.php --arg1=value1 --arg2=value2 ... OR --config=/path/to/config.ini
 *
 */

use CB;

/*define some basic directories*/
$binDirectorty = dirname(__FILE__) . DIRECTORY_SEPARATOR;
$cbHome = dirname($binDirectorty) . DIRECTORY_SEPARATOR;

require_once $cbHome . 'httpsdocs/lib/Util.php';

/* check if we are running under root / Administrator user */

switch (CB\Util\getOS()) {

    case 'WIN':

        $returned_user_state = shell_exec(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'get_user_state.bat');
        $user_state = preg_replace('/\n|\r/si', '', $returned_user_state);

        if ($user_state != 'admin') {
            die("This script should be run under \"Administrator\"\n");
        }
        break;

    case "LINUX":
        $currentUser = empty($_SERVER['USER'])
            ? @$_SERVER['USERNAME']
            : $_SERVER['USER'];

        if (!in_array($currentUser, array('root'))) {
            echo "\033[31mThis script should be run under \"root\" \033[0m\n";
        }

        break;

    default: echo "Unknown OS System" ;

        break;
}

if (!isset($cfg)) {
    $cfg = array();
}

// we include config_platform that will load config.ini if exist and will define $cfg variable
// If config.ini doesnt exist it wil raise an exception: Can't load config file

try {
    require_once $cbHome . 'httpsdocs/config_platform.php';
} catch (\Exception $e) {
    //config.ini could not exist

    //we don't need to do anything here because this script will create confing.ini in result
    //we just use values form config.ini as defaults, if it exists
}

// detect working mode (interactive or not)

require_once \CB\LIB_DIR.'install_functions.php';

$cliCfg = \CB\Install\cliLoadConfig(
    isset($options)
    ? $options
    : null
);

/* for interactive mode the priority is active config.ini
otherwise - the config specified in params */
if (\CB\Cache::get('RUN_SETUP_INTERACTIVE_MODE')) {
    $cfg += $cliCfg;

} else {
    $cfg = $cliCfg + $cfg;
}

\CB\Install\displaySystemNotices();

if (\CB\Util\getOS() != "WIN") {
    //ask for apache user and set ownership for some folders
    $cfg['apache_user'] = readParam('apache_user', $cfg['apache_user']);
    if (\CB\Cache::get('RUN_SETUP_INTERACTIVE_MODE')) {
        setOwnershipForApacheUser($cfg);
    }
}

//init prefix
$cfg['prefix'] = readParam('prefix', $cfg['prefix']);

//init db config
$tryInitDBConfig = 0;
do {
    initDBConfig($cfg);
    $tryInitDBConfig++;
    if ($tryInitDBConfig > 3) {
        trigger_error("ERROR: Cannot configure database connections !!!", E_USER_ERROR);
    }
} while (!verifyDBConfig($cfg));

//specify server_name
$l = readParam('server_name', $cfg['server_name']);

//add trailing slash
if (!empty($l)) {
    $l = trim($l);
    if (substr($l, -1) != '/') {
        $l .= '/';
    }

    $cfg['server_name'] = $l;
}

if (confirm('solr_create_cores')) {
    //init solr connection
    initSolrConfig($cfg);
}

$cfg['admin_email'] = readParam('admin_email', $cfg['admin_email']);
$cfg['sender_email'] = readParam('sender_email', $cfg['sender_email']);

//define comments email params
if (confirm('define_comments_email')) {
    $cfg['comments_email'] = readParam('comments_email', $cfg['comments_email']);
    $cfg['comments_host'] = readParam('comments_host', $cfg['comments_host']);
    $cfg['comments_port'] = readParam('comments_port', $cfg['comments_port']);
    $cfg['comments_ssl'] = readParam('comments_ssl', $cfg['comments_ssl']);
    $cfg['comments_user'] = readParam('comments_user', $cfg['comments_user']);
    $cfg['comments_pass'] = readParam('comments_pass');
} else {
    unset($cfg['comments_email']);
    unset($cfg['comments_host']);
    unset($cfg['comments_port']);
    unset($cfg['comments_ssl']);
    unset($cfg['comments_user']);
    unset($cfg['comments_pass']);
}

$cfg['PYTHON'] = readParam('PYTHON', $cfg['PYTHON']);

$cfg['backup_dir'] = readParam('backup_dir', $cfg['backup_dir']);

//define BACKUP_DIR constant and create corresponding directory

defineBackupDir($cfg);

echo "\nYou have configured main options for casebox.\n" .
    "Saving your settings to " . \CB\DOC_ROOT . 'config.ini';

if (!(\CB\Cache::get('RUN_SETUP_CREATE_BACKUPS') == false)) {
    backupFile(\CB\DOC_ROOT . 'config.ini');
}

do {
    $r = putIniFile(
        \CB\DOC_ROOT . 'config.ini',
        array_intersect_key($cfg, getDefaultConfigValues())
    );

    if ($r === false) {
        if (\CB\Cache::get('RUN_SETUP_INTERACTIVE_MODE')) {
            $r = !confirm('error saving to config.ini file. retry [Y/n]: ');
        } else {
            trigger_error('Error saving to config.ini file', E_USER_ERROR);
        }
    } else {
        showMessage();
    }
} while ($r === false);

//---------- checking required folders existence
$requiredDirs = array(
    \CB\LOGS_DIR,
    \CB\DATA_DIR,
    \CB\TEMP_DIR,
    \CB\MINIFY_CACHE_DIR
);

foreach ($requiredDirs as $dir) {
    if (!file_exists($dir)) {
        if (!mkdir($dir, 0755, true)) {
            echo "Cant create directory $dir \n";
        }
    }
}

if (\CB\Cache::get('RUN_SETUP_INTERACTIVE_MODE')) {
    //---------- create solr symlinks for casebox config sets
    if (createSolrConfigsetsSymlinks($cfg)) {
        echo "Solr configsets symlinks created sucessfully.\n\r";
    } else {
        echo "Error creating symlinks to solr configsets.\n\r";
    }
}

//try to create log core
if (\CB\Cache::get('RUN_SETUP_INTERACTIVE_MODE')) {
    createSolrCore($cfg, 'log', 'log_');
}

//create default database (<prefix>__casebox)
echo "create Main Database:".PHP_EOL;
createMainDatabase($cfg);

echo "\nCasebox was successfully configured on your system\n" .
    "you should create at least one Core to use it.\n";

//ask if new core instance needed
if (confirm('create_basic_core')) {
    $l = readParam('core_name');
    if (!empty($l)) {
        $ds = DIRECTORY_SEPARATOR;
        $options = array(
            'core' => $l
            ,'sql' => \CB\APP_DIR . "install${ds}mysql${ds}bare_bone_core.sql"
        );

        include $binDirectorty . 'core_create.php';
    }

}
echo "Done\n";
