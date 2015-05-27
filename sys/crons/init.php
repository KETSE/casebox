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

ini_set('max_execution_time', 0);
ini_set('allow_url_fopen', true);
error_reporting(E_ALL);

$scriptOptions = getOptions();

if (empty($scriptOptions['core'])) {
    die('no core specified or invalid options set.');
}

$_GET['core'] = $scriptOptions['core'];
$_SERVER['SERVER_NAME'] = $scriptOptions['core'];
$_SERVER['REMOTE_ADDR'] = 'localhost';

$_SESSION['user'] = array(
    'id' => 1
    ,'name' => 'system'
);

$site_path = realpath(
    dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.
    DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'httpsdocs'
).DIRECTORY_SEPARATOR;

register_shutdown_function('CB\\closeCron', $cron_id);

include $site_path.DIRECTORY_SEPARATOR.'config.php';

require_once(LIB_DIR.'language.php');

\CB\Cache::set('scriptOptions', $scriptOptions);

$coreName = Config::get('core_name');
//L\initTranslations(); // would be called from inside crons that need translations

//--------------------------------------------------- functions
function getOptions()
{
    $rez = array();

    $options = getopt('c:alf', array('core', 'all', 'nolimit', 'force'));

    $rez['core'] = empty($options['c'])
        ? @$options['core']
        : $options['c'];

    $rez['all'] = isset($options['a']) || isset($options['all']);

    $rez['nolimit'] = isset($options['l']) || isset($options['nolimit']);

    $rez['force'] = isset($options['f']) || isset($options['force']);

    return $rez;

}

function prepareCron ($cron_id, $execution_timeout = 60, $info = '')
{
    $scriptOptions = \CB\Cache::get('scriptOptions');
    $coreName = Config::get('core_name');

    if (!empty($scriptOptions['force'])) {
        return array('success' => true);
    }

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
                $msg = $info."\n\rCore name: ".$coreName.print_r($r, 1);
                echo $title."\n".$msg;
                System::notifyAdmin($title, $msg);
            }
        } else { //no cron is currently running

        }

        $rez = $r;
        $rez['success'] = true;

    } else {
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

/**
 * mark a cron as finished
 * @param  varchar $cron_id cron name
 * @return void
 */
function closeCron($cron_id, $info = 'ok')
{
    $scriptOptions = \CB\Cache::get('scriptOptions');

    if (!empty($scriptOptions['force'])) {
        return;
    }

    DB\dbQuery(
        'UPDATE crons
        SET last_end_time = CURRENT_TIMESTAMP, execution_info = $2
        WHERE cron_id = $1',
        array($cron_id, $info)
    ) or die(DB\dbQueryError());
}
