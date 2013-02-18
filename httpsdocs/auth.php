<?php
include 'init.php';
if ($_POST['s'] == L\Login) {
	$errors = Array();
	$u = strtolower(trim($_POST['u']));
	$p = $_POST['p'];
	if (empty($u)) $errors[] = L\Specify_username;
	if (empty($p)) $errors[] = L\Specify_password;

	if (empty($errors)) {
		require_once('lib/DB.php');
		connect2DB();
		require_once('remote/classes/Auth.php');
		$r = Auth::Login($u, $p);
		if($r['success'] == false) $errors[] = L\Auth_fail;
	}
	$_SESSION['message'] = array_shift($errors);
}
if (empty($_SESSION['user'])) exit(header('Location: /login.php'));
header('Location: /index.php');
?>