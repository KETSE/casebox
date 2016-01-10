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

// This storage is freed on error (case of allowed memory exhausted)
Cache::set('memory', str_repeat('*', 1024 * 1024));

register_shutdown_function('CB\\onScriptShutdown');

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

DB\dbQuery($sql, array($cron_id, Util\jsonEncode($rez)));

if (checkTikaService() == false) {
    startTikaService();
    sleep(10);
}

$where = 'skip_parsing = 0 and (parse_status is null)';

if (!empty($scriptOptions['all'])) {
    $where =  'skip_parsing = 0';
}

$sql = 'SELECT id
    ,path
    ,`type`
    ,`size`
    ,pages
FROM files_content
WHERE '.$where;

$res = DB\dbQuery($sql); //and name like \'%.pdf\'

while ($r = $res->fetch_assoc()) {
    Cache::set('lastRecId', $r['id']);

    $filename = Config::get('files_dir').$r['path'].DIRECTORY_SEPARATOR.$r['id'];
    echo "\nFile: $filename (".$r['type'].") ";
    if (file_exists($filename)) {
        $skip_parsing = 0;
        $pages = $r['pages'];
        if (substr($r['type'], 0, 5) != 'image') {
            if (!file_exists($filename.'.gz')) {
                echo "\nnot image processing content ...";
                $tikaRez = false;
                try {
                    $tikaRez = getTikaResult($filename);
                } catch (\Exception $e) {

                }

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
                    }

                    $meta = getZipFileContent($filename.'.zip', '__METADATA__');
                    $meta = mb_convert_encoding($meta, mb_detect_encoding($meta), 'UTF-8');
                    preg_match('/page-count",\s*"([0-9]+)"/i', $meta, $matches);
                    $pages = empty($matches[1]) ? null : $matches[1];

                    //try another match
                    if (empty($pages)) {
                        preg_match('/NPages",\s*"([0-9]+)"/i', $meta, $matches);
                        $pages = empty($matches[1]) ? null : $matches[1];

                    }
                    echo " pages: $pages";

                    if (empty($text) && empty($pages)) {
                        $skip_parsing = 1;
                        notifyAdminAboutContent($r['id']);
                    }

                    unlink($filename.'.zip');
                } else {
                    $skip_parsing = 1;
                }
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
        );// or die('error2');

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


// closeCron($cron_id, Util\jsonEncode($rez));

// Solr\Client::runCron();

function checkTikaService()
{
    $rez = true;

    // Create a curl handle to a non-existing location
    $handler = curl_init('http://127.0.0.1:9998/tika');

    // Execute
    curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
    curl_exec($handler);

    // Check if any error occured
    if (curl_errno($handler)) {
        $rez = false;
    }

    // Close handle
    curl_close($handler);

    return $rez;
}

function startTikaService()
{
    $cmd = 'java -Dfile.encoding=UTF8 -jar "'.Config::get('TIKA_SERVER').'" --host=127.0.0.1 --port 9998 &';
    if (IS_WINDOWS) {
        $cmd = 'start /D "'.DOC_ROOT.'libx" tika_windows_service.bat';
    }

    $rez = pclose(popen($cmd, "r"));

    return $rez;
}

function getTikaResult($filename)
{
    $file = fopen($filename, "rb");

    $handler = curl_init('http://127.0.0.1:9998/unpack/all');

    curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handler, CURLOPT_PUT, true);
    curl_setopt($handler, CURLOPT_INFILE, $file);
    curl_setopt($handler, CURLOPT_INFILESIZE, filesize($filename));
    $rez = curl_exec($handler);

    // Check if any error occured
    if (curl_errno($handler)) {
        $rez = false;
    }

    // Close handle
    curl_close($handler);
    fclose($file);

    return $rez;
}

function onScriptShutdown ()
{
    Cache::remove('memory');

    if ((!is_null($err = error_get_last())) && (!in_array($err['type'], array (E_NOTICE, E_WARNING)))) {
        //mark last processed file to be skipped parsing

        $id = Cache::get('lastRecId', false);

        if (!empty($id)) {
            notifyAdminAboutContent($id);

            //update db status
            DB\dbQuery(
                'UPDATE files_content
                SET skip_parsing = 1
                WHERE id = $1',
                $id
            );

        }
    }
};

//notify admin about content processing failure
function notifyAdminAboutContent($contentId)
{
    //select latest file with that content
    $fileIds = DataModel\Files::getContentIdReferences($contentId);
    $fileInfo = '';
    if (!empty($fileIds)) {
        $p = new Objects\Plugins\SystemProperties();
        $fileId = array_pop($fileIds);
        $data = $p->getData($fileId);

        $d = &$data['data'];

        $fileInfo = '<table border="0">' .
            '<tr><td>ID:</td><td>' . $d['id'] . '</td></tr>' .
            '<tr><td>Name:</td><td>' . Objects::getName($fileId) . '</td></tr>' .
            '<tr><td>Path:</td><td>' . $d['path'] . '</td></tr>' .
            '<tr><td>Creator: </td><td>' . $d['cid_text'] . '</td></tr>' .
            '</table>';
    }

    $err = error_get_last();

    if (!is_null($err) && (!in_array($err['type'], array (E_NOTICE, E_WARNING)))) {
        $fileInfo .= "\n\r<hr />\n\r" . $err['message'];
    }

    System::notifyAdmin(
        'Casebox error extracting file content #' . $contentId,
        $fileInfo
    );
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
