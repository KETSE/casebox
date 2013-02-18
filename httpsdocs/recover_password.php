<?php include 'init.php';
	$action = explode('/', @$_GET['f']);
	$action = array_shift($action);
	switch($action){
		case 'forgot-password':
			break;
		case 'reset-password':
			// if(is_debug_host()) {
			// 	var_dump($_GET);
			// 	die();
			// }
			$hash = '';
			if(!empty($_GET['h'])) $hash = $_GET['h'];
			if(!empty($_POST['h'])) $hash = $_POST['h'];
			if(!empty($hash)){
				//process hash from get and check it https://osji.casebox.org/login/reset-password/?h=a9199d0152081ca667f07fbfd684ad9a
				$user_id = null;
				$sql = 'select id from users where recover_hash = $1';
				$res = mysqli_query_params($sql, $hash) or die(mysqli_query_error());
				if($r = $res->fetch_row()) $user_id = $r[0];
				$res->close();
				if(empty($user_id)){
					$_SESSION['msg'] = L('RecoverHashNotFound').(is_debug_host() ? $hash: '');
					break;
				}
				
				if(isset($_POST['p']) && isset($_POST['p2'])){
					$p = $_POST['p'];
					$p2 = $_POST['p2'];
					if(empty($p) || ($p != $p2) ){
						$_SESSION['p_msg'] = L('PasswordMissmatch');
						break;
					}

					mysqli_query_params('update users set `password` = md5($2), recover_hash = null where recover_hash = $1', array($hash, 'aero'.$p)) or die(mysqli_query_error());
					$_SESSION['msg'] = L('PasswordChanged').'<br /> <br /><a href="/">'.L('Login').'</a>';
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

			if(!isset($_POST['s']) || (empty($e) && empty($u)) ){
				header('location: /login/forgot-password/');
				exit(0);
			}
			$user_id = null;
			$user_name = null;
			$user_mail = null;
			if(!empty($e)){
				if($e = filter_var($e, FILTER_VALIDATE_EMAIL)){ 
					$sql = 'select id, email, l'.UL_ID().' `name` from users where email like $1';
					$res = mysqli_query_params($sql, "%$e%") or die(mysqli_query_error());
					while( ($r = $res->fetch_row() ) && empty($user_id) ){
						$mails = explode(',', $r[1]);
						for ($i=0; $i < sizeof($mails); $i++) { 
							$mails[$i] = trim($mails[$i]);
							if(mb_strtolower($mails[$i]) == $e){
								$user_id = $r[0];
								$user_mail = $e;
								$user_name = $r[2];
							}
						}
					}
					$res->close();
					if(empty($user_id)){
						$_SESSION['e_msg'] = L('EmailNotFound');
						header('location: /login/forgot-password/');
						exit(0);
					}
				}else{
					$_SESSION['e_msg'] = L('InvalidEmail');
				}
			}elseif(!empty($u)){
				$user_id = null;
				$sql = 'select id, email, l'.UL_ID().' `name` from users where name = $1';
				$res = mysqli_query_params($sql, $u) or die(mysqli_query_error());
				if($r = $res->fetch_row()){
					$user_id = $r[0];
					$user_mail = $r[1];
					$user_name = $r[2];
				}
				$res->close();
				if(empty($user_id)){
					$_SESSION['u_msg'] = L('UsernameNotFound');
					header('location: /login/forgot-password/');
					exit(0);
				}elseif(empty($user_mail)){
					$_SESSION['u_msg'] = L('UserHasNoMail');
					header('location: /login/forgot-password/');
					exit(0);
				}
			}

			/* generating reset hash and sending mail */
			$template = CB_TEMPLATES_PATH.'password_recovery_email_'.UL().'.html';
			if(!file_exists($template)) $template = CB_TEMPLATES_PATH.'password_recovery_email_en.html';
			if(!file_exists($template)){
				mail(CB_ADMIN_EMAIL, 'Casebox template not found', $template, "Content-type: text/html; charset=utf-8\r\nFrom: noreply@casebox.org\r\n");
				$_SESSION['msg'] = 'Error occured. Administrator has been notified by mail. Please retry later.';
				header('location: /login/forgot-password/');
				exit(0);
			}
			$hash = md5($user_id.$user_mail.date(DATE_ISO8601));
			mysqli_query_params('update users set recover_hash = $2 where id = $1', array($user_id, $hash)) or die(mysqli_query_error());
			$href = getCoreHost().'login/reset-password/?h='.$hash;
			$mail = file_get_contents($template);
			$mail = str_replace(array('{name}', '{link}'), array($user_name, '<a href="'.$href.'" >'.$href.'</a>'), $mail);

			@mail($user_mail, L('MailRecoverSubject'), $mail, "Content-type: text/html; charset=utf-8\r\nFrom: noreply@casebox.org\r\n");			
			$_SESSION['msg'] = L('RecoverMessageSent');
			/* end of generating reset hash and sending mail */
			break;
		default: 
			header('location:/');
			exit(0);
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <title><?php echo CB_get_param('project_name_'.UL()) ?></title>
  <link rel="stylesheet" type="text/css" href="/css/bs/css/bootstrap.min.css" />
  <link rel="stylesheet" type="text/css" href="/css/bs/css/bootstrap-responsive.min.css" />
  <link type='text/css' rel="stylesheet" href="/css/login.css" />
</head>
<body onload="javascript: e = document.getElementById('e'); if(e) e.focus(); editChanged();">
<script type="text/javascript">
//<--
String.prototype.trim = function() {return this.replace(/^\s+|\s+$/g,"");}
function editChanged(){ 
	s = document.getElementById('s');
	if(!s) return;
	e = document.getElementById('e');
	u = document.getElementById('u');
	p = document.getElementById('p');
	p2 = document.getElementById('p2');
	if(e && u) s.disabled = ((e && (e.value.trim() == '') ) && (u && (u.value.trim() == '') ));
	if(p && p2) s.disabled = ((p.value.trim() == '') || (p.value != p2.value));
	setTimeout(editChanged, 500)
}
//-->
</script>
<div class="main">
	<div class="form_login tac">
            	<a href="/" class="dib"><img src="/css/i/CaseBox-Logo-medium.png" style="width: 300px"></a><br>
	    	<form method="post" action="/login/reset-password/" class="standart_form tal" autocomplete="off">
	    	<?php
	    		if(!empty($_SESSION['msg'])){
	    			echo '<div class="alert alert-error">'.$_SESSION['msg'].'</div>';
	    			unset($_SESSION['msg']);
	    		}elseif($prompt_for_new_password){
	    			echo '<input type="hidden" name="h" value="'.$hash.'" />';
	    	?>
                        <label>
                            <input type="password" name="p" id="p" placeholder="<?php echo L('NewPassword');?>" onkeydown="javascript:editChanged()">
                            <span class="icon-lock"></span>
                        </label>
                        <label>
                            <input type="password" name="p2" id="p2" placeholder="<?php echo L('ConfirmPassword');?>" onkeydown="javascript:editChanged()">
                            <span class="icon-lock"></span>
                            <?php if(!empty($_SESSION['p_msg'])) echo '<div class="alert alert-error">'.$_SESSION['p_msg'].'</div>'; unset($_SESSION['p_msg']); ?>
                        </label>
                        <input type="submit" name="s" id="s" value="<?php echo L('Continue');?>" class="btn btn-info" style="margin-top: 26px;" disabled>
	    	<?php
	    		}else{
	    	?>
                        <?php echo '<p>'.L('EnterEmail').'<p>';?>
                        <label>
                            <input type="email" name="e" id="e" placeholder="<?php echo L('Email');?>">
                            <span class="icon-envelope"></span>
                            <?php if(!empty($_SESSION['e_msg'])) echo '<div class="alert alert-error">'.$_SESSION['e_msg'].'</div>'; unset($_SESSION['e_msg']); ?>
                        </label>
                        <?php echo '<p>'.L('OR').'<p>';?>
                        <?php echo '<p>'.L('Specify_username').':<p>';?>
                        <label>
                            <input type="text" name="u" id="u" placeholder="<?php echo L('Username');?>">
                            <span class="icon-user"></span>
                            <?php if(!empty($_SESSION['u_msg'])) echo '<div class="alert alert-error">'.$_SESSION['u_msg'].'</div>'; unset($_SESSION['u_msg']); ?>
                        </label>
                        <input type="submit" name="s" id="s" value="<?php echo L('Continue');?>" class="btn btn-info" style="margin-top: 26px;" disabled>
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
?>