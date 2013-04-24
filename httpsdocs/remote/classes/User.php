<?php

namespace CB;

class User{
	public static function login($login, $pass){
		$ips = '|'.Util\getIPs().'|';

		session_regenerate_id(false);
		$_SESSION['ips'] = $ips;
		$_SESSION['key'] = md5($ips.$login.$pass.time());
		setcookie('key', $_SESSION['key'], 0, '/', $_SERVER['SERVER_NAME'], !empty($_SERVER['HTTPS']), true);
		
		$rez = Array('success' => false, 'msg' => L\Auth_fail);
		$user_id = false;
		
		/* try to authentificate */
		$res = DB\mysqli_query_params('CALL p_user_login($1, $2, $3)', array($login, $pass, $ips)) or die( DB\mysqli_query_error() );
		if (($r = $res->fetch_row()) && ($r[1] == 1))  $user_id = $r[0];
		$res->close();
		DB\mysqli_clean_connection();

		if($user_id){
			$rez = Array('success' => true, 'user' => array());
			
			$sql = 'SELECT u.id, u.tag_id, u.`language_id`, '.config\language_fields.', short_date_format, long_date_format, sex, cfg FROM users_groups u WHERE u.id = $1';
			$res = DB\mysqli_query_params($sql, $user_id) or die( DB\mysqli_query_error() );
			if ($r = $res->fetch_assoc()) {
				$r['admin'] = Security::isAdmin($user_id);
				$r['manage'] = Security::canManage($user_id);
				// $r['role'] = Security::getUserRole($user_id); //TODO: rethink roles mechanism and replace it with groups and accesses
				
				$r['language'] = $GLOBALS['languages'][$r['language_id']-1];
				$r['locale'] = 	$GLOBALS['language_settings'][$r['language']]['locale'];
				if(empty($r['long_date_format'])) $r['long_date_format'] = $GLOBALS['language_settings'][$r['language']]['long_date_format'];
				if(empty($r['short_date_format']))$r['short_date_format'] = $GLOBALS['language_settings'][$r['language']]['short_date_format'];
				$r['time_format'] = $GLOBALS['language_settings'][$r['language']]['time_format'];
				
				$r['cfg'] = empty($r['cfg']) ? array() : (array)json_decode($r['cfg']);

				$rez['user'] = $r;
				$_SESSION['user'] = $r;
				setcookie('L', $r['language']);
			}
			$res->close();

			User::checkUserFolders();
		}
		Log::add(Array('action_type' => 1, 'result' => isset($_SESSION['user']), 'info' => 'user: '.$login."\nip: ".$ips));
		return $rez;
	}
	

	public static function is_loged(){
		return ( !empty($_COOKIE['key']) && 
			!empty($_SESSION['key']) && 
			!empty($_SESSION['ips']) && 
			!empty($_SESSION['user']) &&  
			($_COOKIE['key'] == $_SESSION['key']) && 
			('|'.Util\getIPs().'|' == $_SESSION['ips']) 
			);
	}

	public function getLoginInfo() {
		$rez = array(
			'success' => true
			,'config' => array(
				'task_categories' => defined('CB\\config\\task_categories') ? config\task_categories: null
				,'responsible_party' => defined('CB\\config\\responsible_party') ? config\responsible_party: null
			)
			,'user' => $_SESSION['user']
		);
		$rez['user']['short_date_format'] = str_replace('%', '', $rez['user']['short_date_format']);
		$rez['user']['long_date_format'] = str_replace('%', '', $rez['user']['long_date_format']);
		return $rez;
	}

	public function logout() {
		$rez = Array('success' => true);
		Log::add(Array('action_type' => 2, 'result' => 1));
		
		while(!empty($_SESSION['last_sessions'])) @unlink(session_save_path().DIRECTORY_SEPARATOR.'sess_'.array_shift($_SESSION['last_sessions']));
		session_destroy();
		return $rez;
	}

	public function setLanguage($id) {
		if(isset($GLOBALS['languages'][$id -1])) {
			$_SESSION['user']['language_id'] = $id;
			$_SESSION['user']['language'] = $GLOBALS['languages'][$id -1];
			setcookie('L', $GLOBALS['languages'][$id -1]);
		} else return array('success' => false);
		DB\mysqli_query_params('update users_groups set language_id = $2 where id = $1', array($_SESSION['user']['id'], $id)) or die( DB\mysqli_query_error() );
		return Array('success' => true);
	}

	public static function checkUserFolders($user_id = false){
		$result = true;
		if(!is_numeric($user_id)) $user_id = $_SESSION['user']['id']; 

		$affected_rows = 0;
		
		/* check user home folder existace */
		$home_folder_id = null;
		$res = DB\mysqli_query_params('select id from tree where ( user_id = $1 )  and (`system` = 1) and (`type` = 1) and (`subtype` = 2) and (pid is null)', $user_id) or die( DB\mysqli_query_error() );
		if($r = $res->fetch_row()) $home_folder_id = $r[0]; 
		$res->close();
		if(is_null($home_folder_id)){
			$cfg = defined('CB\\config\\default_home_folder_cfg') ? config\default_home_folder_cfg : null;

			DB\mysqli_query_params('insert into tree (name, user_id, `system`, `type`, `subtype`, cfg) values(\'[Home]\', $1, 1, 1, 2, $2)', array($user_id, $cfg) ) or die( DB\mysqli_query_error() );
			$home_folder_id = DB\last_insert_id();
			$affected_rows++;
		}

		/* check users "My documents" folder existace */
		$my_docs_id = null;
		$res = DB\mysqli_query_params('select id from tree where ( user_id = $1 )  and (`system` = 1) and (`type` = 1) and (`subtype` = 3) and (pid = $2)', array($user_id, $home_folder_id) ) or die( DB\mysqli_query_error() );
		if($r = $res->fetch_row()) $my_docs_id = $r[0]; 
		$res->close();
		if(is_null($my_docs_id)){
			DB\mysqli_query_params('insert into tree (pid, name, user_id, `system`, `type`, `subtype`) values($1, \'[MyDocuments]\', $2, 1, 1, 3)', array($home_folder_id, $user_id)) or die( DB\mysqli_query_error() );
			$my_docs_id = DB\last_insert_id();
			$affected_rows++;
		}

		/* insert home folder security record in tree_acl */
		DB\mysqli_query_params('insert into tree_acl (node_id, user_group_id, allow, deny) values ($1, $2, 4095, 0) on duplicate key update allow = 4095, deny = 0', array($home_folder_id, $user_id)) or die( DB\mysqli_query_error() );
		$affected_rows += DB\affected_rows();
		
		if($affected_rows > 0) SolrClient::runCron();
		return true;
	}
	/**
	 * [checkUserRootFolders checks if specified user (or current) has crated root folders in the tree. If any required folder is missing then it will be created ]
	 * @param  array  $data    already retreived data array of root nodes
	 * @param  int 	  $user_id if not specified then the current logged user id is used
	 * @return boolean         returns true if required folders exists, false - if some folders have been missing and were crated
	 */
	public static function checkUserRootFolders($data = array(), $user_id = false){
		$result = true;
		if(!is_numeric($user_id)) $user_id = $_SESSION['user']['id']; 

		if(empty($data) || (empty($data[0]['subtype']))){
			$data = array();
			$res = DB\mysqli_query_params('select id, `subtype` from tree where ((user_id = $1) or (user_id is null)) and (`system` = 1) and (`type` = 1) and (pid is null)', $user_id) or die( DB\mysqli_query_error() );
			while($r = $res->fetch_assoc()) $data[] = $r; 
			$res->close();
		}

		$existing_folder_types = array();
		foreach($data as $r) if(!empty($r['subtype'])) $existing_folder_types[$r['subtype']] =1;

		return $result;
	}
	
	public function getMainMenuItems(){
		$userMenu = new UserMenu();
		$rez = array(
			'success' => true
			,'items' => $userMenu->getAccordionItems()
			,'tbarItems' => $userMenu->getToolbarItems()
		);
		
		return $rez;
	}
	
	public static function getUserHomeFolderId($user_id = false){
		$rez = null;
		if($user_id == false) $user_id = $_SESSION['user']['id'];
		$res = DB\mysqli_query_params('select id from tree where user_id = $1 and system = 1 and (pid is null) and type = 1 and subtype = 2', $_SESSION['user']['id']) or die( DB\mysqli_query_error() );
		if($r = $res->fetch_row()) $rez = $r[0];
		$res->close();
		if(empty($rez)){
			DB\mysqli_query_params('insert into tree (user_id, `system`, `type`, `subtype`, `name`, cid) values ($1, 1, 1, 2, \'[Home]\', $2)', array($user_id, $_SESSION['user']['id']) ) or die( DB\mysqli_query_error() );
			$rez = DB\last_insert_id();
			SolrClient::runCron();
		}
		return $rez;
	}
	
	public static function getEmailFolderId($user_id = false){
		$rez = null;
		if(empty($user_id)) $user_id = $_SESSION['user']['id'];
		$pid = User::getUserHomeFolderId($user_id);

		$res = DB\mysqli_query_params('select id from tree where user_id = $1 and system = 1 and pid =$2 and type = 1 and subtype = 6', array($_SESSION['user']['id'], $pid) ) or die( DB\mysqli_query_error() );
		if($r = $res->fetch_row()) $rez = $r[0];
		$res->close();
		if(empty($rez)){
			DB\mysqli_query_params('insert into tree (pid, user_id, `system`, `type`, `subtype`, `name`, cid, uid) values ($1, $2, 1, 1, 6, \'[Emails]\', $3, $3)', array($pid, $user_id, $_SESSION['user']['id']) ) or die( DB\mysqli_query_error() );
			$rez = DB\last_insert_id();
			SolrClient::runCron();
		}
		return $rez;
	}
	public function uploadPhoto($p){
		if(!is_numeric($p['id'])) return Array('success' => false, 'msg' => L\Wrong_id);
		$f = &$_FILES['photo'];
		if (!in_array($f['error'], Array(UPLOAD_ERR_OK, UPLOAD_ERR_NO_FILE))) return Array('success' => false, 'msg' => L\Error_uploading_file .': '.$f['error']);
		if (substr($f['type'], 0, 6) !== 'image/') return Array('success' => false, 'msg' => 'Not an image');

		$photoName = $p['id'].'_'.$object_title = preg_replace('/[^a-z0-9\.]/i', '_', $f['name']);
		move_uploaded_file($f['tmp_name'], PHOTOS_PATH.$photoName);
		$res = DB\mysqli_query_params('update users_groups set photo = $2 where id = $1', array($p['id'], $photoName)) or die( DB\mysqli_query_error() );		
		return array('success' => true, 'photo' => $photoName);
	}
}

