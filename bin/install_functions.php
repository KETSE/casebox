<?php

/**
 * backup functions used by install scripts
 */

namespace CB;

/**
 * init solr connection params
 * @return void
 */
function initSolrConfig(&$cfg)
{
    echo "\nSpecify solr configuration:\n";

    $retry = true;
    do {
        $l = readALine('solr home directory (' . $cfg['solr_home'] . '): ');
        if (!empty($l)) {
            $cfg['solr_home'] = $l;
        }
        //add trailing slash
        if (!in_array(substr($cfg['solr_home'], -1), array('/', '\\'))) {
            $cfg['solr_home'] .= DIRECTORY_SEPARATOR;
        }

        $retry = false;
        if (!file_exists($cfg['solr_home'])) {
            $retry = confirm('Can\'t access specified path, would you like to check and enter it again (y/n):' . "\n");
        }

    } while ($retry);

    $l = readALine('solr host (' . $cfg['solr_host'] . '): ');
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
function verifyDBConfig(&$cfg)
{
    echo "Verifying db params ... ";

    global $dbh;
    $rez = true;
    $error = false;

    try {
        $dbh = DB\connectWithParams($cfg);

        $error = mysqli_connect_errno();

    } catch (\Exception $e) {
        $error = true;
    }

    if ($error) {
        $rez = !confirm('Failed to connect to DB with error: ' . mysqli_connect_error() . "\n" . ', would you like to update inserted params (y/n):' . "\n");
    } else {
        echo "Ok\n";
    }

    return $rez;
}

/**
 * init database connection params
 * @return void
 */
function initDBConfig(&$cfg)
{
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
    $dir = empty($cfg['backup_dir'])
        ? dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR
        : $cfg['backup_dir'];

    define('CB\\BACKUP_DIR', $dir);

    if (!file_exists(BACKUP_DIR)) {
        mkdir(BACKUP_DIR, 744, true);
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

    return rename($fileName, BACKUP_DIR . date('Ymd_His_') . basename($fileName));
}

/**
 * backup given database to sys/backup folder
 * @param  varchar $dbName
 * @return boolean
 */
function backupDB($dbName, $dbUser, $dbPass)
{
    $fileName = BACKUP_DIR . date('Ymd_His_') . $dbName . '.sql';

    exec('mysqldump --routines --user=' . $dbUser . ' --password=' . $dbPass . ' ' . $dbName . ' > ' . $fileName);

    return true;
}
