<?php
/**
 * User authentification script.
 * 
 * This script does first checks on submited values from login.php.
 * Uses the User class and calls Login method with passed params to check authentification validity.
 * If the user passes the authentification he's redirected to the index.php where the CaseBox interface starts loading.
 * Otherwise, if the user do not pass authentification, it is redirected to login.php and the corresponding message is displayed (from $_SESSION['message']).
 * 
 * @package CaseBox
 * 
 * */

namespace CB;

include 'init.php';

if( !empty($_POST['s']) && !empty($_POST['p']) && !empty($_POST['u']) ){
	$errors = Array();
	$u = strtolower(trim($_POST['u']));
	$p = $_POST['p'];
	if (empty($u)) $errors[] = L\Specify_username;
	if (empty($p)) $errors[] = L\Specify_password;

	if (empty($errors)) {
		DB\connect();
		$r = User::Login($u, $p);
		if($r['success'] == false) $errors[] = L\Auth_fail;
	}
	$_SESSION['message'] = array_shift($errors);
}

if (empty($_SESSION['user'])) exit(header('Location: /login.php'));

header('Location: /index.php');
