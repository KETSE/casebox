<?php

namespace CB;

class UsersGroups{
	
	public function getChildren($p){ //CHECKED
		$rez = array();
		if(!Security::canManage()) throw new \Exception(L\Access_denied);
		$path = explode('/', $p->path);
		$id = array_pop($path);
		$node_type = null;
		
		if(is_numeric($id)){
			$sql = 'select type from users_groups where id = $1';
			$res = DB\mysqli_query_params($sql, $id) or die(DB\mysqli_query_error());
			if($r = $res->fetch_row()) $node_type = $r[0];
			$res->close();
		}

		
		if($id == -1){ // users out of a group
			$sql = 'select id `nid`, u.cid, name, l'.USER_LANGUAGE_INDEX.' `text`, sex, `enabled` from users_groups u left join users_groups_association a on u.id = a.user_id where u.`type` = 2 and u.deleted = 0 and a.group_id is null order by 3, 2';
			$res = DB\mysqli_query_params($sql, array()) or die(DB\mysqli_query_error());
			while($r = $res->fetch_assoc()){
				$r['loaded'] = true;
				// $r['expanded'] = true;
				$rez[] = $r;
			}
			$res->close();
		}elseif(is_null($node_type)){ /* root node childs*/
			$sql = 'select id `nid`, name, l'.USER_LANGUAGE_INDEX.' `text`, `type`, `system`, (select count(*) from users_groups_association a JOIN users_groups u ON a.user_id = u.id AND u.deleted = 0 where group_id = g.id) `loaded`  from users_groups g where `type` = 1 and `system` = 0 order by 3, 2';
			$res = DB\mysqli_query_params($sql, array()) or die(DB\mysqli_query_error());
			while($r = $res->fetch_assoc()){
				$r['iconCls'] = 'icon-users';
				// if(empty($r['loaded'])) $r['loaded'] = true;
				// else{ 
					// unset($r['loaded']);
					// $r['loaded'] = true;
					// $r['children'] = $this->getChildren(json_decode('{"path":"/'.$r['nid'].'"}'));
				// }
				$r['expanded'] = true;
				
				$rez[] = $r;
			}
			$res->close();
			$rez[] = array('nid' => -1
				,'text' => L\Users_without_group
				,'iconCls' => 'icon-users'
				,'type' => 1
				,'expanded' => true
				
				// ,'children' => $this->getChildren(json_decode('{"path":"/-1"}'))
				);
		}else{// group users
			$sql = 'select u.id `nid`, u.cid, u.name, u.l'.USER_LANGUAGE_INDEX.' `text`, sex, enabled from users_groups_association a join users_groups u on a.user_id = u.id where a.group_id = $1 and u.deleted = 0 ';
			$res = DB\mysqli_query_params($sql, $id) or die(DB\mysqli_query_error());
			while($r = $res->fetch_assoc()){
				$r['loaded'] = true;
				$rez[] = $r;
			}
			$res->close();
		}
		
		$pid = empty($id) ? 'is null' : ' = '.intval($id);
		
		return $rez;
	}

	public function associate($user_id, $group_id){ 
		if(!Security::canManage()) throw new \Exception(L\Access_denied);
		$res = DB\mysqli_query_params('select user_id from users_groups_association where user_id = $1 and group_id = $2', Array($user_id, $group_id) ) or die(DB\mysqli_query_error());
		if($r = $res->fetch_row()) throw new \Exception(L\UserAlreadyInOffice);
		$res->close();
		DB\mysqli_query_params('insert into users_groups_association (user_id, group_id, cid) values ($1, $2, $3)', Array($user_id, $group_id, $_SESSION['user']['id']) ) or die(DB\mysqli_query_error());
		//DB\mysqli_query_params('CALL p_estimate_user_effective_access($1)', $user_id) or die(DB\mysqli_query_error());
		return Array('success' => true);
	}

	public function deassociate($user_id, $group_id){ 
		if(!Security::canManage()) throw new \Exception(L\Access_denied);
		$res = DB\mysqli_query_params('delete from users_groups_association where user_id = $1 and group_id = $2', Array($user_id, $group_id) ) or die(DB\mysqli_query_error());
		//DB\mysqli_query_params('CALL p_estimate_user_effective_access($1)', $user_id) or die(DB\mysqli_query_error());
		
		$outOfGroup = true;
		$res = DB\mysqli_query_params('select group_id from users_groups_association where user_id = $1 limit 1', $user_id ) or die(DB\mysqli_query_error()); //return if the user is associated to another office, otherwize it shoul be added to Users out of office folder
		if($r = $res->fetch_row()) $outOfGroup = false;
		return Array('success' => true, 'outOfGroup' => $outOfGroup);
	}
	public function addUser($params){ 
		if(!Security::canManage()) throw new \Exception(L\Access_denied);
		////params: name, group_id
		$rez = Array('success' => false, 'msg' => L\Missing_required_fields);
		$params->name = trim($params->name);
		if(empty($params->name) || empty($params->password) || (empty($params->confirm_password) || ($params->password != $params->confirm_password))) return $rez;
		$user_id = 0;
		/*check user existance, if user already exists but is deleted then its record wiil be used for new user */
		$res = DB\mysqli_query_params("select id from users_groups where name = $1 and deleted = 0", $params->name) or die(DB\mysqli_query_error());
		if($r = $res->fetch_row()) throw new \Exception(L\User_exists);
		$res->close();
		/*end of check user existance */
		
		DB\mysqli_query_params('insert into users_groups (`name`, `cid`, `password`, language_id, cdate, uid, email) values($1, $2, MD5(CONCAT(\'aero\', $3)), $4, CURRENT_TIMESTAMP, $2, $5) '.
			'on duplicate key update id = last_insert_id(id), `name` = $1, `cid` = $2, `password` = MD5(CONCAT(\'aero\', $3))'.
			',last_login = null, login_successful = null, login_from_ip = null, last_logout = null, last_action_time = null, enabled = 1, visible_in_reports = 1, cdate = CURRENT_TIMESTAMP, deleted = 0, language_id = $4, uid = $2, cdate = CURRENT_TIMESTAMP'
			,array($params->name, $_SESSION['user']['id'], $params->password, LANGUAGE_INDEX, $params->email ) ) or die(DB\mysqli_query_error());
		if($user_id = DB\last_insert_id()){
			$rez = Array('success' => true, 'data' => Array('id' => $user_id));
			$params->id = $user_id;
		}
		
		DB\mysqli_query_params('delete from users_groups_data where user_id = $1',  $user_id) or die(DB\mysqli_query_error());
		
		$res = DB\mysqli_query_params('select id from templates where `type` = 6') or die(DB\mysqli_query_error());
		if($r = $res->fetch_row()) $params->template_id = $r[0];
		$params->sex = null;
		//$params->email = '';
		
		require_once 'VerticalEditGrid.php';
		VerticalEditGrid::addFormData('users_groups', $params);
		
		$res = DB\mysqli_query_params('select tag_id from users_groups where id = $1', $user_id) or die(DB\mysqli_query_error());
		if($r = $res->fetch_row()) $rez['data']['tag_id'] = $r[0];
		$res->close();
		/* in case it was a deleted user we delete all old acceses */
		DB\mysqli_query_params('delete from users_groups_association where user_id = $1', $user_id) or die(DB\mysqli_query_error());
		DB\mysqli_query_params('delete from tree_acl where user_group_id = $1', $rez['data']['tag_id']) or die(DB\mysqli_query_error());
		/* end of in case it was a deleted user we delete all old acceses */
		if( isset($params->group_id) && is_numeric($params->group_id) ){ //&& ( in_array($params->group_id, Security::getManagedOfficeIds()) )
			DB\mysqli_query_params('insert into users_groups_association (user_id, group_id, cid) values($1, $2, $3) on duplicate key update cid = $3', Array($user_id, $params->group_id, $_SESSION['user']['id'])) or die(DB\mysqli_query_error());
			$rez['data']['group_id'] = $params->group_id;
			//DB\mysqli_query_params('CALL p_estimate_user_effective_access($1)', $user_id) or die(DB\mysqli_query_error());
		}else $rez['data']['group_id'] = 0;
		
		$this->updateUserEmails($user_id);
		return $rez;
	}
	private function updateUserEmails($user_id){
		$emails = array();
		$res = DB\mysqli_query_params('SELECT ud.value FROM templates t  JOIN templates_structure ts ON ts.template_id = t.id AND ts.name = \'email\' JOIN users_groups_data ud ON ts.id = ud.field_id and ud.user_id = $1 WHERE t.`type` = 6', $user_id) or die(DB\mysqli_query_error());
		while($r = $res->fetch_row()) if(!empty($r[0])) $emails[] = $r[0];
		$res->close();
		$emails = empty($emails) ? null : implode(', ', $emails);
		DB\mysqli_query_params('update users_groups set email = $1 where id = $2', array($emails, $user_id)) or die(DB\mysqli_query_error());
	}
	public function deleteUser($user_id){ 
		if(!Security::canManage()) throw new \Exception(L\Access_denied);
		$res = DB\mysqli_query_params('update users_groups set deleted = 1, did = $2 where id = $1 ', array($user_id, $_SESSION['user']['id']) ) or die(DB\mysqli_query_error()); // and (cid = $2) !!!!
		return Array('success' => DB\affected_rows() ? true : false, 'data' => array($user_id, $_SESSION['user']['id']));
	}
	public function deleteGroup($group_id){ 
		if(!Security::isAdmin()) throw new \Exception(L\Access_denied);
		/* selecting currently associated users to this group to estimate their access after deletition */
		$user_ids = array();
		$res = DB\mysqli_query_params('select user_id from users_groups_association where group_id = $1', $group_id) or die(DB\mysqli_query_error());
		while($r = $res->fetch_row()) $user_ids[] = $r[0];
		$res->close();
		DB\mysqli_query_params('delete from users_groups_association where group_id = $1', $group_id) or die(DB\mysqli_query_error());
		/* end of selecting currently associated users to this office to estimate their access after deletition */
		
		DB\mysqli_query_params('delete from users_groups where id = $1 and `type` = 1', $group_id) or die(DB\mysqli_query_error()); 
		return Array('success' => true);
	}


	public function getUserData($p){
		if(($_SESSION['user']['id'] != $p->data->id) && !Security::canManage()) throw new \Exception(L\Access_denied);
		$user_id = $p->data->id;
		$rez = Array('success' => false, 'msg' => L\Wrong_id);
		
		$res = DB\mysqli_query_params('select id, cid, name, '.config\language_fields.', sex, email, enabled, '.
			'date_format(last_action_time, \''.$_SESSION['user']['short_date_format'].' %H:%i\') last_action_time, '.
			'date_format(cdate, \''.$_SESSION['user']['short_date_format'].' %H:%i\') `cdate`, '.
			'(select l'.USER_LANGUAGE_INDEX.' from users_groups where id = u.cid) owner, '.
			'(select id from templates where `type` = 6) template_id '.
			'from users_groups u where id = $1 ', $user_id) or die(DB\mysqli_query_error());
		if($r = $res->fetch_assoc()) $rez = Array('success' => true, 'data' => $r);
		$res->close();
		if($rez['success'] == false) throw new \Exception(L\Wrong_id);
		
		require_once 'VerticalEditGrid.php';
		VerticalEditGrid::getData('users_groups', $rez['data']);
		
		return $rez;
	}

	public function saveUserData($params){	//
		$rez = Array('success' => true);
		$data = json_decode($params['data']);
		
		//if(!Security::isAdmin() && !Security::isUsersOwner($data->id) && !($_SESSION['user']['id'] == $data->id)) throw new \Exception(L\Access_denied);
		require_once 'VerticalEditGrid.php';
		VerticalEditGrid::saveData('users_groups', $data);
		
		/* if updating current logged user then checking if interface params have changed */
		$interface_params_changed = false;
		if($data->id == $_SESSION['user']['id']){
			$res = DB\mysqli_query_params('select '.config\language_fields.', language_id, short_date_format, long_date_format from users_groups u where id = $1 ', $data->id) or die(DB\mysqli_query_error());
			if($r = $res->fetch_assoc()){
				$r['language'] = $GLOBALS['languages'][$r['language_id']-1];
				if(empty($r['long_date_format'])) $r['long_date_format'] = $GLOBALS['language_settings'][$r['language']]['long_date_format'];
				if(empty($r['short_date_format'])) $r['short_date_format'] = $GLOBALS['language_settings'][$r['language']]['short_date_format'];
				foreach($r as $k => $v) 
					if($_SESSION['user'][$k] != $v){
						$interface_params_changed = true;
						$_SESSION['user'][$k] = $v;
					}
			}
			$res->close();
			if($interface_params_changed) $rez['interface_params_changed'] = true;
		}
		/* end of if updating current logged user then checking if interface params have changed */
		$this->updateUserEmails($data->id);
		return $rez;
	}
	
	public function getAccessData($user_id = false){ 
		if(!Security::canManage()) throw new \Exception(L\Access_denied);
		$user_id = $this->extractId($user_id);
		$rez = $this->getUserData( (Object) array( 'data' => (object)array('id' => $user_id) ) );
		
		$rez['data']['groups'] = array();
		$sql = 'SELECT a.group_id from users_groups_association a where user_id = $1';
		$res = DB\mysqli_query_params($sql, $user_id) or die(DB\mysqli_query_error());
		while($r = $res->fetch_row())
			$rez['data']['groups'][] = $r[0]; 
		$res->close();
		return $rez;
	}

	public function saveAccessData($params){ 
		if(!Security::canManage()) throw new \Exception(L\Access_denied);
		$params = (Array)$params;
		@$user_id = $this->extractId($params['id']);
		
		//analize groups
		$keep_groups = array();
		if(!empty($params['groups']))
		foreach($params['groups'] as $group_id){
			if(!is_numeric($group_id)) continue;
			$keep_groups[] = $group_id;
			DB\mysqli_query_params('insert into users_groups_association (user_id, group_id, cid) values($1, $2, $3) on duplicate key update uid = $3', Array($user_id, $group_id, $_SESSION['user']['id'])) or die(DB\mysqli_query_error());
		}
		DB\mysqli_query_params('delete from users_groups_association where user_id = $1 and group_id not in (0'.implode(',', $keep_groups).') ', $params['id']) or die(DB\mysqli_query_error());
		//DB\mysqli_query_params('CALL p_estimate_user_effective_access($1)', $user_id) or die(DB\mysqli_query_error());
		return Array('success' => true);
	}	

	public function changePassword($p){ 
		/* passord could be changed by: admin, user owner, user himself */
		if(empty($p['password']) || ($p['password'] != $p['confirmpassword'])) throw new \Exception(L\Wrong_input_data);
		$user_id = $this->extractId($p['id']);

		/* check for old password if users changes password for himself */
		if($_SESSION['user']['id'] == $user_id){
			$res = DB\mysqli_query_params('select id from users_groups where id = $1 and `password` = MD5(CONCAT(\'aero\', $2))', array($user_id, $p['currentpassword'])) or die(DB\mysqli_query_error());
			if(!$res->fetch_row()) throw new \Exception(L\WrongCurrentPassword);
			$res->close();
		}
		/* end of check for old password if users changes password for himself */

		if(!Security::isAdmin() && !Security::isUsersOwner($user_id) && !($_SESSION['user']['id'] == $user_id)) throw new \Exception(L\Access_denied);
		
		DB\mysqli_query_params('update users_groups set `password` = MD5(CONCAT(\'aero\', $2)), uid = $3 where id = $1', array($user_id, $p['password'], $_SESSION['user']['id'])) or die(DB\mysqli_query_error());
		return array('success' => true);
	}

	public function changeUsername($p){ 
		/* username could be changed by: admin or user owner */
		$name = trim(strtolower($p->name));
		$matches = preg_match('/^[a-z0-9\._]+$/', $name);
		if(empty($p->name) || empty($matches) ) throw new \Exception(L\Wrong_input_data);
		
		$user_id = $this->extractId($p->id);

		//if(!Security::isAdmin() && !Security::isUsersOwner($user_id)) throw new \Exception(L\Access_denied);
		
		DB\mysqli_query_params('update users_groups set `name` = $2, uid = $3 where id = $1', array($user_id, $name, $_SESSION['user']['id'])) or die(DB\mysqli_query_error());
		return array('success' => true, 'name' => $name);
	}

//---------------------------------------------------------------------------------
	public static function getUserPreferences($id){
		$rez = array();
		$res = DB\mysqli_query_params('select id, tag_id, name, '.config\language_fields.', sex, email, language_id, short_date_format, long_date_format from users_groups where enabled = 1 and deleted = 0 and id = $1', $id) or die(DB\mysqli_query_error());
		if($r = $res->fetch_assoc()){
			$r['language'] = $GLOBALS['languages'][$r['language_id']-1];
			$r['long_date_format'] = Util\coalesce($r['long_date_format'], $GLOBALS['language_settings'][$r['language']]['long_date_format']);
			$r['short_date_format'] = Util\coalesce($r['short_date_format'], $GLOBALS['language_settings'][$r['language']]['short_date_format']);
			$rez = $r;
		}
		$res->close();
		return $rez;
	}
// PRIVATE SECTION	
	private function extractId($id){ //OK
		if(is_numeric($id)) return $id;
		$a = explode('-', $id);
		$id = array_pop($a);
		if(!is_numeric($id) || ($id < 1)) throw new \Exception(L\Wrong_input_data);
		return $id;
	}
}
?>