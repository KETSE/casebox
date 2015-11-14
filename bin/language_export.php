#!/usr/bin/php
<?php
/*
    Export language translation to a CSV file:

    Parameters:
    1st: filename
    2nd: language iso code

    Example:
        php export_translation.php file.csv es

*/

setlocale(LC_ALL, "en_US.utf8");

$cnfFilename = realpath(__DIR__.'/../httpsdocs').'/config.ini';

if (file_exists($cnfFilename)) {

    $cnf = parse_ini_file($cnfFilename);

    $dbConfig = [
        'host' => $cnf['db_host']
        ,'dbname' => $cnf['prefix'].'__casebox'
        ,'user' => $cnf['db_user']
        ,'pass' => $cnf['db_pass']
        ,'port' => $cnf['db_port']
    ];

    // CSV file as first parameter
    $csv = @$argv[1];

    // Language iso code
    $lg = @$argv[2];

    if (! $csv) {
        echo ("Specify CSV filename\n");
        die;
    }

    if (! $lg) {
        echo ("Specify language code (example: en, ru, es)\n");
        die;

    }

    // Connect to database
    $dbh = connectDB($dbConfig);

    exportTranslation($dbh, $csv, $lg);

} else {
    die("ERROR: config not found ".$cnfFilename."\n");
}

function exportTranslation($dbh, $csv, $lg)
{

    $sql = "SELECT id, name, en, `$lg` AS title, info
        FROM translations
        WHERE deleted = 0";

    if (($handle = fopen($csv, "w")) === false) {
        echo ("Can't open file for writing\n");
        die;
    }

    $a = ['ID', 'Term name', 'English', $lg, 'Info'];
    fputcsv($handle, $a);

    foreach ($dbh->query($sql) as $row) {
        // $s = 'id=' . $row['id'] . ', title=' . $row['title'] . "\n";
        // echo "$s\n";

        $a = [$row['id'], $row['name'], $row['en'], $row['title'], $row['info']];
        fputcsv($handle, $a);
    }

    fclose($handle);
    echo "Language '$lg' exported\n";
}

function connectDB($dbConfig)
{
    $dbh = pdoConnect($dbConfig);
    $dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

    $dbh->set_charset('utf8');

    return $dbh;
}

function pdoConnect($p)
{
    return new \PDO(
        'mysql:host=' . $p['host'] .
        ';port='      . $p['port'] .
        ';dbname='    . $p['dbname'],
        $p['user'],
        $p['pass']
    ); // array( PDO::ATTR_PERSISTENT => false)
}
