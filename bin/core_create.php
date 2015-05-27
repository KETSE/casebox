<?php
/**
 * core instantiation
 *
 * Script params:
 *     -c, --core  - required, core name
 *     -s, --sql   - sql dump file
 *
 * Example: php -f core_create.php -- -c text_core_name -s /path/to/mysql/dump.sql
 */
namespace CB;

$binDirectorty = dirname(__FILE__) . DIRECTORY_SEPARATOR;
$cbHome = dirname($binDirectorty) . DIRECTORY_SEPARATOR;

require_once $cbHome . 'httpsdocs/config_platform.php';

require_once $binDirectorty . 'install_functions.php';

//check script options
if (empty($options)) {
    $options = getopt('c:s:', array('core:', 'sql:'));
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
    die('no sql dump file specified or invalid options set.');
}

if (!defined('CB\INTERACTIVE_MODE')) {
    //define working mode
    define('CB\INTERACTIVE_MODE', empty($options['config']));
}

defineBackupDir($cfg);

$dbName = PREFIX . $coreName;
$dbUser = $cfg['db_user'];
$dbPass = $cfg['db_pass'];

$applyDump = true;

if (DB\dbQuery('use `' . $dbName . '`')) {
    if (confirm('overwrite_existing_core_db')) {
        echo 'Backuping .. ';
        backupDB($dbName, $dbUser, $dbPass);
        echo "Ok\n";

    } else {
        $applyDump = false;
    }
} else {
    if (!DB\dbQuery('CREATE DATABASE `' . $dbName . '` CHARACTER SET utf8 COLLATE utf8_general_ci')) {
        if (INTERACTIVE_MODE) {
            echo 'Cant create database "' . $dbName . '".';
        } else {
            trigger_error('Cant create database "' . $dbName . '".', E_USER_ERROR);
        }
        $applyDump = false;
    }
}

if ($applyDump) {
    echo 'Applying dump .. ';
    exec('mysql --user=' . $dbUser . ' --password=' . $dbPass . ' ' . $dbName . ' < ' . $sqlFile);
    echo "Ok\n";
}

$cbDb = $cfg['prefix'] . '__casebox';

echo 'Registering core .. ';
DB\dbQuery(
    'INSERT INTO ' . $cbDb . ' .cores (name, cfg) VALUES ($1, $2)',
    array($coreName, '{}')
);
echo "Ok\n";

//ask to provide root email & password
$email = '';
$pass = '';
do {
    $email = readParam('core_root_email');
} while (INTERACTIVE_MODE && empty($l));

do {
    $pass = readParam('core_root_pass');
} while (INTERACTIVE_MODE && empty($l));

DB\dbQuery(
    'UPDATE `'.$dbName.'`.users_groups
    SET `password` = MD5(CONCAT(\'aero\', $2))
        ,email = $3
        ,`data` = $4
    WHERE name = $1',
    array(
        'root'
        ,$pass
        ,$email
        ,'{"email": "'.$email.'"}'
    )
) or die(DB\dbQueryError());

createSolrCore($cfg, $coreName);

echo "Done.\n";
