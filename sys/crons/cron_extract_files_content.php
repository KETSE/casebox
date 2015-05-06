<?php
namespace CB;

$cron_id = 'extract_files_content';
$execution_timeout = 2 * 60; //default is 60 seconds

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'init.php';

$cd = prepareCron($cron_id, $execution_timeout);

if (!$cd['success']) {
    echo "\nerror preparing cron\n";
    exit(1);
}

$rez = array(
    'Total' => 0
    ,'Processed' =>0
    ,'Not found'=> 0
    ,'Processed List' => array()
    ,'Not found List' => array()
);
$processed_list = array();
$not_fount_list = array();

$sql = 'UPDATE crons
    SET last_end_time = CURRENT_TIMESTAMP, execution_info = $2
    WHERE cron_id = $1';

DB\dbQuery($sql, array($cron_id, json_encode($rez, JSON_UNESCAPED_UNICODE))) or die(DB\dbQueryError());

if (checkTikaService() == false) {
    startTikaService();
}

$where = 'skip_parsing = 0 and (parse_status is null)';

if (!empty($scriptOptions['all'])) {
    $where =  'skip_parsing = 0';
}

$sql = 'SELECT id
    ,path
    ,`type`
    ,pages
FROM files_content
WHERE '.$where;
$res = DB\dbQuery($sql) or die(DB\dbQueryError()); //and name like \'%.pdf\'

while ($r = $res->fetch_assoc()) {
    $filename = Config::get('files_dir').$r['path'].DIRECTORY_SEPARATOR.$r['id'];
    echo "\nFile: $filename (".$r['type'].") ";
    if (file_exists($filename)) {
        $skip_parsing = 0;
        $pages = $r['pages'];
        if (substr($r['type'], 0, 5) != 'image') {
            if (!file_exists($filename.'.gz')) {
                echo "\nnot image processing content ...";
                $tikaRez = getTikaResult($filename);
                if ($tikaRez !== false) {
                    file_put_contents($filename.'.zip', $tikaRez);
                    $text = getZipFileContent($filename.'.zip', '__TEXT__');
                    $text = mb_convert_encoding($text, mb_detect_encoding($text), 'UTF-8');
                    $text = str_replace(array("\n", "\r", "\t"), ' ', $text);
                    $text = trim($text);
                    if (!empty($text)) {
                        echo "... size: ".strlen($text)."\n";
                        $text = gzcompress($text, 9);
                        file_put_contents($filename.'.gz', $text);
                    } else {
                        $skip_parsing = 1;
                    }

                    $meta = getZipFileContent($filename.'.zip', '__METADATA__');
                    $meta = mb_convert_encoding($meta, mb_detect_encoding($meta), 'UTF-8');
                    preg_match('/page-count",\s*"([0-9]+)"/i', $meta, $matches);
                    $pages = empty($matches[1]) ? null : $matches[1];
                    echo " pages: $pages";

                    unlink($filename.'.zip');
                }//else $skip_parsing = 1;
            }
        } else {
            $skip_parsing = 1;
        }

        DB\dbQuery(
            'UPDATE files_content
            SET parse_status = 1
                ,pages = $2
                ,skip_parsing = $3
            WHERE id = $1',
            array(
                $r['id']
                ,$pages
                ,$skip_parsing
            )
        ) or die('error2');

        $rez['Processed'] = $rez['Processed'] +1;
        $rez['Processed List'][] =  $filename;
    } else {
        echo " - Not found.";
        $rez['Not found'] = $rez['Not found']+1;
        $rez['Not found List'][] = $filename;
    }

    DB\dbQuery(
        'UPDATE crons
        SET last_action = CURRENT_TIMESTAMP
        WHERE cron_id = $1',
        $cron_id
    ) or die('error updating crons last action');
    echo '.';
}
$res->close();
$rez['Total'] = $rez['Processed'] + $rez['Not found'];


// closeCron($cron_id, json_encode($rez, JSON_UNESCAPED_UNICODE));

// Solr\Client::runCron();

function checkTikaService()
{
    $rez = true;

    // Create a curl handle to a non-existing location
    $ch = curl_init('http://localhost:9998/tika');

    // Execute
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);

    // Check if any error occured
    if (curl_errno($ch)) {
        $rez = false;
    }

    // Close handle
    curl_close($ch);

    return $rez;
}

function startTikaService()
{
    $cmd = 'java -Dfile.encoding=UTF8 -jar "'.Config::get('TIKA_SERVER').'" --port 9998 &';
    if (IS_WINDOWS) {
        $cmd = 'start /D "'.DOC_ROOT.'libx" tika_windows_service.bat';
    }

    return pclose(popen($cmd, "r"));
}

function getTikaResult($filename)
{
    $file = fopen($filename, "rb");

    $ch = curl_init('http://127.0.0.1:9998/all');

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_PUT, true);
    curl_setopt($ch, CURLOPT_INFILE, $file);
    curl_setopt($ch, CURLOPT_INFILESIZE, filesize($filename));
    $rez = curl_exec($ch);

    // Check if any error occured
    if (curl_errno($ch)) {
        $rez = false;
    }
    // Close handle
    curl_close($ch);
    fclose($file);

    return $rez;
}

function getZipFileContent ($zip_file, $filename)
{
    $content = '';
    $z = new \ZipArchive();
    if ($z->open($zip_file)) {
        $fp = $z->getStream($filename);
        if (!$fp) {
            return false;
        }

        while (!feof($fp)) {
            $content .= fread($fp, 2);
        }

        fclose($fp);
    }

    return $content;
}
