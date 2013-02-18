<?php include 'init.php';?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <title><?php echo CB_get_param('project_name_'.UL()) ?></title>
  <link rel="stylesheet" type="text/css" href="/css/bs/css/bootstrap.min.css" />
  <link rel="stylesheet" type="text/css" href="/css/bs/css/bootstrap-responsive.min.css" />
  <link type='text/css' rel="stylesheet" href="/css/login.css" />
</head>
<body onload="javascript: document.getElementById('u').focus();editChanged();">
<script type="text/javascript">
//<--
String.prototype.trim = function() {return this.replace(/^\s+|\s+$/g,"");}
function editChanged(){ 
	s = document.getElementById('s');
	s.disabled = ((document.getElementById('u').value.trim() == '') || (document.getElementById('p').value == ''));
	if(s.disabled) setTimeout(editChanged, 500)
}
//-->
</script>
<div class="main">
    <div class="form_login tac">
                    <a href="/" class="dib"><img src="/css/i/CaseBox-Logo-medium.png" style="width: 300px"></a><br>
                    <form method="post" action="auth.php" class="standart_form tal" autocomplete="off">
                        <label>
                            <input type="text" name="u" id="u" placeholder="<?php echo L('Username');?>">
                            <!-- <div class="alert alert-error">username is required</div> -->
                            <span class="icon-user"></span>
                        </label>
                        <label>
                            <input type="password" name="p" id="p" placeholder="<?php echo L('Password');?>" onkeydown="javascript:editChanged()">
                            <?php echo isset($_SESSION['message']) ? '<div class="alert alert-error">'.$_SESSION['message'].'</div>' : '';?>
                            <span class="icon-lock"></span>
                        </label>
                        <a style="margin-top: 30px;" class="pull-right" href="/login/forgot-password/"><?php echo L('ForgotPassword');?></a>
                        <input type="submit" name="s" id="s" value="<?php echo L('Login');?>" class="btn btn-info" style="margin-top: 26px;" disabled>
                    </form>
    </div>
</div>    
</body>
</html>
<?php
	unset($_SESSION['message']);
	unset($_SESSION['user']);
?>