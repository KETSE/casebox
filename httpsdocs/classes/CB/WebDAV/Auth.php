<?php
namespace CB\WebDAV;


class Auth extends \Sabre\DAV\Auth\Backend\AbstractBasic {

    protected function validateUserPass($username, $password) {

        $auth_flag = false;

        // error_log('webDAV/Auth: validateUserPass');

        $user = new \CB\User();
        if (!$user->isLoged()) {
            $r = $user->Login(strtolower(trim($username)), $password);
            if ($r['success'] == true) {
                    $auth_flag = true;
                    $_SESSION['user']['TSV_checked'] = true;
            }
        } else {
            $auth_flag = true;
            $_SESSION['user']['TSV_checked'] = true;
        }

        return $auth_flag;
    }

}