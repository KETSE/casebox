<?php 
namespace CB;

//error_reporting(E_ALL);
//ini_set('display_errors', 1);
if (isset($_GET['state']) && isset($_GET['code'])) {

    require_once __DIR__ . '/classes/CB/Oauth2Utils.php';
    $state = Oauth2Utils::decodeState($_GET['state']);

    if (isset($state['core'])&&isset($state['state'])) {

        $_GET['core'] = $state['core'];
        $oauthMode = 1;
        
        $SESSION_NAME = str_replace(
                array(
                '.casebox.org'
                , '.'
                , '-'
                ), '', $_SERVER['SERVER_NAME']
            ) . $state['core'];

        // set id of session, to prevent create new session id on redirect 
        $_COOKIE[$SESSION_NAME] = $state['state'];
        
        require_once 'init.php';
         $coreUrl      = \CB\Config::get('core_url');
        // $coreUrl = \CB\Config::get('server_name') . $state['core'] . '/';
        $provider = Oauth2Utils::getGoogleProvider();
        $encodedState = Oauth2Utils::getLocalState($provider, $_GET['state'], $_GET['code']);
        header('Location: ' . $coreUrl . 'login/auth/?state=' . $encodedState);
    } else {
        trigger_error('oauth2callback core on encoded state is not set ', E_USER_WARNING);
    }
} else {
    trigger_error('oauth2callback wrong parameters ' . print_r($_GET, true), E_USER_WARNING);
}