#!/usr/bin/php
<?php

namespace CB;

/**
 * install CaseBox script designed to help first configuration and core instatiation
 *
 * Requirements:
 *     on Windows platform path to mysql/bin should be added to "Path" environment variable
 */

echo "Notice: on Windows platform path to mysql/bin should be added to \"Path\" environment variable.\n\n";

try {
    require_once '../httpsdocs/config_platform.php';
} catch (\Exception $e) {
    //config.ini could not exist

}
// initialize default values in cofig if not detected
$defaultValues = array(
   'prefix' => 'cb'
    ,'db_host' => '127.0.0.1'
    ,'db_port' => '3306'
    ,'db_user' => 'local'
    ,'db_pass' => ''

    ,'solr_host' => '127.0.0.1'
    ,'solr_port' => '8983'

    ,'session.lifetime' => '180'

    //;ADMIN_EMAIL: email adress used to notify admin on any casebox problems
    ,'admin_email' => 'your.email@server.com'
    //;SENDER_EMAIL: email adress placed in header for sent mails
    ,'sender_email' => 'emails.sender@server.com'

    ,'webdav_url' => 'https://sss.davdev.casebox.org/edit/{core_name}/{node_id}/{name}'
    ,'PYTHON' => 'python'
);

$cfg = $cfg + $defaultValues;

//init prefix
$l = readALine('Specify prefix used for database names, solr core and log files (default "' . $cfg['prefix'] . '"):' . "\n");
if (!empty($l)) {
    $cfg['prefix'] = $l;
}

//init db config
do {
    initDBConfig();
} while (!verifyDBConfig());

//init solr connection
initSolrConfig();
// do {
//     $cfg['core'] = 'cb_dev';
// } while (!verifySolrConfig());

$l = readALine('Specify administrator email address (default "' . $cfg['admin_email'] . '"):' . "\n");
if (!empty($l)) {
    $cfg['admin_email'] = $l;
}

$l = readALine('Specify sender email address, placed in header for sent mails (default "' . $cfg['sender_email'] . '"):' . "\n");
if (!empty($l)) {
    $cfg['sender_email'] = $l;
}

$l = readALine('Specify python path (default "' . $cfg['PYTHON'] . '"):' . "\n");
if (!empty($l)) {
    $cfg['PYTHON'] = $l;
}

echo "\nYou have configured main options for casebox.\n".
    "Saving your settings to casebox.ini ... ";

backupFile(DOC_ROOT . 'config.ini');

do {

    $r = putIniFile(
        DOC_ROOT . 'config.ini',
        array_intersect_key($cfg, $defaultValues)
    );

    if ($r === false) {
        $r = !confirm(' error saving to config.ini file. retry (y/n)?:');
    } else {
        echo "Ok\n\n";
    }
} while ($r === false);

//create default database (<prefix>__casebox)
$cbDb = $cfg['prefix'] . '__casebox';

$r = DB\dbQuery('use `' . $cbDb . '`');
if ($r) {
    if (confirm("'$cbDb' database exists. Would you like to backup it and overwrite with dump from current installation? (y/n): ")) {
        echo 'Backuping .. ';
        backupDB($cbDb);
        echo "Ok\n";

        echo 'Applying dump .. ';
        exec('mysql --user=' . $cfg['db_user'] . ' --password=' . $cfg['db_pass'] . ' ' . $cbDb . ' < ' . APP_DIR . 'install/mysql/_casebox.sql');
        echo "Ok\n";
    }
} else {
    if (confirm("$cbDb database does not exist. Would you like to create it from current installation dump file? (y/n): ")) {
        if (DB\dbQuery('CREATE DATABASE `' . $cbDb . '` CHARACTER SET utf8 COLLATE utf8_general_ci')) {
            exec('mysql --user=' . $cfg['db_user'] . ' --password=' . $cfg['db_pass'] . ' ' . $cbDb . ' < ' . APP_DIR . 'install/mysql/_casebox.sql');
        } else {
            echo 'Cant create database "' . $cbDb . '".';
        }
    }
}

//core instantiation
if (confirm('Would you like to instantiate a core (y/n)?:')) {
    $coreName = readALine('Specify core name (without prefix): ');

    if (empty($coreName)) {
        echo 'No name specified.';

    } else {
        $dbName = $cfg['prefix'] . '_' . $coreName;
        $applyDump = true;

        if (DB\dbQuery('use `' . $dbName . '`')) {
            if (confirm('Database "' . $dbName.'"  already exists. Would you like to overwrite it?')) {
                echo 'Backuping .. ';
                backupDB($dbName);
                echo "Ok\n";

            } else {
                $applyDump = false;
            }
        } else {
            if (!DB\dbQuery('CREATE DATABASE `' . $dbName . '` CHARACTER SET utf8 COLLATE utf8_general_ci')) {
                echo 'Cant create database "' . $dbName . '".';
                $applyDump = false;
            }
        }

        if ($applyDump) {
            echo 'Applying dump .. ';
            exec('mysql --user=' . $cfg['db_user'] . ' --password=' . $cfg['db_pass'] . ' ' . $dbName . ' < ' . APP_DIR . 'install/examples/demosrc/mysql/demosrc.sql');
            echo "Ok\n";
        }

        echo 'Registering core .. ';
        DB\dbQuery(
            'INSERT INTO ' . $cbDb . ' .cores (name, cfg) VALUES ($1, $2)',
            array($coreName, '{}')
        );
        echo "Ok\n";

        echo "Copy core files ... ";
        rcopy(APP_DIR . 'install/examples/demosrc/core', DOC_ROOT . 'cores/' . $coreName);
        echo "Done\n";

        //verify if solr core exist
        $cfg['solr_core'] = $dbName;
        $askReindex = true;

        $solr = verifySolrConfig();
        if ($solr === false) {
            if (confirm('Solr core "' . $dbName . '" doesnt exist. Would you like to create it? (y/n): ')) {
                echo 'Creating solr core ... ';

                $h = fopen(
                    'http://' . $cfg['solr_host']. ':' . $cfg['solr_port'] . '/solr/admin/cores?action=CREATE&' .
                    'name=' . $dbName . '&persist=true&instanceDir=' . DATA_DIR . '/solr&dataDir=data/' . $dbName,
                    'r'
                );

                fclose($h);

                echo "Ok\n";
            } else {
                $askReindex = false;
            }
        }

        if ($askReindex) {
            if (confirm('Do you want to start full core reindex? (y/n): ')) {
                echo 'Reindex solr core ... ';
                exec('php utils/solr_reindex_core.php ' . $coreName . ' all');
                echo "Ok\n";
            }
        }
    }
}

//--------------------------------------------------------------------
/**
 * verify if can connect to solr
 * @return boolean
 */
function verifySolrConfig()
{
    global $cfg;
    echo "Verifying solr params ... ";
    $rez = false;

    try {
        $rez = new Solr\Client(
            array(
                'host' => $cfg['solr_host']
                ,'port' => $cfg['solr_port']
                ,'core' => $cfg['solr_core']
                ,'SOLR_CLIENT' => $cfg['SOLR_CLIENT']
            )
        );

    } catch (\Exception $e) {
        $rez = false;
    }

    return $rez;
}

/**
 * init solr connection params
 * @return void
 */
function initSolrConfig()
{
    global $cfg;

    $l = readALine('Specify solr configuration:' . "\n".'solr host (' . $cfg['solr_host'] . '): ');
    if (!empty($l)) {
        $cfg['solr_host'] = $l;
    }

    $l = readALine('solr port (' . $cfg['solr_port'] . '): ');
    if (!empty($l)) {
        $cfg['solr_port'] = $l;
    }
}

/**
 * verify specified database params
 * @return boolean
 */
function verifyDBConfig()
{
    global $cfg;
    echo "Verifying db params ... ";
    $rez = true;
    $error = false;

    try {
        $db = @DB\connectWithParams($cfg);

        $error = mysqli_connect_errno();

    } catch (\Exception $e) {
        $error = true;
    }

    if ($error) {
        $l = confirm('Failed to connect to DB with error: ' . mysqli_connect_error() . "\n" . ', would you like to update inserted params (y/n):' . "\n");
        $rez = ($l == 'n');

    } else {
        echo "Ok\n";
    }

    return $rez;
}

/**
 * init database connection params
 * @return void
 */
function initDBConfig()
{
    global $cfg;
    //init database configuration
    $l = readALine('Specify database configuration:' . "\n".'db host (' . $cfg['db_host'] . '): ');
    if (!empty($l)) {
        $cfg['db_host'] = $l;
    }

    $l = readALine('db port (' . $cfg['db_port'] . '): ');
    if (!empty($l)) {
        $cfg['db_port'] = $l;
    }

    $l = readALine('db user (' . $cfg['db_user'] . '): ');
    if (!empty($l)) {
        $cfg['db_user'] = $l;
    }

    $l = readALine('db password: ');
    if (!empty($l)) {
        $cfg['db_pass'] = $l;
    }
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
 * [confirm description]
 * @param  varchar $message
 * @return boolean
 */
function confirm($message)
{
    $l = '';
    do {
        $l = readALine($message);
    } while (!in_array($l, array('y', 'n')));

    return ($l == 'y');
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

    $backupDir = getBackupDir();

    return rename($fileName, $backupDir . date('Ymd_His_') . basename($fileName));
}

/**
 * backup given database to sys/backup folder
 * @param  varchar $dbName
 * @return boolean
 */
function backupDB($dbName)
{
    global $cfg;

    $backupDir = getBackupDir();

    $fileName = $backupDir . date('Ymd_His_') . $dbName . '.sql';

    exec('mysqldump --routines --user=' . $cfg['db_user'] . ' --password=' . $cfg['db_pass'] . ' ' . $dbName . ' > ' . $fileName);

    return true;
}

/**
 * return backup folder and automaticly creates it if doesnt exist
 * @return varchar
 */
function getBackupDir()
{
    $rez = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR;

    if (!file_exists($rez)) {
        @mkdir($rez, 0744, true);
    }

    return $rez;
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
 * copies files and non-empty directories
 * @param  varchar $src
 * @param  varchar $dst
 * @return void
 */
function rcopy($src, $dst)
{
    if (is_dir($src)) {
        mkdir($dst);
        $files = scandir($src);
        foreach ($files as $file) {
            if ($file != "." && $file != "..") {
                rcopy("$src/$file", "$dst/$file");
            }
        }
    } elseif (file_exists($src)) {
        copy($src, $dst);
    }
}
