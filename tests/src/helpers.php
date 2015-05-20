<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace CB\UNITTESTS\HELPERS;

function getCBPath()
{
    return realpath(__DIR__ . '/../../httpsdocs');
}

/**
 * 
 * @param type $corename
 */
function init($corename = DEFAULT_TEST_CORENAME)
{
    $CB_PATH = getCBPath();
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    $_SERVER['SERVER_NAME'] = 'local.casebox.org';
    $_GET['core'] = $corename;
    $_SESSION['user'] = array('id' => 1);
    require_once $CB_PATH . '/config.php';
    require_once $CB_PATH . '/language.php';
    // require_once '../../../lib/Util.php';
    \CB\L\initTranslations();
    \CB\Config::setEnvVar('user_language_index', 1);
}

/**
 * 
 * @param type $corename
 */
function prepareDB($corename = DEFAULT_TEST_CORENAME) {

        $p = ['dbUser' => 'local',
        'dbPass' => 'h0st',
        'dbName' => 'cb_' . $corename,
        'sqlFile' => TEST_PATH.'/src/SQL/raw.sql'
    ];

    // import into MySql
    $cmd = 'mysql --user=' . $p['dbUser'] . ' --password=' . $p['dbPass'] . ' -e "create database ' . $p['dbName'] . '"';
    echo "\nsql: $cmd\n";
    echo exec($cmd);

    $cmd = 'mysql --user=' . $p['dbUser'] . ' --password=' . $p['dbPass'] . ' ' . $p['dbName'] . ' < ' . $p['sqlFile'];
    echo "\nsql: $cmd\n";
    echo exec($cmd);
}

function prepareSolr($corename = DEFAULT_TEST_CORENAME) {
    
   // drop SOLR core
    $url = 'http://localhost:8983/solr/admin/cores?action=UNLOAD&deleteInstanceDir=true&core=cb_' . $corename;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    $output = curl_exec($ch);


    // create SOLR core
    $url = 'http://localhost:8983/solr/admin/cores?action=CREATE&configSet=cb_default&name=cb_' . $corename;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    $output = curl_exec($ch);

    //
    $cmd = 'php ../bin/solr_reindex_core.php -c ' . $corename . ' -a -l > exec.txt';
    //echo "\nreindex_core: $cmd\n";
    exec($cmd);
}
/**
 * 
 * @param type $corename
 */
function prepareInstance($corename = DEFAULT_TEST_CORENAME)
{
    prepareDB($corename);
    prepareSolr($corename);
}


function getCookieFilePath($corename = DEFAULT_TEST_CORENAME) {
    $cookie_path = TEST_PATH_TEMP.'/cookie';
    //$cookie_filename = $cookie_path.'/'.$corename.'.txt';
   if(!file_exists($cookie_path)) {
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
    
    Unirest\Request::cookieFile(getCookieFileName());
        
    /*curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    curl_setopt($ch, CURLOPT_COOKIEJAR, realpath(getCookieFileName()));
    curl_setopt($ch, CURLOPT_COOKIEFILE, realpath(getCookieFileName()));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); */
}

/**
 * 
 * @param type $corename
 * @return type
 */
function getCoreUrl($corename = DEFAULT_TEST_CORENAME)
{
    return 'https://dev-u1.casebox.org/' . $corename . '/';
}

/**
 * 
 * @param type $corename
 */
function getLoginKey($corename = DEFAULT_TEST_CORENAME)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, getCoreUrl($corename) . 'login/');
    $this->setDefaulCurlParams($ch);

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
function login($corename = DEFAULT_TEST_CORENAME, $username = DEFAULT_TEST_USERNAME, $userpass = DEFAULT_TEST_USERPASS)
{

 
     
    // create curl resource
   /* $ch = curl_init();

    // set url
    curl_setopt($ch, CURLOPT_URL, getCoreUrl($corename) . 'login/auth/');
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

    curl_setopt($ch, CURLOPT_COOKIEJAR, realpath($this->cookieFile));
    curl_setopt($ch, CURLOPT_COOKIEFILE, realpath($this->cookieFile));

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

    // echo "Login: $output\n";
    // echo "-- </Login> --------\n";
    // var_dump(curl_getinfo($ch));
    // echo "----------------\n";
    // close curl resource to free up system resources
    curl_close($ch); */
}
