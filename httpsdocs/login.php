<?php

namespace CB;

require_once 'init.php';

if (!empty($_SESSION['check_TSV']) && ((time() - $_SESSION['check_TSV']) > 180)) {
    unset($_SESSION['check_TSV']);
}

$coreName = Config::get('core_name');
$coreDir  = Config::get('core_dir');
$coreDirUri = '/cores' . Config::get('core_uri');
$coreUrl  = Config::get('core_url');

/* check if set an object id for view in url and store it in session for redirect after success login */
if (!empty($_GET['view']) && is_numeric($_GET['view'])) {
    $_SESSION['redirect'] = array('view' => $_GET['view']);
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8">
        <title><?php
            echo Config::getProjectName();
            ?></title>
        <link rel="stylesheet" type="text/css" href="/css/bs/css/bootstrap.min.css" />
        <link rel="stylesheet" type="text/css" href="/css/bs/css/bootstrap-responsive.min.css" />
        <link type='text/css' rel="stylesheet" href="/css/login.css" />
        <?php
        if (file_exists($coreDir . 'favicon.ico')) {
            echo '<link rel="shortcut icon" href="' . $coreDirUri  . 'favicon.ico" type="image/x-icon">';
        } else {
            echo '<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">';
        }
        ?>
    </head>
    <body onload="javascript: e = document.getElementById('u');
            if (!e)
                e = document.getElementById('c');
            e.focus();
            editChanged();">
        <script type="text/javascript">
            //<--
            String.prototype.trim = function () {
                return this.replace(/^\s+|\s+$/g, "");
            }
            function editChanged()
            {
                s = document.getElementById('s');
<?php
if (empty($_SESSION['check_TSV'])) {
    ?>

                    s.disabled = ((document.getElementById('u').value.trim() == '') || (document.getElementById('p').value == ''));

    <?php
} else {
    ?>

                    s.disabled = (document.getElementById('c').value.trim() == '');

    <?php
}
?>
                if (s.disabled)
                    setTimeout(editChanged, 500)
            }
            //-->
        </script>
        <div class="main">
            <div class="form_login tac">
                <a href="/" class="dib"><img src="/css/i/CaseBox-Logo-medium.png" style="width: 300px"></a><br>
                <form method="post" action="<?php echo '/'.$coreName; ?>/login/auth/" class="standart_form tal" autocomplete="off">
                    <?php
                    if (empty($_SESSION['check_TSV'])) {
                        ?>
                        <label>
                            <input type="text" name="u" id="u" placeholder="<?php echo L\get('Username'); ?>">
                            <span class="icon-user"></span>
                        </label>
                        <label>
                            <input type="password" name="p" id="p" placeholder="<?php echo L\get('Password'); ?>" onkeydown="javascript:editChanged()">
                            <?php echo isset($_SESSION['message']) ? '<div class="alert alert-error">'.$_SESSION['message'].'</div>'
                                    : ''; ?>
                            <span class="icon-lock"></span>
                        </label>
                        <a style="margin-top: 30px;" class="pull-right" href="<?php echo '/'.$coreName; ?>/recover/forgot-password/"><?php echo L\get('ForgotPassword'); ?></a>
                        <?php

                            $Oauth2Providers = Config::get('oauth2_providers', null);
                            if (isset($Oauth2Providers) && in_array('google', explode(',', $Oauth2Providers))) {
                                $googleProvider = Oauth2Utils::getGoogleProvider();
                                $googleAuthUrl = Oauth2Utils::getLoginUrl($googleProvider);
                                if (isset($googleAuthUrl)) {
                                    echo "<a style='margin-top:25px;margin-right:25px;' class='pull-right' href='" . $googleAuthUrl . "'><img src='/css/i/gplus_signin_button.png' style='height:35px' /></a>";
                                }
                            }

                            ?>
                        <input type="submit" name="s" id="s" value="<?php echo L\get('Login'); ?>" class="btn btn-info" style="margin-top: 26px;" disabled>
                        <?php
                    } else {
                        ?>
                        <label>
                        <?php echo L\get('TSV');
                        ?>
                        </label>
                        <label>
                            <input type="text" name="c" id="c" placeholder="<?php echo L\get('EnterCode'); ?>">
    <?php echo isset($_SESSION['message']) ? '<div class="alert alert-error">'.$_SESSION['message'].'</div>'
            : ''; ?>
                            <span class="icon-lock"></span>
                        </label>
    <?php
    //dont show login button for yubikey authentication
    $cfg = User::getTSVConfig();
    if ($cfg['method'] !== 'ybk') {
        echo '<input type="submit" name="s" id="s" value="'.L\get('Verify').'" class="btn btn-info" style="margin-top: 26px;" disabled>';
    }
    echo '<a href="/'.$coreName.'/login/auth/?l=1" style="margin-top: 30px;"  class="pull-right">'.L\get('Exit').'</a>';
}
?>
                </form>
            </div>
        </div>

        <div class="footer">
<?php echo Config::get('login_footer_text'); ?>
        </div>

        <div class="footer" style="right: 0;">
            <a href="https://www.casebox.org/">www.casebox.org</a> <span style="color: #AAA; padding-left: 2px; padding-right: 5px">&bull;</span>  <a href="http://forum.casebox.org/">Support forum</a>
        </div>
    </body>
</html>
<?php
unset($_SESSION['message']);

if (empty($_SESSION['check_TSV'])) {
    unset($_SESSION['user']);
}
