#!/usr/bin/php
<?php
/*
    Import language translation from a CSV file:

    Parameters:
    1st: filename
    2nd: language iso code
    3rd: the column number that contains the translation  (optional, by default 2)

    Example:
        php import_translation.php file.csv es
        // this command will import into 'es' column of casebox.translations table the 2nd column from CSV

    to specify which column to import:
        php import_translation.php file.csv es 4
        will use the 4th column of the csv file

*/

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

    // optional parameter: CSV column
    $col = @$argv[3];
    if (! $col) {
        // default column = 2
        $col = 2;
    }

    if (! $csv) {
        echo ("Specify CSV filename\n");
        die;
    }

    if (! file_exists($csv)) {
        echo ("Filename doesn't exist\n");
        die;
    }

    if (! $lg) {
        echo ("Specify language code (example: en, ru, es)\n");
        die;

    }

    // Connect to database
    $dbh = connectDB($dbConfig);

    importTranslation($dbh, $csv, $lg, $col);

} else {
    die("ERROR: config not found ".$cnfFilename."\n");
}

function importTranslation($dbh, $csv, $lg, $col)
{

    $dbh->beginTransaction();
    $sql = "UPDATE translations SET `$lg`=:title WHERE id=:id";
    $q = $dbh->prepare($sql);

    $row = 1;
    if (($handle = fopen($csv, "r")) !== false) {
        while (($data = fgetcsv($handle, 0, ",")) !== false) {
            $row++;

            $id = $data[0];
            $title = $data[$col-1];  // first col is at index "0"

            // remove new lines
            $title = preg_replace('/\n/', '', $title);
            $title = preg_replace('/\r/', '', $title);

            $q->execute(
                array(
                    ':id'          => $id
                    ,':title'     => $title
                )
            );

            // echo ("id=$id: title=$title\n");
        }
        fclose($handle);
    }
    $dbh->commit();
    echo "Language '$lg' imported\n";
}

function connectDB($dbConfig)
{
    $dbh = pdoConnect($dbConfig);
    $dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

    $dbh->set_charset('utf8');
    $dbh->exec("SET AUTOCOMMIT = 0");

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
