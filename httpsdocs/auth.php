<?php
/**
 * User authentification script.
 *
 * This script does first checks on submited values from login.php.
 * Uses the User class and calls Login method with passed params to check authentification validity.
 * If the user passes the authentification he's redirected to the index.php
 * where the CaseBox interface starts loading.
 * Otherwise, if the user do not pass authentification, it is redirected to login.php
 * and the corresponding message is displayed (from $_SESSION['message']).
 *
 * @package CaseBox
 *
 * */
namespace CB;

require_once 'init.php';

if (Oauth2Utils::isOauth2Login()) {

    $Check = Oauth2Utils::checkLogined();
    if ($Check['success']) {
        
         $r = User::setAsLoged($Check['user_id'], $Check['session_id']);

                    if ($r['success'] == false) {
                        $errors[] = L\get('Auth_fail');
                    } else {
                        $cfg = User::getTSVConfig();
                        if (!empty($cfg['method'])) {
                            $_SESSION['check_TSV'] = time();
                            $_SESSION['user']['TSV_checked'] = false;
                        } else {
                            $_SESSION['user']['TSV_checked'] = true;
                        }
                    }
       // header('Location: '.Config::get('core_url'));
    } else {
        $errors[] = $Check['message'];
        $_SESSION['message'] = array_shift($errors);
    }
}

//reset if sign out clicked on check tsv
if (!empty($_GET['l'])) {
    unset($_SESSION['check_TSV']);
}

if (!empty($_POST['s']) && !empty($_POST['p']) && !empty($_POST['u'])) {
    $errors = array();
    $u      = strtolower(trim($_POST['u']));
    $p      = $_POST['p'];
    if (empty($u)) {
        $errors[] = L\get('Specify_username');
    }
    if (empty($p)) {
        $errors[] = L\get('Specify_password');
    }

    if (empty($errors)) {
        DB\connect();
        $user = new User();
        $r    = $user->Login($u, $p);

        if ($r['success'] == false) {
            $errors[] = L\get('Auth_fail');
        } else {
            $cfg = $user->getTSVConfig();
            if (!empty($cfg['method'])) {
                $_SESSION['check_TSV'] = time();
                $_SESSION['user']['TSV_checked'] = false;
            } else {
                $_SESSION['user']['TSV_checked'] = true;
            }
        }
    }
    $_SESSION['message'] = array_shift($errors);
} elseif (!empty($_SESSION['check_TSV']) && !empty($_POST['c'])) {
    $u                  = new User();
    $cfg                = $u->getTSVConfig();
    $authenticator      = $u->getTSVAuthenticator($cfg['method'], $cfg['sd']);
    $verificationResult = $authenticator->verifyCode($_POST['c']);

    if ($verificationResult === true) {
        unset($_SESSION['check_TSV']);
        $_SESSION['user']['TSV_checked'] = true;
    } else {
        $_SESSION['message'] = is_string($verificationResult) ? htmlspecialchars($verificationResult, ENT_COMPAT) : 'Wrong verification code. Please try again.';
    }
}

$coreUrl = Config::get('core_url');

if (!User::isLoged()) {
    exit(header('Location: '.$coreUrl.'login/'));
}

if (!empty($_SESSION['redirect']['view'])) {
    $viewId = $_SESSION['redirect']['view'];
    unset($_SESSION['redirect']['view']);
    header('Location: '.$coreUrl.'view/'.$viewId.'/');
} else {
    header('Location: '.$coreUrl);
}
