<?php
class User{

	public static function checkUserFolders($user_id = false){
		$result = true;
		if(!is_numeric($user_id)) $user_id = $_SESSION['user']['id']; 

		$affected_rows = 0;
		
		/* check user home folder existace */
		$home_folder_id = null;
		$res = mysqli_query_params('select id from tree where ( user_id = $1 )  and (`system` = 1) and (`type` = 1) and (`subtype` = 2) and (pid is null)', $user_id) or die(mysqli_query_error());
		if($r = $res->fetch_row()) $home_folder_id = $r[0]; 
		$res->close();
		if(is_null($home_folder_id)){
			$cfg = CB_get_param('default_home_folder_cfg');

			mysqli_query_params('insert into tree (name, user_id, `system`, `type`, `subtype`, cfg) values(\'[Home]\', $1, 1, 1, 2, $2)', array($user_id, $cfg) ) or die(mysqli_query_error());
			$home_folder_id = last_insert_id();
			$affected_rows++;
		}

		/* check users "My documents" folder existace */
		$my_docs_id = null;
		$res = mysqli_query_params('select id from tree where ( user_id = $1 )  and (`system` = 1) and (`type` = 1) and (`subtype` = 3) and (pid = $2)', array($user_id, $home_folder_id) ) or die(mysqli_query_error());
		if($r = $res->fetch_row()) $my_docs_id = $r[0]; 
		$res->close();
		if(is_null($my_docs_id)){
			mysqli_query_params('insert into tree (pid, name, user_id, `system`, `type`, `subtype`) values($1, \'[MyDocuments]\', $2, 1, 1, 3)', array($home_folder_id, $user_id)) or die(mysqli_query_error());
			$my_docs_id = last_insert_id();
			$affected_rows++;
		}

		/* insert home folder security record in tree_acl */
		mysqli_query_params('insert into tree_acl (node_id, user_group_id, allow, deny) values ($1, $2, 4095, 0) on duplicate key update allow = 4095, deny = 0', array($home_folder_id, $user_id)) or die(mysqli_query_error());
		$affected_rows += affected_rows();
		
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
			$res = mysqli_query_params('select id, `subtype` from tree where ((user_id = $1) or (user_id is null)) and (`system` = 1) and (`type` = 1) and (pid is null)', $user_id) or die(mysqli_query_error());
			while($r = $res->fetch_assoc()) $data[] = $r; 
			$res->close();
		}

		$existing_folder_types = array();
		foreach($data as $r) if(!empty($r['subtype'])) $existing_folder_types[$r['subtype']] =1;

		// if(empty($existing_folder_types[7])){
		// 	/* Favorites folder does not exist, creating it and default subchilds */
		// 	mysqli_query_params('insert into tree (`user_id`, `system`, `type`, `subtype`, `name`) values ($1, 1, 1, 7, \'[RecycleBin]\')', array($user_id)) or die(mysqli_query_error());
		// 	$recyclebin_id = last_insert_id();
		// 	$result = false;
		// }

		// if(empty($existing_folder_types[2])){
		// 	/* Favorites folder does not exist, creating it and default subchilds */
		// 	mysqli_query_params('insert into tree (`user_id`, `system`, `type`, `subtype`, `name`) values ($1, 1, 1, 2, \'[Favorites]\')', array($user_id)) or die(mysqli_query_error());
		// 	$favorites_id = last_insert_id();
		// 	mysqli_query_params('insert into tree (`pid`, `user_id`, `system`, `type`, `subtype`, `name`) values ($1, $2, 1, 1, 1, \'[Recent]\')', array($favorites_id, $user_id)) or die(mysqli_query_error());
		// 	$result = false;
		// }

		// if(empty($existing_folder_types[3])){
		// 	/* My Casebox folder does not exist, creating it and default subchilds */
		// 	mysqli_query_params('insert into tree (`user_id`, `system`, `type`, `subtype`, `name`) values ($1, 1, 1, 3, \'[MyCaseBox]\')', array($user_id)) or die(mysqli_query_error());
		// 	$my_casebox_id = last_insert_id();
		// 	mysqli_query_params('insert into tree (`pid`, `user_id`, `system`, `type`, `subtype`, `name`) values ($1, $2, 1, 1, 4, \'[Cases]\')', array($my_casebox_id, $user_id)) or die(mysqli_query_error());
			
		// 	mysqli_query_params('insert into tree (`pid`, `user_id`, `system`, `type`, `subtype`, `name`) values ($1, $2, 1, 1, 5, \'[Tasks]\')', array($my_casebox_id, $user_id)) or die(mysqli_query_error());
		// 	$tasks_id = last_insert_id();
		// 	mysqli_query_params('insert into tree (`pid`, `user_id`, `system`, `type`, `subtype`, `name`) values ($1, $2, 1, 1, 1, \'[Upcoming]\')', array($tasks_id, $user_id)) or die(mysqli_query_error());
		// 	mysqli_query_params('insert into tree (`pid`, `user_id`, `system`, `type`, `subtype`, `name`) values ($1, $2, 1, 1, 1, \'[Missed]\')', array($tasks_id, $user_id)) or die(mysqli_query_error());
		// 	mysqli_query_params('insert into tree (`pid`, `user_id`, `system`, `type`, `subtype`, `name`) values ($1, $2, 1, 1, 1, \'[Closed]\')', array($tasks_id, $user_id)) or die(mysqli_query_error());
			
		// 	mysqli_query_params('insert into tree (`pid`, `user_id`, `system`, `type`, `subtype`, `name`) values ($1, $2, 1, 1, 6, \'[Messages]\')', array($my_casebox_id, $user_id)) or die(mysqli_query_error());
		// 	$messages_id = last_insert_id();
		// 	mysqli_query_params('insert into tree (`pid`, `user_id`, `system`, `type`, `subtype`, `name`) values ($1, $2, 1, 1, 1, \'[New]\')', array($messages_id, $user_id)) or die(mysqli_query_error());
		// 	mysqli_query_params('insert into tree (`pid`, `user_id`, `system`, `type`, `subtype`, `name`) values ($1, $2, 1, 1, 1, \'[Unread]\')', array($messages_id, $user_id)) or die(mysqli_query_error());
			
		// 	mysqli_query_params('insert into tree (`pid`, `user_id`, `system`, `type`, `subtype`, `name`) values ($1, $2, 1, 1, 7, \'[PrivateArea]\')', array($my_casebox_id, $user_id)) or die(mysqli_query_error());
		// 	$result = false;
		// }
		// /* checking if common area folders is created */
		// if(empty($existing_folder_types[8])){
		// 	/* Casebox folder does not exist, creating it and default subchilds */
		// 	mysqli_query_params('insert into tree (`user_id`, `system`, `type`, `subtype`, `name`) values (null, 1, 1, 8, \'Casebox\')') or die(mysqli_query_error());
		// 	$casebox_id = last_insert_id();
		// 	mysqli_query_params('insert into tree (`pid`, `user_id`, `system`, `type`, `subtype`, `name`) values ($1, null, 1, 1, 4, \'[Cases]\')', $casebox_id) or die(mysqli_query_error());
			
		// 	mysqli_query_params('insert into tree (`pid`, `user_id`, `system`, `type`, `subtype`, `name`) values ($1, null, 1, 1, 5, \'[Tasks]\')', $casebox_id) or die(mysqli_query_error());
		// 	$tasks_id = last_insert_id();
		// 	mysqli_query_params('insert into tree (`pid`, `user_id`, `system`, `type`, `subtype`, `name`) values ($1, null, 1, 1, 1, \'[Upcoming]\')', $tasks_id) or die(mysqli_query_error());
		// 	mysqli_query_params('insert into tree (`pid`, `user_id`, `system`, `type`, `subtype`, `name`) values ($1, null, 1, 1, 1, \'[Missed]\')', $tasks_id) or die(mysqli_query_error());
		// 	mysqli_query_params('insert into tree (`pid`, `user_id`, `system`, `type`, `subtype`, `name`) values ($1, null, 1, 1, 1, \'[Closed]\')', $tasks_id) or die(mysqli_query_error());
			
		// 	mysqli_query_params('insert into tree (`pid`, `user_id`, `system`, `type`, `subtype`, `name`) values ($1, null, 1, 1, 6, \'[Messages]\')', $casebox_id) or die(mysqli_query_error());
		// 	$messages_id = last_insert_id();
		// 	mysqli_query_params('insert into tree (`pid`, `user_id`, `system`, `type`, `subtype`, `name`) values ($1, null, 1, 1, 1, \'[New]\')', $messages_id) or die(mysqli_query_error());
		// 	mysqli_query_params('insert into tree (`pid`, `user_id`, `system`, `type`, `subtype`, `name`) values ($1, null, 1, 1, 1, \'[Unread]\')', $messages_id) or die(mysqli_query_error());
			
		// 	$result = false;
		// }

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
		$res = mysqli_query_params('select id from tree where user_id = $1 and system = 1 and (pid is null) and type = 1 and subtype = 2', $_SESSION['user']['id']) or die(mysqli_query_error());
		if($r = $res->fetch_row()) $rez = $r[0];
		$res->close();
		if(empty($rez)){
			mysqli_query_params('insert into tree (user_id, `system`, `type`, `subtype`, `name`, cid) values ($1, 1, 1, 2, \'[Home]\', $2)', array($user_id, $_SESSION['user']['id']) ) or die(mysqli_query_error());
			$rez = last_insert_id();
			SolrClient::runCron();
		}
		return $rez;
	}
	
	public static function getEmailFolderId($user_id = false){
		$rez = null;
		if(empty($user_id)) $user_id = $_SESSION['user']['id'];
		$pid = User::getUserHomeFolderId($user_id);

		$res = mysqli_query_params('select id from tree where user_id = $1 and system = 1 and pid =$2 and type = 1 and subtype = 6', array($_SESSION['user']['id'], $pid) ) or die(mysqli_query_error());
		if($r = $res->fetch_row()) $rez = $r[0];
		$res->close();
		if(empty($rez)){
			mysqli_query_params('insert into tree (pid, user_id, `system`, `type`, `subtype`, `name`, cid, uid) values ($1, $2, 1, 1, 6, \'[Emails]\', $3, $3)', array($pid, $user_id, $_SESSION['user']['id']) ) or die(mysqli_query_error());
			$rez = last_insert_id();
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
		move_uploaded_file($f['tmp_name'], CB_PHOTOS_PATH.$photoName);
		$res = mysqli_query_params('update users_groups set photo = $2 where id = $1', array($p['id'], $photoName)) or die(mysqli_query_error());		
		return array('success' => true, 'photo' => $photoName);
	}
}

