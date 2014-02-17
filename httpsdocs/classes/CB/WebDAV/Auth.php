<?php
namespace CB\WebDAV;

use Sabre\DAV\Exception;
use Sabre\HTTP;

class Auth{
    function __construct(){

        $auth_flag = false;
        $auth = new HTTP\BasicAuth();
        $auth_params = $auth->getUserPass();

        $_SESSION['hash'] = (isset($_SESSION['hash'])) ? $_SESSION['hash'] : '';

        if($auth_params){
            $hash = $this->hash($auth_params);

            if($hash != $_SESSION['hash']){
                $user = new \CB\User();
                $r = $user->Login(strtolower(trim($auth_params[0])), $auth_params[1]);
                if ($r['success'] == true) {
                    $_SESSION['hash'] = $hash;
                    $_SESSION['user'] = $r['user'];

                    $auth_flag = true;
                }
            }else {
                $auth_flag = true;
            }
        }

        if(!$auth_flag){
            // no credits specified
            $auth->requireLogin();
            //throw new Exception\NotAuthenticated();
            die();
        }
    }

    function hash($array){
          return md5(json_encode($array));
    }
}