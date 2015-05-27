<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace CB\UNITTESTS\HELPERS;

function getCBPath()
{
    return realpath(__DIR__.'/../../httpsdocs');
}

/**
 * 
 * @param type $corename
 */
function init($corename = DEFAULT_TEST_CORENAME)
{

    $CB_PATH                = getCBPath();
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    $_SERVER['SERVER_NAME'] = getHost();
    $_GET['core']           = $corename;
    $_SESSION['user'] = array('id' => 1);
    require_once $CB_PATH.'/config.php';
    require_once $CB_PATH.'/lib/language.php';

    // require_once '../../../lib/Util.php';
    \CB\L\initTranslations();
    \CB\Config::setEnvVar('user_language_index', 1);
}


/**
 * 
 * @param type $corename
 */
function prepareInstance($corename = DEFAULT_TEST_CORENAME)
{
   //$cmd_create_core = 'php '.CB_ROOT_PATH.'/bin/core_create_auto.php -c '.DEFAULT_TEST_CORENAME. ' -s '.CB_ROOT_PATH.'/install/examples/demosrc/mysql/demosrc.sql';

  /* $options = array(
            'core' => DEFAULT_TEST_CORENAME,
            'sql' => CB_ROOT_PATH . '/install/mysql/bare_bone_core.sql'
        );

    echo "Start Prepare instance for testing\n";
        include CB_ROOT_PATH.'/bin/core_create_auto.php';
    echo "End for Prepare instance\n"; */

    $options = array(
            'file' => TEST_PATH.'/src/config/install_config.ini'
        );

    echo "Start Prepare instance for testing\n";
        include CB_ROOT_PATH.'/bin/install.php';
    echo "End for Prepare instance\n";

    
    
}

function getCookieFilePath($corename = DEFAULT_TEST_CORENAME)
{
    $cookie_path = TEST_PATH_TEMP.'/cookie';
    //$cookie_filename = $cookie_path.'/'.$corename.'.txt';
    if (!file_exists($cookie_path)) {
        mkdir($cookie_path, 0755, true);
    }
    return $cookie_path;
}

/**
 * 
 * @param type $ch
 */
function setDefaulCurlParams(&$ch)
{

      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_COOKIEJAR, realpath(getCookieFilePath()));
      curl_setopt($ch, CURLOPT_COOKIEFILE, realpath(getCookieFilePath()));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
}

/**
 * 
 * @param type $corename
 * @return type
 */
function getCoreUrl($corename = DEFAULT_TEST_CORENAME)
{
    return 'https://'.getHost().'/'.$corename.'/';
}

/**
 *  return associative arrays with current CASEBOX config
 */
function getCfg()
{

    // get host name from cfg
    $cfg_file = getCBPath().'/config.ini';
    if (file_exists($cfg_file)) {
        $data_cfg = parse_ini_file($cfg_file);
        return $data_cfg;
    } else {
        trigger_error('can\'t fine current cfg file', E_USER_WARNING);
    }
}

/**
 * get host of testing server
 * for future refactor to get host from configuration
 * @return string
 */
function getHost()
{
    $cfg = getCfg();

    if (isset($cfg['server_name'])) {
        $sourceUrl = parse_url($cfg['server_name']);
        $host      = $sourceUrl['host'];
        return $host;
    } else {
        trigger_error('cant read cfg for server_name', E_USER_WARNING);
    }
}

/**
 * 
 * @param type $corename
 */
function getLoginKey($corename = DEFAULT_TEST_CORENAME)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, getCoreUrl($corename).'login/');
    setDefaulCurlParams($ch);

    //return the transfer as a string
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);

    // $output = curl_exec($ch);
    // echo "output: $output\n";
    // echo "-- curl info --------\n";
    //var_dump(curl_getinfo($ch));

    curl_close($ch);
}

/**
 * 
 * @param type $corename
 * @param type $username
 * @param type $userpass
 */
function login($corename = DEFAULT_TEST_CORENAME,
               $username = DEFAULT_TEST_USERNAME,
               $userpass = DEFAULT_TEST_USERPASS)
{



        $fields = ['u' => 'root',
                   'p' => 'r00t',
                   's' => 'Login'];

    // create curl resource
    $ch = curl_init();

    // set url
    curl_setopt($ch, CURLOPT_URL, getCoreUrl($corename).'login/auth/');
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

    curl_setopt($ch, CURLOPT_COOKIEJAR, getCookieFilePath($corename));
    curl_setopt($ch, CURLOPT_COOKIEFILE, getCookieFilePath($corename));

    //return the transfer as a string
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);

    // $output contains the output string
    $output = curl_exec($ch);
    $info = curl_getinfo($ch);
    if(isset($info['redirect_url']) && $info['redirect_url'] == getCoreUrl($corename) ) {
        return true;
    } else {
        return false;
    }

    /*echo "Login: $output\n";
     echo "-- </Login> --------\n";
     var_dump(curl_getinfo($ch));
     echo "----------------\n";
    // close curl resource to free up system resources */
     
    curl_close($ch);
}
