<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace UnitTest;

use \CB\Install;
use \CB\Config;
use \CB\L;
use \CB\Cache;

class Helpers
{
    /**
     *
     * @param type $corename
     */
    public static function init($corename = DEFAULT_TEST_CORENAME)
    {

        $CB_PATH                = \CB_DOC_ROOT;
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['SERVER_NAME'] = static::getHost();
        $_GET['core']           = $corename;
        $_SESSION['user']       = array('id' => 1, 'groups' => [1] );

        require_once $CB_PATH.'/config.php';
        require_once $CB_PATH.'/lib/language.php';

        L\initTranslations();
        Config::setEnvVar('user_language_index', 1);
        \CB\User::setAsLoged(1, 'AbrACadaBraK333y');
    }

    /**
     * get path to file configuration for make test install
     * @return string
     */
    public static function getConfigFilename()
    {
        // return \CB_DOC_ROOT . 'config.ini';
        return TEST_PATH_TEMP . 'auto_install_config.ini';
    }

    /**
     * get path to template for file configuration
     * @return string
     */
    public static function getConfigFilenameTPL()
    {
        return TEST_PATH . 'auto_install_config.ini';
    }

    /**
     *
     * @param type $corename
     */
    public static function prepareInstance($corename = DEFAULT_TEST_CORENAME)
    {
        $corename = $corename; //dummy codacy assignment

        try {
            require_once CB_ROOT_PATH . 'httpsdocs/config_platform.php';

        } catch (\Exception $e) {
            //config.ini could not exist
            //we don't need to do anything here because this script will create confing.ini in result
            //we just use values form config.ini as defaults, if it exists
        }

        require_once \CB\LIB_DIR . 'install_functions.php';


        $configFilename = static::getConfigFilename();

        $configFilenameTpl = static::getConfigFilenameTPL();

        if (!file_exists($configFilename)) {
            if (file_exists($configFilenameTpl)) {
                $test_cfg = parse_ini_file($configFilenameTpl);
                Cache::set('RUN_SETUP_INTERACTIVE_MODE', true);

                $test_cfg['backup_dir']      = CB_ROOT_PATH . 'backup'.DIRECTORY_SEPARATOR;
                //$test_cfg['server_name']     = Install\readParam('server_name', $test_cfg['server_name']);
                $test_hostname               = preg_replace('/^http(s)?:\/\//si', '', $test_cfg['server_name']);

                $tests_solr_path = CB_ROOT_PATH.'tests/server/solr/solr-5.2.0/server/';

                if (file_exists($tests_solr_path)) {
                    $test_cfg['solr_home'] = CB_ROOT_PATH.'tests/server/solr/solr-5.2.0/server/';
                }

                $test_cfg['admin_email']     = 'admin@'.$test_hostname;
                $test_cfg['sender_email']    = 'sender@'.$test_hostname;
                $test_cfg['comments_email']  = 'comments@'.$test_hostname;
                $test_cfg['core_root_email'] = 'root@'.$test_hostname;

                if (!\CB\IS_WINDOWS) {
                    //ask for apache user and set ownership for some folders
                    // $test_cfg['apache_user'] = Install\readParam('apache_user', $test_cfg['apache_user']);
                }

                /* $test_cfg['db_user']     = Install\readParam('db_user', $test_cfg['db_user']);
                $test_cfg['db_pass']     = Install\readParam('db_pass');

                $test_cfg['su_db_user'] = Install\readParam('su_db_user', $test_cfg['su_db_user']);
                $test_cfg['su_db_pass'] = Install\readParam('su_db_pass'); */

                echo 'writing autoconfig file to:'.$configFilename.PHP_EOL;

                Install\putIniFile($configFilename, $test_cfg);

                if (!\CB\IS_WINDOWS) {
                    //     shell_exec('chown ' . $test_cfg['apache_user'].' "' . $configFilename . '"');
                }

                Cache::set('RUN_SETUP_INTERACTIVE_MODE', false);
            }
        }


        if (file_exists($configFilename)) {
            $options = array(
                'file' => $configFilename
            );

            include CB_ROOT_PATH . 'bin/install.php';

        } else {
            $error_msg = ' Please create cofig file : '.$configFilename.PHP_EOL.' '
                .' You can use file: ' . TEST_PATH . 'auto_install_config.ini as template '.PHP_EOL.PHP_EOL;

            trigger_error($error_msg, E_USER_ERROR);
        }
    }

    public static function getCookieFilePath()
    {

        $cookie_path = TEST_PATH_TEMP . 'cookie';

        if (!file_exists($cookie_path)) {
            mkdir($cookie_path, 0755, true);
        }

        return $cookie_path;
    }

    /**
     *
     * @param type $ch
     */
    public static function setDefaulCurlParams(&$ch)
    {

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_COOKIEJAR, realpath(static::getCookieFilePath()));
        curl_setopt($ch, CURLOPT_COOKIEFILE, realpath(static::getCookieFilePath()));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    }

    /**
     *
     * @param  type $corename
     * @return type
     */
    public static function getCoreUrl($corename = DEFAULT_TEST_CORENAME)
    {
        return 'https://' . static::getHost() . '/' . $corename . '/';
    }

    /**
     *  return associative arrays with current CASEBOX config
     */
    public static function getCfg()
    {

        // get host name from cfg
        $cfg_file = \CB_DOC_ROOT . 'config.ini';

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
    public static function getHost()
    {
        $cfg = static::getCfg();

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
    public static function getLoginKey($corename = DEFAULT_TEST_CORENAME)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, static::getCoreUrl($corename).'login/');
        static::setDefaulCurlParams($ch);

        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);

        curl_close($ch);
    }

    /**
     *
     * @param type $corename
     * @param type $username
     * @param type $userpass
     */
    public static function login($username = DEFAULT_TEST_USERNAME, $userpass = DEFAULT_TEST_USERPASS, $corename = DEFAULT_TEST_CORENAME)
    {

        $fields = [
            'u' => $username,
            'p' => $userpass,
            's' => 'Login'];

        // create curl resource
        $ch = curl_init();

        // set url
        curl_setopt($ch, CURLOPT_URL, static::getCoreUrl($corename).'login/auth/');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

        curl_setopt($ch, CURLOPT_COOKIEJAR, static::getCookieFilePath());
        curl_setopt($ch, CURLOPT_COOKIEFILE, static::getCookieFilePath());

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
        if (isset($info['redirect_url']) && $info['redirect_url'] == static::getCoreUrl($corename)) {
            return true;
        } else {
            return false;
        }

        // close curl resource to free up system resources */
        curl_close($ch);
    }

    public static function getCredentialUserData($username)
    {

        $data = [
            'username' => 'root',
            'userpass' => 'test'
        ];

        switch ($username) {
            case 'root':
                $test_cnf_filename = static::getConfigFilename();
                $test_cnf = Config::loadConfigFile($test_cnf_filename);
                if (isset($test_cnf['core_root_pass'])) {
                    $data['userpass'] = $test_cnf['core_root_pass'];
                }
                break;

            default:
                $data['userpass'] = 'test';
        }

        return $data;
    }

    /**
     * assigns the output of a file into a variable... lovely jubbly!
     * @param  varchar $filename [description]
     * @param  string  $data     [description]
     * @return varchar
     */
    public static function getIncludeContents($filename, $data = '')
    {
        if (is_file($filename)) {

            if (is_array($data)) {
                extract($data);
            }

            ob_start();

            include $filename;

            $contents = ob_get_contents();

            ob_end_clean();

            return $contents;
        }

        return false;
    }
}
