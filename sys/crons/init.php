<?php
/**
*   Initialization file for crons
*
*   @author Țurcanu Vitalie <vitalie.turcanu@gmail.com>
*   @access private
*   @package CaseBox
*   @copyright Copyright (c) 2011, CaseBox
**/
namespace CB;

// if no corename argument passed then exit
if (empty($argv[1])) {
    echo "\nError: no core argument given\n";
    exit(0);
}

ini_set('max_execution_time', 0);
ini_set('allow_url_fopen', true);
error_reporting(E_ALL);

$_GET['core'] = $argv[1];
$_SERVER['SERVER_NAME'] = $argv[1];
$_SERVER['REMOTE_ADDR'] = 'localhost';

// session_start();
$_SESSION['user'] = array(
    'id' => 1
    ,'name' => 'system'
);

$site_path = realpath(
    dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.
    DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'httpsdocs'
).DIRECTORY_SEPARATOR;

include $site_path.DIRECTORY_SEPARATOR.'config.php';

require_once LIB_DIR.'Util.php';

require_once(DOC_ROOT.'language.php');
//L\initTranslations(); // would be called from inside crons that need translations

//--------------------------------------------------- functions
function prepareCron ($cron_id, $execution_timeout = 60, $info = '')
{
    $rez = array('success' => false);
    $res = DB\dbQuery(
        'SELECT id
            ,cron_id
            ,last_start_time
            ,last_end_time
            ,(DATE_ADD(last_action, INTERVAL '.$execution_timeout.' SECOND) < CURRENT_TIMESTAMP) `timeout`
        FROM crons
        WHERE cron_id = $1',
        array($cron_id)
    ) or die( DB\dbQueryError() );

    if ($r = $res->fetch_assoc()) {
        if (empty($r['last_end_time'])) {
            if ($r['timeout'] == 0) { // seems that a cron instance is running
                echo "another cron is running\n";
                $res->close();

                return $rez;
            } else { //timeout ocured of script cron execution
                $title = 'CaseBox cron notification ('.$cron_id.'), timeout occured.';
                $msg = $info."\n\rCore name: ".CORE_NAME.print_r($r, 1);
                echo $title."\n".$msg;
                notifyAdmin($title, $msg);
            }
        } else { //no cron is currently running

        }

        $rez = $r;
        $rez['success'] = true;
    } else {
        global $cron_id;
        $rez['success'] = true;
        $t = debug_backtrace();
        DB\dbQuery(
            'INSERT INTO crons (cron_id, cron_file)
            VALUES($1
                 , $2)',
            array(
                $cron_id
                ,$t[0]['file']
            )
        ) or die( DB\dbQueryError() );
        $rez['id'] = DB\dbLastInsertId();
    }
    $res->close();
    DB\dbQuery(
        'UPDATE crons
        SET last_start_time = CURRENT_TIMESTAMP
            ,last_end_time = NULL
            ,last_action = CURRENT_TIMESTAMP, execution_info=NULL
        WHERE id = '.$rez['id']
    ) or die('error');

    return $rez;
}

function notifyAdmin($subject, $message)
{
    echo 'Notifying admin: '.CONFIG\ADMIN_EMAIL;
    mail(CONFIG\ADMIN_EMAIL, $subject, $message, 'From: '.CONFIG\SENDER_EMAIL. "\r\n");
}
