<?php
namespace CB;

use \CB\User;

require_once 'init.php';

$action = @$_GET['subcommand'];

$prompt_for_new_password = false;

$coreUrl = Config::get('core_url');

switch ($action) {
    case 'forgot-password':
        break;

    case 'reset-password':
        //check if recover hash is given
        $hash = '';
        if (!empty($_GET['h'])) {
            $hash = $_GET['h'];
        }
        if (!empty($_POST['h'])) {
            $hash = $_POST['h'];
        }
        if (!empty($hash)) {
            //process hash from get and check it
            $user_id = User::getIdByRecoveryHash($hash);

            if (empty($user_id)) {
                $_SESSION['msg'] = '<div class="alert alert-error">'.L\get('RecoverHashNotFound').(IS_DEBUG_HOST ? $hash: '').'</div>';
                break;
            }

            //if recovery hash check passed - check and set new password if specified
            if (isset($_POST['p']) && isset($_POST['p2'])) {
                $p = $_POST['p'];
                $p2 = $_POST['p2'];
                if (empty($p) || ($p != $p2)) {
                    $_SESSION['p_msg'] = L\get('PasswordMissmatch');
                    break;
                }
                User::setNewPasswordByRecoveryHash($hash, $p);

                $_SESSION['msg'] = '<div class="alert alert-success">'.L\get('PasswordChangedMsg').
                    '<br /> <br /><a href="' . $coreUrl . '">'.L\get('Login').'</a></div>';
                break;
            }

            $prompt_for_new_password = true;
            break;
        }

        $e = @$_POST['e'];
        $u = @$_POST['u'];
        $e = trim($e);
        $u = trim($u);
        $e = mb_strtolower($e);
        $u = mb_strtolower($u);

        //redirect to recovery form if not submited or empty user and email
        if (!isset($_POST['s']) || (empty($e) && empty($u))) {
            header('location: ' . $coreUrl . 'recover/forgot-password/');
            exit(0);
        }

        $user_id = null;
        $user_mail = null;
        if (!empty($e)) {
            if ($e = filter_var($e, FILTER_VALIDATE_EMAIL)) {
                $user_id = User::getIdByEmail($e);

                if (empty($user_id)) {
                    $_SESSION['e_msg'] = L\get('EmailNotFound');
                    header('location: ' . $coreUrl . 'recover/forgot-password/');
                    exit(0);
                }
            } else {
                $_SESSION['e_msg'] = L\get('InvalidEmail');
            }
        } elseif (!empty($u)) {
            $user_id = User::getIdByUsername($u);

            if (empty($user_id)) {
                $_SESSION['u_msg'] = L\get('UsernameNotFound');
                header('location: ' . $coreUrl . 'recover/forgot-password/');
                exit(0);
            } else {
                $user = User::getPreferences($user_id);

                $user_mail = empty($user['cfg']['security']['recovery_email'])
                    ? $user['email']
                    : $user['cfg']['security']['recovery_email'];

                if (empty($user_mail)) {
                    $_SESSION['u_msg'] = L\get('UserHasNoMail');
                    header('location: ' . $coreUrl . 'recover/forgot-password/');
                    exit(0);
                }
            }
        }

        if (!UsersGroups::sendEmailInvite($user_id)) {
            $_SESSION['msg'] = '<div class="alert alert-error">Error occured. Administrator has been notified by mail. Please retry later.</div>';
            header('location: ' . $coreUrl . 'recover/forgot-password/');
            exit(0);
        }

        $_SESSION['msg'] = '<div class="alert alert-success">'.L\get('RecoverMessageSent').'</div>';
        break;

    default:
        header('location: ' . $coreUrl);
        exit(0);
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<title><?php echo Config::getProjectName() ?></title>
<?php
echo '
<link rel="stylesheet" type="text/css" href="/css/bs/css/bootstrap.min.css" />
<link rel="stylesheet" type="text/css" href="/css/bs/css/bootstrap-responsive.min.css" />
<link type="text/css" rel="stylesheet" href="/css/login.css" />';
?>
</head>
<body onload="javascript: e = document.getElementById('e'); if(e) e.focus(); editChanged();">
<script type="text/javascript">
//<--
String.prototype.trim = function() {return this.replace(/^\s+|\s+$/g,"");}
function editChanged()
{
    var s = document.getElementById('s');
    if (!s) {
        return;
    }
    var e = document.getElementById('e')
        ,u = document.getElementById('u')
        ,p = document.getElementById('p')
        ,p2 = document.getElementById('p2');
    if (e && u) {
        s.disabled = ((e && (e.value.trim() == '') ) && (u && (u.value.trim() == '') ));
    }
    if (p && p2) {
        s.disabled = ((p.value.trim() == '') || (p.value != p2.value));
    }
    setTimeout(editChanged, 500)
}
//-->
</script>
<div class="main">
<div class="form_login tac">
        <?php
        echo '<a href="' . $coreUrl . '" class="dib"><img src="' . $coreUrl . '/logo.png" style="width: 300px"></a><br>
        <form method="post" action="' . $coreUrl . 'recover/reset-password/" class="standart_form tal" autocomplete="off">';
        $homeLink = '<a href="' . $coreUrl . '" class="btn btn-info">'.L\get('Login').'</a>';

        if (!empty($_SESSION['msg'])) {
            echo $_SESSION['msg'].$homeLink;
            unset($_SESSION['msg']);
        } elseif ($prompt_for_new_password) {
            echo '<input type="hidden" name="h" value="'.$hash.'" />';

            ?>
                    <label>
                        <input type="password" name="p" id="p" placeholder="<?php echo L\get('NewPassword');?>" onkeydown="javascript:editChanged()">
                        <span class="icon-lock"></span>
                    </label>
                    <label>
                        <input type="password" name="p2" id="p2" placeholder="<?php echo L\get('ConfirmPassword');?>" onkeydown="javascript:editChanged()">
                        <span class="icon-lock"></span>
    <?php

    if (!empty($_SESSION['p_msg'])) {
        echo '<div class="alert alert-error">'.$_SESSION['p_msg'].'</div>';
        unset($_SESSION['p_msg']);
    }

                        ?>
                    </label>
                    <input type="submit" name="s" id="s" value="<?php echo L\get('Continue');?>" class="btn btn-info" style="margin-top: 26px;" disabled>
        <?php

        } else {

            ?>
                    <?php echo '<p>'.L\get('EnterEmail').'<p>';?>
                    <label>
                        <input type="email" name="e" id="e" placeholder="<?php echo L\get('Email');?>">
                        <span class="icon-envelope"></span>
    <?php

    if (!empty($_SESSION['e_msg'])) {
        echo '<div class="alert alert-error">'.$_SESSION['e_msg'].'</div>';
    }
            unset($_SESSION['e_msg']);

            ?>
                    </label>
                    <?php echo '<p>'.L\get('OR').'<p>';?>
                    <?php echo '<p>'.L\get('Specify_username').':<p>';?>
                    <label>
                        <input type="text" name="u" id="u" placeholder="<?php echo L\get('Username');?>">
                        <span class="icon-user"></span>
    <?php

    if (!empty($_SESSION['u_msg'])) {
        echo '<div class="alert alert-error">'.$_SESSION['u_msg'].'</div>';
    }

            unset($_SESSION['u_msg']);

            ?>
                    </label>
                    <input type="submit" name="s" id="s" value="<?php echo L\get('Continue');?>" class="btn btn-info" style="margin-top: 26px;" disabled>
        <?php
        }
        ?>
            </form>
    </div>
</div>
</body>
</html>
<?php

unset($_SESSION['message']);

unset($_SESSION['user']);
