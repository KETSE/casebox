<?php
class UsersGroups{
	
	public function getChildren($p){ //CHECKED
		$rez = array();
		if(!Security::canManage()) throw new Exception(L\Access_denied);
		$path = explode('/', $p->path);
		$id = array_pop($path);
		$node_type = null;
		
		if(is_numeric($id)){
			$sql = 'select type from users_groups where id = $1';
			$res = mysqli_query_params($sql, $id) or die(mysqli_query_error());
			if($r = $res->fetch_row()) $node_type = $r[0];
			$res->close();
		}

		
		if($id == -1){ // users out of a group
			$sql = 'select id `nid`, u.cid, name, l'.UL_ID().' `text`, sex, `enabled` from users_groups u left join users_groups_association a on u.id = a.user_id where u.`type` = 2 and u.deleted = 0 and a.group_id is null order by 3, 2';
			$res = mysqli_query_params($sql, array()) or die(mysqli_query_error());
			while($r = $res->fetch_assoc()){
				$r['loaded'] = true;
				$rez[] = $r;
			}
			$res->close();
		}elseif(is_null($node_type)){ /* root node childs*/
			$sql = 'select id `nid`, name, l'.UL_ID().' `text`, (select count(*) from users_groups_association a JOIN users_groups u ON a.user_id = u.id AND u.deleted = 0 where group_id = g.id) `loaded`  from users_groups g where `type` = 1 and `system` = 0 order by 3, 2';
			$res = mysqli_query_params($sql, array()) or die(mysqli_query_error());
			while($r = $res->fetch_assoc()){
				$r['iconCls'] = 'icon-group';
				$r['loaded'] = empty($r['loaded']);
				$rez[] = $r;
			}
			$res->close();
			$rez[] = array('nid' => -1
				,'text' => L\Users_without_group
				,'iconCls' => 'icon-users'
				);
		}else{// group users
			$sql = 'select u.id `nid`, u.cid, u.name, u.l'.UL_ID().' `text`, sex, enabled from users_groups_association a join users_groups u on a.user_id = u.id where a.group_id = $1 and u.deleted = 0 ';
			$res = mysqli_query_params($sql, $id) or die(mysqli_query_error());
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
		if(!Security::canManage()) throw new Exception(L\Access_denied);
		$res = mysqli_query_params('select user_id from users_groups_association where user_id = $1 and group_id = $2', Array($user_id, $group_id) ) or die(mysqli_query_error());
		if($r = $res->fetch_row()) throw new Exception(L\UserAlreadyInOffice);
		$res->close();
		mysqli_query_params('insert into users_groups_association (user_id, group_id, cid) values ($1, $2, $3)', Array($user_id, $group_id, $_SESSION['user']['id']) ) or die(mysqli_query_error());
		//mysqli_query_params('CALL p_estimate_user_effective_access($1)', $user_id) or die(mysqli_query_error());
		return Array('success' => true);
	}

	public function deassociate($user_id, $group_id){ 
		if(!Security::canManage()) throw new Exception(L\Access_denied);
		$res = mysqli_query_params('delete from users_groups_association where user_id = $1 and group_id = $2', Array($user_id, $group_id) ) or die(mysqli_query_error());
		//mysqli_query_params('CALL p_estimate_user_effective_access($1)', $user_id) or die(mysqli_query_error());
		
		$outOfGroup = true;
		$res = mysqli_query_params('select group_id from users_groups_association where user_id = $1 limit 1', $user_id ) or die(mysqli_query_error()); //return if the user is associated to another office, otherwize it shoul be added to Users out of office folder
		if($r = $res->fetch_row()) $outOfGroup = false;
		return Array('success' => true, 'outOfGroup' => $outOfGroup);
	}
	public function addUser($params){ 
		if(!Security::canManage()) throw new Exception(L\Access_denied);
		////params: name, group_id
		$rez = Array('success' => false, 'msg' => L\Missing_required_fields);
		$params->name = trim($params->name);
		if(empty($params->name) || empty($params->password) || (empty($params->confirm_password) || ($params->password != $params->confirm_password))) return $rez;
		$user_id = 0;
		/*check user existance, if user already exists but is deleted then its record wiil be used for new user */
		$res = mysqli_query_params("select id from users_groups where name = $1 and deleted = 0", $params->name) or die(mysqli_query_error());
		if($r = $res->fetch_row()) throw new Exception(L\User_exists);
		$res->close();
		/*end of check user existance */
		
		mysqli_query_params('insert into users_groups (`name`, `cid`, `password`, language_id, cdate, uid, email) values($1, $2, MD5(CONCAT(\'aero\', $3)), $4, CURRENT_TIMESTAMP, $2, $5) '.
			'on duplicate key update id = last_insert_id(id), `name` = $1, `cid` = $2, `password` = MD5(CONCAT(\'aero\', $3))'.
			',last_login = null, login_successful = null, login_from_ip = null, last_logout = null, last_action_time = null, enabled = 1, visible_in_reports = 1, cdate = CURRENT_TIMESTAMP, deleted = 0, language_id = $4, uid = $2, cdate = CURRENT_TIMESTAMP'
			,array($params->name, $_SESSION['user']['id'], $params->password, $_SESSION['languages']['per_abrev'][$GLOBALS['CB_LANGUAGE']]['id'], $params->email ) ) or die(mysqli_query_error());
		if($user_id = last_insert_id()){
			$rez = Array('success' => true, 'data' => Array('id' => $user_id));
			$params->id = $user_id;
		}
		
		mysqli_query_params('delete from users_groups_data where user_id = $1',  $user_id) or die(mysqli_query_error());
		
		$res = mysqli_query_params('select id from templates where `type` = 6') or die(mysqli_query_error());
		if($r = $res->fetch_row()) $params->template_id = $r[0];
		$params->sex = null;
		//$params->email = '';
		
		require_once 'VerticalEditGrid.php';
		VerticalEditGrid::addFormData('users_groups', $params);
		
		$res = mysqli_query_params('select tag_id from users_groups where id = $1', $user_id) or die(mysqli_query_error());
		if($r = $res->fetch_row()) $rez['data']['tag_id'] = $r[0];
		$res->close();
		/* in case it was a deleted user we delete all old acceses */
		mysqli_query_params('delete from users_groups_association where user_id = $1', $user_id) or die(mysqli_query_error());
		mysqli_query_params('delete from tree_acl where user_group_id = $1', $rez['data']['tag_id']) or die(mysqli_query_error());
		/* end of in case it was a deleted user we delete all old acceses */
		if( isset($params->group_id) && is_numeric($params->group_id) ){ //&& ( in_array($params->group_id, Security::getManagedOfficeIds()) )
			//if(!$this->validRole($params->role_id)) throw new Exception(L\Wrong_input_data);
			mysqli_query_params('insert into users_groups_association (user_id, group_id, cid) values($1, $2, $3) on duplicate key update cid = $3', Array($user_id, $params->group_id, $_SESSION['user']['id'])) or die(mysqli_query_error());
			$rez['data']['group_id'] = $params->group_id;
			//mysqli_query_params('CALL p_estimate_user_effective_access($1)', $user_id) or die(mysqli_query_error());
		}else $rez['data']['group_id'] = 0;
		
		$this->updateUserEmails($user_id);
		return $rez;
	}
	private function updateUserEmails($user_id){
		$emails = array();
		$res = mysqli_query_params('SELECT ud.value FROM templates t  JOIN templates_structure ts ON ts.template_id = t.id AND ts.name = \'email\' JOIN users_groups_data ud ON ts.id = ud.field_id and ud.user_id = $1 WHERE t.`type` = 6', $user_id) or die(mysqli_query_error());
		while($r = $res->fetch_row()) if(!empty($r[0])) $emails[] = $r[0];
		$res->close();
		$emails = empty($emails) ? null : implode(', ', $emails);
		mysqli_query_params('update users_groups set email = $1 where id = $2', array($emails, $user_id)) or die(mysqli_query_error());
	}
	public function deleteUser($user_id){ 
		if(!Security::canManage()) throw new Exception(L\Access_denied);
		$res = mysqli_query_params('update users_groups set deleted = 1, did = $2 where id = $1 and (cid = $2) ', array($user_id, $_SESSION['user']['id']) ) or die(mysqli_query_error());
		return Array('success' => affected_rows() ? true : false, 'data' => array($user_id, $_SESSION['user']['id']));
	}
	public function deleteGroup($group_id){ 
		if(!Security::isAdmin()) throw new Exception(L\Access_denied);
		/* selecting currently associated users to this group to estimate their access after deletition */
		$user_ids = array();
		$res = mysqli_query_params('select user_id from users_groups_association where group_id = $1', $group_id) or die(mysqli_query_error());
		while($r = $res->fetch_row()) $user_ids[] = $r[0];
		$res->close();
		mysqli_query_params('delete from users_groups_association where group_id = $1', $group_id) or die(mysqli_query_error());
		/* end of selecting currently associated users to this office to estimate their access after deletition */
		
		mysqli_query_params('delete from users_groups where id = $1 and `type` = 1', $group_id) or die(mysqli_query_error()); 
		//mysqli_query_params('delete from t using tags t JOIN tag_groups__tags_result tr ON t.id = tr.tag_id JOIN tag_groups g ON tr.tags_group_id = g.id AND g.system = 1 where t.id = $1', $office_id) or die(mysqli_query_error());
		
		//foreach($user_ids as $id) 	mysqli_query_params('CALL p_estimate_user_effective_access($1)', $id) or die(mysqli_query_error());
		return Array('success' => true);
	}/**/


	public function getUserData($p){
		if(($_SESSION['user']['id'] != $p->data->id) && !Security::canManage()) throw new Exception(L\Access_denied);
		$user_id = $p->data->id;
		$rez = Array('success' => false, 'msg' => L\Wrong_id);
		
		$res = mysqli_query_params('select id, cid, name, '.$_SESSION['languages']['string'].', sex, email, enabled, '.
			'date_format(last_action_time, \''.$_SESSION['user']['short_date_format'].' %H:%i\') last_action_time, '.
			'date_format(cdate, \''.$_SESSION['user']['short_date_format'].' %H:%i\') `cdate`, '.
			'(select l'.UL_ID().' from users_groups where id = u.cid) owner, '.
			'(select id from templates where `type` = 6) template_id '.
			'from users_groups u where id = $1 ', $user_id) or die(mysqli_query_error());
		if($r = $res->fetch_assoc()) $rez = Array('success' => true, 'data' => $r);
		$res->close();
		if($rez['success'] == false) throw new Exception(L\Wrong_id);
		
		require_once 'VerticalEditGrid.php';
		VerticalEditGrid::getData('users_groups', $rez['data']);
		
		return $rez;
	}

	public function saveUserData($params){	//
		$rez = Array('success' => true);
		$data = json_decode($params['data']);
		
		//if(!Security::isAdmin() && !Security::isUsersOwner($data->id) && !($_SESSION['user']['id'] == $data->id)) throw new Exception(L\Access_denied);
		require_once 'VerticalEditGrid.php';
		VerticalEditGrid::saveData('users_groups', $data);
		
		/* if updating current logged user then checking if interface params have changed */
		$interface_params_changed = false;
		if($data->id == $_SESSION['user']['id']){
			$res = mysqli_query_params('select '.$_SESSION['languages']['string'].', language_id, short_date_format, long_date_format from users_groups u where id = $1 ', $data->id) or die(mysqli_query_error());
			if($r = $res->fetch_assoc()){
				$r['language'] = $_SESSION['languages']['per_id'][$r['language_id']]['abreviation'];
				if(empty($r['short_date_format'])) $r['short_date_format'] = $_SESSION['languages']['per_id'][$r['language_id']]['short_date_format'];
				if(empty($r['long_date_format'])) $r['long_date_format'] = $_SESSION['languages']['per_id'][$r['language_id']]['long_date_format'];
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
		if(!Security::canManage()) throw new Exception(L\Access_denied);
		$user_id = $this->extractId($user_id);
		$rez = $this->getUserData( (Object) array( 'data' => (object)array('id' => $user_id) ) );
		
		$rez['data']['groups'] = array();
		$sql = 'SELECT a.group_id from users_groups_association a where user_id = $1';
		$res = mysqli_query_params($sql, $user_id) or die(mysqli_query_error());
		while($r = $res->fetch_row())
			$rez['data']['groups'][] = $r[0]; 
		$res->close();
		return $rez;
	}

	public function saveAccessData($params){ 
		if(!Security::canManage()) throw new Exception(L\Access_denied);
		$params = (Array)$params;
		@$user_id = $this->extractId($params['id']);
		
		//analize groups
		$keep_groups = array();
		if(!empty($params['groups']))
		foreach($params['groups'] as $group_id){
			if(!is_numeric($group_id)) continue;
			$keep_groups[] = $group_id;
			mysqli_query_params('insert into users_groups_association (user_id, group_id, cid) values($1, $2, $3) on duplicate key update uid = $3', Array($user_id, $group_id, $_SESSION['user']['id'])) or die(mysqli_query_error());
		}
		mysqli_query_params('delete from users_groups_association where user_id = $1 and group_id not in (0'.implode(',', $keep_groups).') ', $params['id']) or die(mysqli_query_error());
		//mysqli_query_params('CALL p_estimate_user_effective_access($1)', $user_id) or die(mysqli_query_error());
		return Array('success' => true);
	}	

//---------------------------------------------------------------------------------
	

	
	
	public function addGroup($params){ 
		$rez = Array('success' => true, 'data' => array());
		////params: name, office_id, role_id
		$params->name = trim($params->name);
		if(empty($params->name) || (!Security::isAdmin())) throw new Exception(L\Failed_creating_office);
		// check if office with that name already exists 
		$res = mysqli_query_params('select t.id from tag_groups g join tag_groups__tags_result tr on g.id = tr.tags_group_id join tags t on tr.tag_id = t.id where g.system = 1 and t.l'.UL_ID().' = $1', $params->name) or die(mysqli_query_error());
		if($r = $res->fetch_row()) throw new Exception(L\Office_exists);
		$res->close();
		// end of check if office with that name already exists 
		$pid = null;
		$res = mysqli_query_params('select t.id from tag_groups g join tag_groups__tags tr on g.id = tr.tags_group_id join tags t on tr.tag_id = t.id where g.system = 1 order by t.`type`, t.`order`') or die(mysqli_query_error());
		if($r = $res->fetch_row()) $pid = $r[0];
		$res->close();
		
		$office_id = null;
		mysqli_query_params('insert into tags (pid, '.$_SESSION['languages']['string'].', `type`) VALUES($1 '.str_repeat(',$2', $_SESSION['languages']['count']).', 1)', array($pid, $params->name) ) or die(mysqli_query_error());
		if($office_id = last_insert_id()) $rez['data']['id'] = $office_id;
		if(empty($pid)) mysqli_query_params('insert into tag_groups__tags (tags_group_id, tag_id, recursive) select id, $1, 1 from tag_groups where system = 1', $office_id ) or die(mysqli_query_error());
		require_once 'System.php';
		System::updateTagGroupsResultTable($office_id);
		return $rez;
	}/**/


	public function changeRole($user_id, $office_id, $role_id){ //NOT USED IN INTERFACE
		if(!Security::canManage()) throw new Exception(L\Access_denied);
		$user_id = $this->extractId($user_id);
		$office_id = $this->extractId($office_id);
		if(!$this->validRole($role_id)) throw new Exception(L\Wrong_input_data);
		if(!in_array($office_id, Security::getManagedOfficeIds())) throw new Exception(L\No_manage_access_for_office);
		if($role_id < Security::getUserRole()) throw new Exception(L\Cannot_give_higher_access);
		$res = mysqli_query_params('update users_groups_association set role_id = $1, uid = $4 where user_id = $2 and office_id = $3', Array($role_id, $user_id, $office_id, $_SESSION['user']['id']) ) or die(mysqli_query_error());
		//mysqli_query_params('CALL p_estimate_user_effective_access($1)', $user_id) or die(mysqli_query_error());
		return Array('success' => true);
	}

	public function setRoleActive($user_id, $office_id, $active){ //NOT USED IN INTERFACE
		if(!Security::canManage()) throw new Exception(L\Access_denied);
		$user_id = $this->extractId($user_id);
		$office_id = $this->extractId($office_id);
		if(!in_array($office_id, Security::getManagedOfficeIds())) throw new Exception(L\No_manage_access_for_office);
		mysqli_query_params('update users_groups_association set `active` = $1, uid = $4 where user_id = $2 and office_id = $3', Array($active, $user_id, $office_id, $_SESSION['user']['id']) ) or die(mysqli_query_error());
		//mysqli_query_params('CALL p_estimate_user_effective_access($1)', $user_id) or die(mysqli_query_error());
		return Array('success' => true);
	}
	public function changePassword($p){ 
		/* passord could be changed by: admin, user owner, user himself */
		if(empty($p['password']) || ($p['password'] != $p['confirmpassword'])) throw new exception(L\Wrong_input_data);
		$user_id = $this->extractId($p['id']);

		/* check for old password if users changes password for himself */
		if($_SESSION['user']['id'] == $user_id){
			$res = mysqli_query_params('select id from users_groups where id = $1 and `password` = MD5(CONCAT(\'aero\', $2))', array($user_id, $p['currentpassword'])) or die(mysqli_query_error());
			if(!$res->fetch_row()) throw new exception(L\WrongCurrentPassword);
			$res->close();
		}
		/* end of check for old password if users changes password for himself */

		if(!Security::isAdmin() && !Security::isUsersOwner($user_id) && !($_SESSION['user']['id'] == $user_id)) throw new Exception(L\Access_denied);
		
		mysqli_query_params('update users_groups set `password` = MD5(CONCAT(\'aero\', $2)), uid = $3 where id = $1', array($user_id, $p['password'], $_SESSION['user']['id'])) or die(mysqli_query_error());
		return array('success' => true);
	}
	public function changeUsername($p){ 
		/* username could be changed by: admin or user owner */
		$name = trim(strtolower($p->name));
		$matches = preg_match('/^[a-z0-9\._]+$/', $name);
		if(empty($p->name) || empty($matches) ) throw new exception(L\Wrong_input_data);
		
		$user_id = $this->extractId($p->id);

		if(!Security::isAdmin() && !Security::isUsersOwner($user_id)) throw new Exception(L\Access_denied);
		
		mysqli_query_params('update users_groups set `name` = $2, uid = $3 where id = $1', array($user_id, $name, $_SESSION['user']['id'])) or die(mysqli_query_error());
		return array('success' => true, 'name' => $name);
	}
	public function getUserTags(){
		$tags = array();
		$res = mysqli_query_params('select id, l'.UL_ID().' `name` from tags where user_id = $1 order by 2', $_SESSION['user']['id'] ) or die(mysqli_query_error());
		while($r = $res->fetch_assoc()) $tags[] = $r;
		$res->close();
		return array('success' => true, 'data' => $tags);
	}
	public function addUserTag($params){
		$res = mysqli_query_params('select id from tags where user_id = $1 and l'.UL_ID().' = $2', Array($_SESSION['user']['id'], $params->name) ) or die(mysqli_query_error());
		if($r = $res->fetch_row()) throw new Exception(L\Tag_already_exists);
		$res->close();
		$p = array('user_id' => $_SESSION['user']['id']);
		$values_string = '$1';
		$on_duplicate = '';
		getLanguagesParams($params, $p, $values_string, $on_duplicate, $params->name);
		
		mysqli_query_params('insert into tags ('.implode(',', array_keys($p)).') values('.$values_string.')', array_values($p) ) or die(mysqli_query_error());
		$id = last_insert_id();
		return array('success' => true, 'data' => array('id' => $id, 'name' => $params->name));
	}
	public function searchUserTags($p){
		$tags = array();
		$res = mysqli_query_params('select id, l'.UL_ID().' `name` from tags where user_id = $1 and l'.UL_ID().' like $2 order by 2 limit 15', Array($_SESSION['user']['id'], $p->text.'%') ) or die(mysqli_query_error());
		while($r = $res->fetch_assoc()) $tags[] = $r;
		$res->close();
		return array('success' => true, 'data' => $tags);
	}
	
	public function searchSysTags($p){
		$tags = array();
		$res = mysqli_query_params('SELECT t.id, t.l'.UL_ID().' `name`, tr.tags_group_id `groupId` '.
			'FROM tag_groups__tags_result tr '.
			'JOIN tag_groups tg ON tr.tags_group_id = tg.id AND tg.system IN (0, $2) '.
			'JOIN tags t ON t.id = tr.tag_id AND t.l'.UL_ID().' LIKE $1 '.
			'ORDER BY tg.order, t.order, 2 LIMIT 15', Array($p->text.'%', $p->group_id) ) or die(mysqli_query_error());
		while($r = $res->fetch_assoc()) $tags[] = $r;
		$res->close();
		return array('success' => true, 'data' => $tags);
	}
	public static function getUserPreferences($id){
		$rez = array();
		$res = mysqli_query_params('select id, tag_id, name, '.$_SESSION['languages']['string'].', sex, email, language_id, short_date_format, long_date_format from users_groups where enabled = 1 and deleted = 0 and id = $1', $id) or die(mysqli_query_error());
		if($r = $res->fetch_assoc()){
			$res2 = mysqli_query_params('select short_date_format, long_date_format, time_format from languages where id = $1', $r['language_id']) or die(mysqli_query_error());
			if($r2 = $res2->fetch_assoc())
				foreach($r2 as $k => $v) if(empty($r[$k])) $r[$k] = $r2[$k];
			$res2->close();
			$rez = $r;
		}
		$res->close();
		return $rez;
	}
// PRIVATE SECTION	
	private function validRole($role_id){ return (is_numeric($role_id) && ($role_id > 1) && ($role_id < 5)); } //OK
	private function extractId($id){ //OK
		if(is_numeric($id)) return $id;
		$a = explode('-', $id);
		$id = array_pop($a);
		if(!is_numeric($id) || ($id < 1)) throw new Exception(L\Wrong_input_data);
		return $id;
	}
}
?>