<?php
/**
 * core instantiation
 *
 * Script params:
 *     -c, --core  - required, core name
 *     -s, --sql   - sql dump file, if no value specified then barebone sql dump will be used
 *
 * Example: php -f core_create.php -- -c text_core_name -s /path/to/mysql/dump.sql
 */
namespace CB\Install;

use CB\DB;
use CB\DataModel as DM;

$binDirectorty = dirname(__FILE__) . DIRECTORY_SEPARATOR;
$cbHome = dirname($binDirectorty) . DIRECTORY_SEPARATOR;

require_once $cbHome . 'httpsdocs/config_platform.php';

require_once \CB\LIB_DIR . 'install_functions.php';

//check script options
if (empty($options)) {
    $options = getopt('c:s::', array('core:', 'sql::'));
}

$coreName = empty($options['c'])
    ? @$options['core']
    : $options['c'];

if (empty($coreName)) {
    die('no core specified or invalid options set.');
}

$sqlFile = empty($options['s'])
    ? @$options['sql']
    : $options['s'];

if (empty($sqlFile)) {
    $ds = DIRECTORY_SEPARATOR;
    $sqlFile = $cbHome . "install${ds}mysql${ds}bare_bone_core.sql";
}

/*if (!defined('CB\INTERACTIVE_MODE')) {
    //define working mode
    define('CB\INTERACTIVE_MODE', empty($options['config']));
} */

if (!\CB\Cache::get('RUN_SETUP_INTERACTIVE_MODE')) {
    //define working mode
    \CB\Cache::set('RUN_SETUP_INTERACTIVE_MODE', !\CB\Cache::exist('RUN_SETUP_CFG'));
}

\CB\Install\defineBackupDir($cfg);

$dbName = (
    isset($cfg['prefix'])
        ? $cfg['prefix'].'_'
        : \CB\PREFIX
    ) . $coreName;

$dbUser = isset($cfg['su_db_user'])
    ? $cfg['su_db_user']
    : $cfg['db_user'];
$dbPass = isset($cfg['su_db_pass'])
    ? $cfg['su_db_pass']
    : $cfg['db_pass'];

$applyDump = true;

if (\CB\DB\dbQuery('use `' . $dbName . '`', array('hideErrors' => true))) {
    if (confirm('overwrite_existing_core_db')) {
        if (\CB\Cache::get('RUN_SETUP_CREATE_BACKUPS') !== false) {
            echo 'Backuping .. ';
            backupDB($dbName, $dbUser, $dbPass, $cfg['db_host']);
            showMessage();
        }
    } else {
        $applyDump = false;
    }
} else {
    if (!\CB\DB\dbQuery('CREATE DATABASE `' . $dbName . '` CHARACTER SET utf8 COLLATE utf8_general_ci')) {
        if (\CB\Cache::get('RUN_SETUP_INTERACTIVE_MODE')) {
            echo 'Cant create database "' . $dbName . '".';
        } else {
            trigger_error('Cant create database "' . $dbName . '".', E_USER_ERROR);
        }
        $applyDump = false;
    }
}

if ($applyDump) {
    restoreDB(
        $dbName,
        $dbUser,
        $dbPass,
        $cfg['db_host'],
        $sqlFile
    );

    showMessage();
}

$cbDb = $cfg['prefix'] . '__casebox';

echo 'Registering core .. ';
\CB\DB\dbQuery(
    'REPLACE INTO ' . $cbDb . ' .cores (name, cfg) VALUES ($1, $2)',
    array($coreName, '{}')
);
showMessage();

//ask to provide root email & password
$email = '';
$pass = '';

do {
    $email = readParam('core_root_email');
} while (\CB\Cache::get('RUN_SETUP_INTERACTIVE_MODE') && empty($email));

do {
    $pass = readParam('core_root_pass');
} while (\CB\Cache::get('RUN_SETUP_INTERACTIVE_MODE') && empty($pass));

DB\dbQuery("use `$dbName`");

if (!empty($email) || !empty($pass)) {
    DM\Users::updateByName(
        array(
            'name' => 'root'
            ,'password' => $pass
            ,'email' => $email
            ,'data' => '{"email": "'.$email.'"}'
        )
    );
}

//set core languages
$sql = 'REPLACE INTO `config` (id, param, `value`)
    VALUES ($1, $2, $3);';

$language = readParam('core_default_language', 'en');

DB\dbQuery(
    $sql,
    array(
        DM\Config::toId('default_language', 'param'),
        'default_language',
        $language
    )
);

$languages = readParam('core_languages', $language);

DB\dbQuery(
    $sql,
    array(
        DM\Config::toId('languages', 'param'),
        'languages',
        $languages
    )
);

createSolrCore($cfg, $coreName);

echo 'Creating language files .. ';
exec('php "' . $binDirectorty . 'languages_update_js_files.php"');
showMessage();

echo "Done.\n";
