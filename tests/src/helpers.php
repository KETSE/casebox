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
    $_SESSION['user']       = array('id' => 1);
    require_once $CB_PATH.'/config.php';
    require_once $CB_PATH.'/lib/language.php';

    // require_once '../../../lib/Util.php';
    \CB\L\initTranslations();
    \CB\Config::setEnvVar('user_language_index', 1);
}

/**
 * get path to file configuration for make test install
 * @return string
 */
function getConfigFilename()
{
    return TEST_PATH_TEMP.'/auto_install_config.ini';
}

/**
 * get path to template for file configuration
 * @return string
 */
function getConfigFilenameTPL()
{
    return TEST_PATH.'/src/config_templates/auto_install_config.ini';
}

/**
 *
 * @param type $corename
 */
function prepareInstance($corename = DEFAULT_TEST_CORENAME)
{

    try {
        require_once CB_ROOT_PATH.'/httpsdocs/config_platform.php';
    } catch (\Exception $e) {
        //config.ini could not exist
        //we don't need to do anything here because this script will create confing.ini in result
        //we just use values form config.ini as defaults, if it exists
    }

    require_once \CB\LIB_DIR.'install_functions.php';


    $config_filename = getConfigFilename();

    $config_filename_tpl = getConfigFilenameTPL();

    if (!file_exists($config_filename)) {
        if (file_exists($config_filename_tpl)) {

            $test_cfg = parse_ini_file($config_filename_tpl);
            \CB\Cache::set('RUN_SETUP_INTERACTIVE_MODE', true);

            $test_cfg['backup_dir']      = CB_ROOT_PATH.DIRECTORY_SEPARATOR.'backup'.DIRECTORY_SEPARATOR;
            $test_cfg['server_name']     = \CB\INSTALL\readParam('server_name', $test_cfg['server_name']);
            $test_hostname               = preg_replace('/^http(s)?:\/\//si', '', $test_cfg['server_name']);

            $test_cfg['solr_home']     = \CB\INSTALL\readParam('solr_home', $test_cfg['solr_home']);

            $test_cfg['admin_email']     = 'admin@'.$test_hostname;
            $test_cfg['sender_email']    = 'sender@'.$test_hostname;
            $test_cfg['comments_email']  = 'comments@'.$test_hostname;
            $test_cfg['core_root_email'] = 'root@'.$test_hostname;

            if (!\CB\IS_WINDOWS) {
                //ask for apache user and set ownership for some folders
                $test_cfg['apache_user'] = \CB\INSTALL\readParam('apache_user', $test_cfg['apache_user']);
            }

            $test_cfg['db_user']     = \CB\INSTALL\readParam('db_user', $test_cfg['db_user']);
            $test_cfg['db_pass']     = \CB\INSTALL\readParam('db_pass');

            $test_cfg['su_db_user'] = \CB\INSTALL\readParam('su_db_user', $test_cfg['su_db_user']);
            $test_cfg['su_db_pass'] = \CB\INSTALL\readParam('su_db_pass');

            echo 'writing autoconfig file to:'.$config_filename.PHP_EOL;

            \CB\INSTALL\putIniFile($config_filename, $test_cfg);

            if (!\CB\IS_WINDOWS) {
                shell_exec('chown ' . $test_cfg['apache_user'].' "' . $config_filename . '"');
            }


            \CB\Cache::set('RUN_SETUP_INTERACTIVE_MODE', false);
        }
    }


    if (file_exists($config_filename)) {

        $options = array(
            'file' => $config_filename
        );
        include CB_ROOT_PATH.'/bin/install.php';

    } else {
        $error_msg = ' Please create cofig file : '.$config_filename.PHP_EOL.' '
            .' You can use file: '.TEST_PATH.'/src/config_templates/auto_install_config.ini as template '.PHP_EOL.PHP_EOL;

        trigger_error($error_msg, E_USER_ERROR);
    }
}

function getCookieFilePath($corename = DEFAULT_TEST_CORENAME)
{

    $cookie_path = TEST_PATH_TEMP.'/cookie';

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
        trigger_error('can\'t find current cfg file', E_USER_WARNING);
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
function login($username = DEFAULT_TEST_USERNAME, $userpass = DEFAULT_TEST_USERPASS, $corename = DEFAULT_TEST_CORENAME)
{

    $fields = [
        'u' => $username,
        'p' => $userpass,
        's' => 'Login'];

    // create curl resource
    $ch = curl_init();

    // set url
    curl_setopt($ch, CURLOPT_URL, getCoreUrl($corename).'login/auth/');
    curl_setopt($ch, CURLOPT_POST, true);
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
    $info   = curl_getinfo($ch);
    if (isset($info['redirect_url']) && $info['redirect_url'] == getCoreUrl($corename)) {
        return true;
    } else {
        return false;
    }

    /* echo "Login: $output\n";
      echo "-- </Login> --------\n";
      var_dump(curl_getinfo($ch));
      echo "----------------\n";
      // close curl resource to free up system resources */

    curl_close($ch);
}

function getCredentialUserData($username)
{

    $data = [
        'username' => 'root',
        'userpass' => 'test'
    ];

    switch ($username) {
        case 'root':
            $test_cnf_filename = getConfigFilename();
            $test_cnf = \CB\Config::loadConfigFile($test_cnf_filename);
            if (isset($test_cnf['core_root_pass'])) {
                $data['userpass'] = $test_cnf['core_root_pass'];
            }
            break;

        default:
            $data['userpass'] = 'test';
    }

    return $data;
}
