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

$path = dirname(__FILE__) . DIRECTORY_SEPARATOR;
$cbPath = dirname($path) . DIRECTORY_SEPARATOR;
require_once $cbPath . 'httpsdocs/config_platform.php';

require_once $path . 'install_functions.php';

//check script options
$options = getopt('c:s:', array('core:', 'sql:'));

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

defineBackupDir($cfg);

$dbName = PREFIX . $coreName;
$dbUser = $cfg['db_user'];
$dbPass = $cfg['db_pass'];

$applyDump = true;

if (DB\dbQuery('use `' . $dbName . '`')) {
    if (confirm('Database "' . $dbName.'"  already exists. Would you like to overwrite it?')) {
        echo 'Backuping .. ';
        backupDB($dbName, $dbUser, $dbPass);
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
    $l = readALine('Specify email address for root user:' . "\n");
    $email = $l;
} while (empty($l));

do {
    $l = readALine('Specify root user password:' . "\n");
    $pass = $l;
} while (empty($l));

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

//verify if solr core exist
$solrHost = $cfg['solr_host'];
$solrPort = $cfg['solr_port'];
$askReindex = true;

$solr = Solr\Service::verifyConfigConnection(
    array(
        'host' => $solrHost
        ,'port' => $solrPort
        ,'core' => $dbName
        ,'SOLR_CLIENT' => $cfg['SOLR_CLIENT']
    )
);

if ($solr === false) {
    if (confirm('Solr core "' . $dbName . '" doesnt exist. Would you like to create it? (y/n): ')) {
        echo 'Creating solr core ... ';

        if ($h = fopen(
            'http://' . $solrHost. ':' . $solrPort . '/solr/admin/cores?action=CREATE&' .
            'name=' . $dbName . '&configSet=cb_default',
            'r'
        )) {
            fclose($h);

            echo "Ok\n";
        } else {
            echo "Error creating core.\n";
        }
    } else {
        $askReindex = false;
    }
} else {
    echo "Solr core exists.\n";
}

if ($askReindex) {
    if (confirm('Do you want to start full core reindex? (y/n): ')) {
        echo 'Reindex solr core ... ';
        exec('php -f ' . $path . 'solr_reindex_core.php -- -c ' . $coreName . ' -a -l');
        echo "Ok\n";
    }
}

echo "Done.\n";
