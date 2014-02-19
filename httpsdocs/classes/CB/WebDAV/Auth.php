<?php
namespace CB\WebDAV;

use Sabre\DAV\Exception;
use Sabre\HTTP;

class Auth{
    function __construct(){
        $auth_flag = false;
        $auth = new HTTP\BasicAuth();
        $auth_params = $auth->getUserPass();

        $user = new \CB\User();
        if(!$user->isLoged()){
            if($auth_params){
                $r = $user->Login(strtolower(trim($auth_params[0])), $auth_params[1]);
                if ($r['success'] == true) {
                    $auth_flag = true;
                }
            }
        }else{
            $auth_flag = true;
        }

        if(!$auth_flag){
            $auth->requireLogin();
            die();
        }
    }
}