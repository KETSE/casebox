<?php

/**
 * backup functions used by install scripts
 */

namespace CB\INSTALL;

use CB;

/**
 * return default values for casebox configuration
 * @return [type] [description]
 */
function getDefaultConfigValues()
{
    return array(
       'prefix' => 'cb'

        ,'apache_user' => 'apache'

        ,'db_host' => 'localhost'
        ,'db_port' => '3306'

        ,'su_db_user' => 'root'
        // ,'su_db_pass' => '' // shouldn't be saved to config.ini

        ,'db_user' => 'local'
        ,'db_pass' => ''

        ,'server_name' => 'https://yourserver.com/'

        ,'solr_home' => '/var/solr/data/'
        ,'solr_host' => '127.0.0.1'
        ,'solr_port' => '8983'

        ,'session.lifetime' => '180'

        //;ADMIN_EMAIL: email adress used to notify admin on any casebox problems
        ,'admin_email' => 'your.email@server.com'
        //;SENDER_EMAIL: email adress placed in header for sent mails
        ,'sender_email' => 'emails.sender@server.com'

        ,'comments_email' => 'comments@subdomain.domain.com'
        ,'comments_host' => '127.0.0.1'
        ,'comments_port' => 993
        ,'comments_ssl' => true
        ,'comments_user' => ''
        ,'comments_pass' => ''

        ,'PYTHON' => 'python'
        ,'backup_dir' => \CB\APP_DIR . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR
    );
}

/**
 * get question / phrase to be displayed for a given paramName
 */
function getParamPhrase($paramName)
{
    $phrases = array(
        'apache_user' => 'Specify apache user {default}:' . "\n"
        ,'prefix' => 'Specify prefix used for database names, solr core and log files {default}:' . "\n"
        ,'server_name' => 'Provide server name with protocol {default}:' . "\n"

        ,'db_host' => 'db host {default}: '
        ,'db_port' => 'db port {default}: '
        ,'su_db_user' => 'privileged db user {default}: '
        ,'su_db_pass' => 'privileged db user\'s password: '
        ,'db_user' => 'db user {default}: '
        ,'db_pass' => 'db password: '

        ,'admin_email' => 'Specify administrator email address {default}:' . "\n"
        ,'sender_email' => 'Specify sender email address, placed in header for sent mails {default}:' . "\n"

        ,'define_comments_email' => 'Would you like to define comments email parametters [Y/n]: '
        ,'comments_email' => 'Specify comments email address, used to receive replies for Casebox comment notifications {default}:' . "\n"
        ,'comments_host' => 'Specify comments email server host {default}:' . "\n"
        ,'comments_port' => 'Specify comments email server port {default}:' . "\n"
        ,'comments_ssl' => 'Specify if ssl connection is used for comments email server [Y/n]: '
        ,'comments_user' => 'Specify username for comments email server connection (can be left blank if email could be used as username):' . "\n"
        ,'comments_pass' => 'Specify password for comments email server connection:' . "\n"

        ,'PYTHON' => 'Specify python path {default}:' . "\n"

        ,'solr_home' => 'solr home directory {default}: '
        ,'solr_host' => 'solr host {default}: '
        ,'solr_port' => 'solr port {default}: '

        ,'backup_dir' => 'Specify backup directory {default}:' . "\n"

        // ,'? or overwrite, cause it asks only when doesnt exist
        ,'log_solr_overwrite' => 'Solr core {prefix}_log exists or can\'t access solr. Would you like to try to create it [Y/n]: '

        ,'overwrite__casebox_db' => "'{prefix}_casebox' database exists. Would you like to backup it and overwrite with dump from current installation [Y/n]: "

        ,'create__casebox_from_dump' => "{prefix}_casebox database does not exist. Would you like to create it from current installation dump file [Y/n]: "

        ,'create_basic_core' => "Do you want to create a basic default core [Y,n]: "
        ,'core_name' => "Core name:\n"

        //core_create specific params
        ,'core_overwrite_existing_db' => 'Database for given core name already exists. Would you like to overwrite it?'
        ,'core_root_email' => 'Specify email address for root user:' . "\n"
        ,'core_root_pass' => 'Specify root user password:' . "\n"
        ,'core_solr_overwrite' => 'Solr core already exists, overwrite [Y/n]: '
        ,'core_solr_reindex' => 'Reindex core [Y/n]: '
        ,'overwrite_existing_core_db' => "Core database exists. Would you like to backup it and overwrite with dump from current installation [Y/n]: "

    );

    return empty($phrases[$paramName])
        ? $paramName
        : $phrases[$paramName];
}

/**
 * display notices for specific operation system
 * @return [type] [description]
 */
function displaySystemNotices()
{
    if (\CB\IS_WINDOWS) {
        echo "Notice: on Windows platform path to mysql/bin should be added to \"Path\" environment variable.\n\n";
    } else {

    }
}

/**
 * set ownership to apache user for following CB folders:
 *     logs, data, httpsdocs/cores
 * @param [type] &$cfg [description]
 */
function setOwnershipForApacheUser(&$cfg)
{
    if (\CB\IS_WINDOWS) {
        return ;
    }

    shell_exec('chown -R ' . $cfg['apache_user'].' "' . \CB\LOGS_DIR . '"');
    shell_exec('chown -R ' . $cfg['apache_user'].' "' . \CB\DATA_DIR . '"');
}

/**
 * init solr connection params
 * @return void
 */
function initSolrConfig(&$cfg)
{
    echo "\nSpecify solr configuration:\n";

    $retry = true;
    do {
        $cfg['solr_home'] = readParam('solr_home', $cfg['solr_home']);
        //add trailing slash
        if (!in_array(substr($cfg['solr_home'], -1), array('/', '\\'))) {
            $cfg['solr_home'] .= DIRECTORY_SEPARATOR;
        }

        $retry = false;

        if (!file_exists($cfg['solr_home'])) {
            if (\CB\Cache::get('RUN_SETUP_INTERACTIVE_MODE')) {
                $retry = confirm('Can\'t access specified path, would you like to check and enter it again [Y/n]:' . "\n");
            } else {
                trigger_error('Error accessing solr home directory "' . $cfg['solr_home'] .'".', E_USER_ERROR);
            }
        }

    } while ($retry);

    $cfg['solr_host'] = readParam('solr_host', $cfg['solr_host']);

    $cfg['solr_port'] = readParam('solr_port', $cfg['solr_port']);
}

/**
 * create symlynks in solr directory for casebox config sets
 * @param  array &$cfg
 * @return boolean
 */
function createSolrConfigsetsSymlinks(&$cfg)
{
    //creating solr symlinks
    $solrCSPath = $cfg['solr_home'] . 'configsets' . DIRECTORY_SEPARATOR;
    $CBCSPath = \CB\SYS_DIR . 'solr_configsets' . DIRECTORY_SEPARATOR;

    if (!file_exists($solrCSPath)) {
        mkdir($solrCSPath, 744, true);
    }

    $r = true;
    if (!file_exists($solrCSPath . 'cb_default')) {
        $r = symlink($CBCSPath . 'default_config' . DIRECTORY_SEPARATOR, $solrCSPath . 'cb_default');
    }
    if (!file_exists($solrCSPath . 'cb_log')) {
        $r = $r && symlink($CBCSPath . 'log_config' . DIRECTORY_SEPARATOR, $solrCSPath . 'cb_log');
    }

    return $r;
}

/**
 * method to create a solr core with additional checks
 * @param  array &$cfg
 * @return boolean
 */
function createSolrCore(&$cfg, $coreName, $paramPrefix = 'core_')
{

    //verify if solr core exist
    $solrHost     = $cfg['solr_host'];
    $solrPort     = $cfg['solr_port'];
    $createCore   = true;
    $askReindex   = true;
    $fullCoreName = $cfg['prefix'].'_'.$coreName;

    $solr = \CB\Solr\Service::verifyConfigConnection(
        array(
            'host' => $solrHost
            ,'port' => $solrPort
            ,'core' => $fullCoreName
            ,'SOLR_CLIENT' => $cfg['SOLR_CLIENT']
        )
    );

    if ($solr !== false) {
        if (confirm($paramPrefix.'solr_overwrite', 'n')) {
            echo 'Unloading core '.$coreName.'... ';
            unset($solr);
            if (solrUnloadCore($solrHost, $solrPort, $fullCoreName)) {
                echo "\033[32mOk\033[0m\n";
            } else {
                displayError("Error unloading core.\n");
                $createCore = false;
            }
        } else {
            $createCore = false;
        }
    }

    if ($createCore) {
        echo 'Creating solr core ... ';

        if (solrCreateCore($solrHost, $solrPort, $fullCoreName)) {
            echo "\033[32mOk\033[0m\n";
        } else {
            displayError("Error creating core.\n");
            $askReindex = false;
        }
    }

    if ($askReindex && ($paramPrefix !== 'log_')) {
        if (confirm($paramPrefix.'solr_reindex', 'n')) {
            echo 'Reindexing core ... ';

            $cmd_reindex_core = 'php '.\CB\BIN_DIR.'solr_reindex_core.php -c '.$coreName.' -a -l';
            $reindex_result = shell_exec($cmd_reindex_core);
            // here need to verify result of execution solr_reindex_core.php
            echo " \033[32mOk\033[0m\n";
        }
    }
}

/**
 * unload a solr core
 * @return boolean
 */
function solrUnloadCore($host, $port, $coreName)
{
    $rez = true;

    $url = 'http://' . $host. ':' . $port . '/solr/admin/cores?action=UNLOAD&' .
        'core=' . $coreName . '&deleteInstanceDir=true';

    if ($h = fopen($url, 'r')) {
        fclose($h);
    } else {
        $rez = false;
    }

    return $rez;
}

/**
 * create a solr core
 * @return boolean
 */
function solrCreateCore($host, $port, $coreName)
{
    $rez = true;

    if ($h = fopen(
        'http://' . $host. ':' . $port . '/solr/admin/cores?action=CREATE&' .
        'name=' . $coreName . '&configSet=cb_default',
        'r'
    )) {
        fclose($h);

    } else {
        $rez = false;
    }

    return $rez;
}

/**
 * verify specified database params
 * @return boolean
 */
function verifyDBConfig(&$cfg)
{
    echo "Verifying db params ... ";

    $rez = true;
    $error = false;

    try {
        //check firstly for priviliget user
        $dbh = connectDBWithSuUser($cfg);

        $error = mysqli_connect_errno();

        //check the standart user also
        if (empty($error)) {
            $dbh = @\CB\DB\connectWithParams($cfg);

            $error = mysqli_connect_errno();
        }

    } catch (\Exception $e) {
        $error = true;
    }

    if (\CB\Cache::get('RUN_SETUP_INTERACTIVE_MODE')) {
        if ($error) {
            $rez = !confirm('Failed to connect to DB with error: ' . mysqli_connect_error() . "\n" . ', would you like to update inserted params [Y/n]: ');
        } else {
            echo "\033[32mOk\033[0m\n";
        }
    } elseif ($error) {
        trigger_error('Error connecting to database with user "' . $cfg['db_user'] .'".', E_USER_ERROR);
    }

    return $rez;
}

/**
 * short function to connect to DB with privileged user
 * @param  array $cfg
 * @return db handler | null
 */
function connectDBWithSuUser($cfg)
{

    @$newParams = array(
        'db_host' => $cfg['db_host'],
        'db_user' => $cfg['su_db_user'],
        'db_pass' => $cfg['su_db_pass'],
        'db_name' => $cfg['db_name'],
        'db_port' => $cfg['db_port'],
        'initsql' => $cfg['initsql']
    );

    return @\CB\DB\connectWithParams($newParams);
}

/**
 * init database connection params
 * @return void
 */
function initDBConfig(&$cfg)
{
    echo 'Specify database configuration:' . "\n";

    //init database configuration
    $cfg['db_host'] = readParam('db_host', $cfg['db_host']);
    $cfg['db_port'] = readParam('db_port', $cfg['db_port']);
    $cfg['su_db_user'] = readParam('su_db_user', $cfg['su_db_user']);
    $cfg['su_db_pass'] = readParam('su_db_pass');
    $cfg['db_user'] = readParam('db_user', $cfg['db_user']);
    $cfg['db_pass'] = readParam('db_pass');
}

/**
 * create default database (<prefix>__casebox)
 * @param  array $cfg
 * @return boolean
 */
function createMainDatabase($cfg)
{
    $rez = true;

    connectDBWithSuUser($cfg);

    $cbDb = $cfg['prefix'] . '__casebox';

    $r = \CB\DB\dbQuery('use `' . $cbDb . '`');
    if ($r) {
        if (confirm('overwrite__casebox_db')) {

            echo 'Backuping .. ';

            if (!(\CB\Cache::get('RUN_SETUP_CREATE_BACKUPS') == false)) {
                backupDB($cbDb, $cfg['db_user'], $cfg['db_pass']);
            }

            echo "\033[32mOk\033[0m\n";

            echo 'Applying dump .. ';
            exec('mysql --user=' . $cfg['db_user'] . ' --password=' . $cfg['db_pass'] . ' ' . $cbDb . ' < ' . \CB\APP_DIR . 'install/mysql/_casebox.sql');
            echo "\033[32mOk\033[0m\n";
        }
    } else {
        if (confirm('create__casebox_from_dump')) {
            if (\CB\DB\dbQuery('CREATE DATABASE `' . $cbDb . '` CHARACTER SET utf8 COLLATE utf8_general_ci')) {
                exec('mysql --user=' . $cfg['db_user'] . ' --password=' . $cfg['db_pass'] . ' ' . $cbDb . ' < ' . \CB\APP_DIR . 'install/mysql/_casebox.sql');
            } else {
                $rez = false;
                echo 'Cant create database "' . $cbDb . '".';
            }
        }
    }

    return $rez;
}

/**
 * read a line from stdin
 * @return varchar
 */
function readALine($message)
{
    $rez = '';
    if (PHP_OS == 'WINNT') {
        echo $message;
        $rez = stream_get_line(STDIN, 1024, PHP_EOL);
    } else {
        $rez = readline($message);
    }

    return trim($rez);
}

/**
 * get a paramValue
 * @param  varchar $paramName
 * @param  varchar $defaultValue
 * @return varchar
 */
function readParam($paramName, $defaultValue = null)
{
    $rez = $defaultValue;

    if (\CB\Cache::get('RUN_SETUP_INTERACTIVE_MODE')) {
        $question = str_replace(
            '{default}',
            '(default "' . $defaultValue. '")',
            getParamPhrase($paramName)
        );

        $question = str_replace('{prefix}', constant('CB\\PREFIX'), $question);

        $l = readAline($question);

        /* define prefix not defined yet */
        if ($paramName == 'prefix' && !defined('CB\\PREFIX')) {
            define('CB\\PREFIX', $l);
        }

        if (!empty($l)) {
            $rez = $l;
        }

    } else {
        $cfg = \CB\Cache::get('RUN_SETUP_CFG');
        if (!empty($cfg[$paramName])) {
            $rez = $cfg[$paramName];
        }
    }

    return trim($rez);
}

/**
 * confirm description
 * @param  varchar $message
 * @return boolean
 */
function confirm($paramName)
{
    $l = '';
    do {
        $l = readParam($paramName, 'y');
        $l = strtolower($l);
    } while (!in_array($l, array('', 'y', 'n')));

    return (($l == 'y') || ($l == ''));
}

/**
 * save ini file
 * @param  varchar  $file
 * @param  array  $array
 * @param  integer $i
 * @return variant
 */
function putIniFile ($file, $array, $i = 0)
{
    $str = "";
    foreach ($array as $k => $v) {
        if (is_array($v)) {
            $str .= str_repeat(" ", $i*2) . "[$k]" . PHP_EOL;
            $str .= putIniFile("", $v, $i+1);
        } else {
            $str .= str_repeat(" ", $i*2) . "$k = $v" . PHP_EOL;
        }
    }

    if ($file) {
        return file_put_contents($file, $str);
    } else {
        return $str;
    }
}

/**
 * define backup_dir constant and create folder if doesnt exist
 * @param  array &$cfg
 * @return varchar
 */
function defineBackupDir(&$cfg)
{
    if (\CB\Cache::exist('RUN_INSTALL_BACKUP_DIR')) {
        return \CB\Cache::get('RUN_INSTALL_BACKUP_DIR');
    }

    $dir = empty($cfg['backup_dir'])
        ? \CB\APP_DIR . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR
        : $cfg['backup_dir'];

    \CB\Cache::set('RUN_INSTALL_BACKUP_DIR', $dir);

    if (!file_exists($dir)) {
        mkdir($dir, 0766, true);
    }

    return $dir;
}

/**
 * backup given file to sys/backup folder
 * @param  varchar $fileName
 * @return boolean
 */
function backupFile($fileName)
{

    if (!file_exists($fileName)) {
        return false;
    }

    return rename($fileName, \CB\Cache::get('RUN_INSTALL_BACKUP_DIR'). date('Ymd_His_') . basename($fileName));
}

/**
 * backup given database to sys/backup folder
 * @param  varchar $dbName
 * @return boolean
 */
function backupDB($dbName, $dbUser, $dbPass)
{
    $fileName = \CB\Cache::get('RUN_INSTALL_BACKUP_DIR') . date('Ymd_His_') . $dbName . '.sql';

    exec('mysqldump --routines --user=' . $dbUser . ' --password=' . $dbPass . ' ' . $dbName . ' > ' . $fileName);

    return true;
}

/**
 * function to display errors in interactive mode or to raise them
 * @param  varchar $error
 * @return void
 */
function displayError($error)
{
    if (\CB\Cache::exist('RUN_SETUP_INTERACTIVE_MODE')) {
        if (\CB\Cache::get('RUN_SETUP_INTERACTIVE_MODE')) {
            echo $error;

            return;
        }
    }

    trigger_error($error, E_USER_ERROR);
}
