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

    // Connect to database
    $dbh = connectDB($dbConfig);

    exportTranslation($dbh);

} else {
    die("ERROR: config not found ".$cnfFilename."\n");
}

function exportTranslation($dbh)
{

    $sql = "SELECT *
        FROM translations
        WHERE deleted = 0";

    $rez = array();

    foreach ($dbh->query($sql) as $r) {
        $id = $r['id'];
        $name = $r['name'];
        $info = $r['info'];

        unset($r['id']);
        unset($r['pid']);
        unset($r['name']);
        unset($r['type']);
        unset($r['info']);
        unset($r['deleted']);

        foreach ($r as $k => $v) {
            if (is_numeric($k)) {
                continue;
            }
            $v = empty($v)
                ? $r['en']
                : $v;

            $rez[$k][] = '<trans-unit id="' . $id . '">
                <source>' . $name . '</source>
                <target>' . $v . '</target>' .
                (empty($info)
                    ? ''
                    : '
                    <note> ' . $info . ' </note>'
                )
                .'
            </trans-unit>';
        }
    }

    foreach ($rez as $k => &$v) {
        $content = '<!-- app/resources/translations/' . $k . '.xlf -->
<?xml version="1.0"?>
<xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
    <file source-language="en" target-language="' . $k . '" datatype="plaintext" original="en.xlf">
        <body>
            ' . implode("\n            ", $v) . '
        </body>
    </file>
</xliff>
';
        file_put_contents($k . '.xlf', $content);
    }
    echo "\nLanguages exported\n";
}

function connectDB($dbConfig)
{
    $dbh = pdoConnect($dbConfig);
    $dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

    // $dbh->set_charset('utf8');
    return $dbh;
}

function pdoConnect($p)
{
    return new \PDO(
        'mysql:host=' . $p['host'] .
        ';port='      . $p['port'] .
        ';dbname='    . $p['dbname'].
        ';charset=utf8',
        $p['user'],
        $p['pass']
    ); // array( PDO::ATTR_PERSISTENT => false)
}
