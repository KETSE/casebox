<?php
class UsersGroups{
	
	public function getChildren($params){ //CHECKED
		if(!Security::canManage()) throw new Exception(L\Access_denied);
		$p = explode('/', $params->path);
		$p = array_pop($p);
		$n = explode('-', $p);
		@$rez = $this->getNodeChildren($n[0], $n[1]);
		return $rez;
	}

	public function getReadAccessChildren($params){ //CHECKED
		$rez = array('success' => false, 'msg' => L\Access_denied);
		if(!Security::canManageCase($params->case_id)) throw new Exception(L\Access_denied);

		$p = explode('/', $params->path);
		$p = array_pop($p);
		$n = explode('-', $p);
		$rez = array();
		@$rez = $this->getNodeChildren($n[0], $n[1], false, $params->case_id);
		return $rez;
	}

	public function getUserData($p){//added to tests	//CHECKED
		if(($_SESSION['user']['id'] != $p->data->id) && !Security::canManage()) throw new Exception(L\Access_denied);
		$user_id = $this->extractId($p->data->id);
		$rez = Array('success' => false, 'msg' => L\Wrong_id);
		$res = mysqli_query_params('select id, pid, name, '.$_SESSION['languages']['string'].', sex, email, enabled, '.
			'date_format(last_action_time, \''.$_SESSION['user']['short_date_format'].' %H:%i\') last_action_time, '.
			'date_format(cdate, \''.$_SESSION['user']['short_date_format'].' %H:%i\') `cdate`, '.
			'(select l'.UL_ID().' from users where id = u.pid) owner, '.
			'(select id from templates where `type` = 6) template_id '.
			'from users u where id = $1 ', $user_id) or die(mysqli_query_error());
		if($r = $res->fetch_assoc()) $rez = Array('success' => true, 'data' => $r);
		$res->close();
		if($rez['success'] == false) throw new Exception(L\Wrong_id);
		
		require_once 'VerticalEditGrid.php';
		VerticalEditGrid::getData('users', $rez['data']);
		
		return $rez;
	}
	
	public function getAccessData($user_id = false){//added to tests //CHECKED
		if(!Security::canManage()) throw new Exception(L\Access_denied);
		$user_id = $this->extractId($user_id);
		$data = array();
		$res = mysqli_query_params('select id, tag_id, pid, name, l'.UL_ID().', sex, photo, '.
			'date_format(last_action_time, \''.$_SESSION['user']['short_date_format'].' %H:%i\') last_action_time, '.
			'date_format(cdate, \''.$_SESSION['user']['short_date_format'].' %H:%i\') `cdate`, '.
			'(select l'.UL_ID().' from users where id = u.pid) owner, '.
			'(select id from templates where `type` = 6) template_id '.
			'from users u where id = $1 ', $user_id) or die(mysqli_query_error());
		if($r = $res->fetch_assoc()) $data = $r; else throw new Exception(L\Wrong_id);
		$res->close();

		$sql = 'SELECT r.id, r.l'.UL_ID().' `office`, (ur.role_id = 2) `manager`, (ur.role_id = 3) `lawyer`, (ur.role_id = 4) `user`
			FROM tag_groups g join tag_groups__tags_result tr on g.id = tr.tags_group_id join tags r on tr.tag_id = r.id LEFT JOIN users_roles_association ur ON ur.office_id = r.id and ur.user_id = $1
			WHERE g.system = 1 and r.id in (0'.implode(', ', Security::getManagedOfficeIds()).') order by r.`order`, 2';
		$res = mysqli_query_params($sql, $user_id) or die(mysqli_query_error());
		while($r = $res->fetch_assoc())
			$data['offices'][] = $r; 
		$res->close();
		return array('success' => true, 'data' => $data);
	}

	public function saveAccessData($params){//added to tests //CHECKED
		if(!Security::canManage()) throw new Exception(L\Access_denied);
		$params = (Array)$params;
		@$user_id = $this->extractId($params['id']);
		
		//analize offices
		$managed_office_ids = Security::getManagedOfficeIds();
		$keep_offices = array();
		if(!empty($params['offices']))
		foreach($params['offices'] as $r){
			if(!in_array($r->id, $managed_office_ids)) continue;
			$role_id = 0;
			if($r->manager == 1){
				$role_id = 2;
				array_push($keep_offices, $r->id);
			}elseif($r->user == 1){
				$role_id = 4;
				array_push($keep_offices, $r->id);
			}
			if($this->validRole($role_id))
				mysqli_query_params('insert into users_roles_association (user_id, office_id, role_id, `active`, cid) values($1, $2, $3, 1, $4) on duplicate key update role_id = $3, active = 1, uid = $4', Array($user_id, $r->id, $role_id, $_SESSION['user']['id'])) or die(mysqli_query_error());
		}
		mysqli_query_params('delete from users_roles_association where user_id = $1 and office_id not in (0'.implode(',', $keep_offices).') and office_id in (0'.implode(',', $managed_office_ids).')', $params['id']) or die(mysqli_query_error());
		//mysqli_query_params('CALL p_estimate_user_effective_access($1)', $user_id) or die(mysqli_query_error());
		return Array('success' => true);
	}	

	public function saveUserData($params){	////CHECKED
		$rez = Array('success' => true);
		$data = json_decode($params['data']);
		
		if(!Security::isAdmin() && !Security::isUsersOwner($data->id) && !($_SESSION['user']['id'] == $data->id)) throw new Exception(L\Access_denied);
		require_once 'VerticalEditGrid.php';
		VerticalEditGrid::saveData('users', $data);
		
		/* if updating current logged user then checking if interface params have changed */
		$interface_params_changed = false;
		if($data->id == $_SESSION['user']['id']){
			$res = mysqli_query_params('select '.$_SESSION['languages']['string'].', language_id, short_date_format, long_date_format from users u where id = $1 ', $data->id) or die(mysqli_query_error());
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
	
	private function updateUserEmails($user_id){
		$emails = array();
		$res = mysqli_query_params('SELECT ud.value FROM templates t  JOIN templates_structure ts ON ts.template_id = t.id AND ts.name = \'email\' JOIN users_data ud ON ts.id = ud.field_id and ud.user_id = $1 WHERE t.`type` = 6', $user_id) or die(mysqli_query_error());
		while($r = $res->fetch_row()) if(!empty($r[0])) $emails[] = $r[0];
		$res->close();
		$emails = empty($emails) ? null : implode(', ', $emails);
		mysqli_query_params('update users set email = $1 where id = $2', array($emails, $user_id)) or die(mysqli_query_error());
	}
	
	public function addUser($params){//added to tests //CHECKED
		if(!Security::canManage()) throw new Exception(L\Access_denied);
		////params: name, office_id, role_id
		$rez = Array('success' => false, 'msg' => L\Missing_required_fields);
		$params->name = trim($params->name);
		if(empty($params->name) || empty($params->password) || (empty($params->confirm_password) || ($params->password != $params->confirm_password))) return $rez;
		$user_id = 0;
		/*check user existance, if user already exists but is deleted then its record wiil be used for new user */
		$res = mysqli_query_params("select id from users where name = $1 and deleted = 0", $params->name) or die(mysqli_query_error());
		if($r = $res->fetch_row()) throw new Exception(L\User_exists);
		$res->close();
		/*end of check user existance */
		
		mysqli_query_params('insert into users (`name`, `pid`, `password`, language_id, cdate, uid, email) values($1, $2, MD5(CONCAT(\'aero\', $3)), $4, CURRENT_TIMESTAMP, $2, $5) '.
			'on duplicate key update id = last_insert_id(id), `name` = $1, `pid` = $2, `password` = MD5(CONCAT(\'aero\', $3))'.
			',last_login = null, login_successful = null, login_from_ip = null, last_logout = null, last_action_time = null, enabled = 1, visible_in_reports = 1, cdate = CURRENT_TIMESTAMP, deleted = 0, language_id = $4, uid = $2, cdate = CURRENT_TIMESTAMP'
			,array($params->name, $_SESSION['user']['id'], $params->password, $_SESSION['languages']['per_abrev'][$GLOBALS['CB_LANGUAGE']]['id'], $params->email ) ) or die(mysqli_query_error());
		if($user_id = last_insert_id()){
			$rez = Array('success' => true, 'data' => Array('id' => $user_id));
			$params->id = $user_id;
		}
		
		mysqli_query_params('delete from users_data where user_id = $1',  $user_id) or die(mysqli_query_error());
		
		$res = mysqli_query_params('select id from templates where `type` = 6') or die(mysqli_query_error());
		if($r = $res->fetch_row()) $params->template_id = $r[0];
		$params->sex = null;
		//$params->email = '';
		
		require_once 'VerticalEditGrid.php';
		VerticalEditGrid::addFormData('users', $params);
		
		$res = mysqli_query_params('select tag_id from users where id = $1', $user_id) or die(mysqli_query_error());
		if($r = $res->fetch_row()) $rez['data']['tag_id'] = $r[0];
		$res->close();
		/* in case it was a deleted user we delete all old acceses */
		mysqli_query_params('delete from users_roles_association where user_id = $1', $user_id) or die(mysqli_query_error());
		mysqli_query_params('delete from cases_rights where tag_id = $1', $rez['data']['tag_id']) or die(mysqli_query_error());
		/* end of in case it was a deleted user we delete all old acceses */
		if( isset($params->office_id) && is_numeric($params->office_id) && ( in_array($params->office_id, Security::getManagedOfficeIds()) ) ){
			if(!$this->validRole($params->role_id)) throw new Exception(L\Wrong_input_data);
			mysqli_query_params('insert into users_roles_association (user_id, office_id, role_id, cid, uid) values($1, $2, $3, $4, $4) on duplicate key update role_id = $3, active = 1, uid = $4', Array($user_id, $params->office_id, $params->role_id, $_SESSION['user']['id'])) or die(mysqli_query_error());
			$rez['data']['office_id'] = $params->office_id;
			//mysqli_query_params('CALL p_estimate_user_effective_access($1)', $user_id) or die(mysqli_query_error());
		}else $rez['data']['office_id'] = 0;
		
		$this->updateUserEmails($user_id);
		return $rez;
	}

	public function addOffice($params){//added to tests //CHECKED
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
	public function deleteOffice($office_id){//added to tests //CHECKED
		if(!Security::isAdmin()) throw new Exception(L\Access_denied);
		$office_id = $this->extractId($office_id);
		/* selecting currently associated users to this office to estimate their access after deletition */
		$user_ids = array();
		$res = mysqli_query_params('select user_id from users_roles_association where office_id = $1', $office_id) or die(mysqli_query_error());
		while($r = $res->fetch_row()) $user_ids[] = $r[0];
		$res->close();
		mysqli_query_params('delete from users_roles_association where office_id = $1', $office_id) or die(mysqli_query_error());
		/* end of selecting currently associated users to this office to estimate their access after deletition */
		mysqli_query_params('delete from t using tags t JOIN tag_groups__tags_result tr ON t.id = tr.tag_id JOIN tag_groups g ON tr.tags_group_id = g.id AND g.system = 1 where t.id = $1', $office_id) or die(mysqli_query_error());
		//foreach($user_ids as $id) 	mysqli_query_params('CALL p_estimate_user_effective_access($1)', $id) or die(mysqli_query_error());
		return Array('success' => true);
	}/**/

	public function associate($user_id, $office_id){//added to tests //CHECKED
		if(!Security::canManage()) throw new Exception(L\No_manage_access_for_office);
		$user_id = $this->extractId($user_id);
		$office_id = $this->extractId($office_id);
		if(!in_array($office_id, Security::getManagedOfficeIds())) throw new Exception(L\No_manage_access_for_office);
		
		$res = mysqli_query_params('select user_id from users_roles_association where user_id = $1 and office_id = $2', Array($user_id, $office_id) ) or die(mysqli_query_error());
		if($r = $res->fetch_row()) throw new Exception(L\UserAlreadyInOffice);
		$res->close();
		mysqli_query_params('insert into users_roles_association (user_id, role_id, office_id, cid) values ($1, 4, $2, $3)', Array($user_id, $office_id, $_SESSION['user']['id']) ) or die(mysqli_query_error());
		//mysqli_query_params('CALL p_estimate_user_effective_access($1)', $user_id) or die(mysqli_query_error());
		return Array('success' => true);
	}

	public function deassociate($user_id, $office_id){//added to tests //CHECKED
		if(!Security::canManage()) throw new Exception(L\Access_denied);
		$user_id = $this->extractId($user_id);
		$office_id = $this->extractId($office_id);
		if(!in_array($office_id, Security::getManagedOfficeIds())) throw new Exception(L\No_manage_access_for_office);
		$res = mysqli_query_params('delete from users_roles_association where user_id = $1 and office_id = $2', Array($user_id, $office_id) ) or die(mysqli_query_error());
		//mysqli_query_params('CALL p_estimate_user_effective_access($1)', $user_id) or die(mysqli_query_error());
		
		$outOfOffice = true;
		$res = mysqli_query_params('select office_id from users_roles_association where user_id = $1 limit 1', $user_id ) or die(mysqli_query_error()); //return if the user is associated to another office, otherwize it shoul be added to Users out of office folder
		if($r = $res->fetch_row()) $outOfOffice = false;
		return Array('success' => true, 'outOfOffice' => $outOfOffice);
	}

	public function deleteUser($user_id){//added to tests //CHECKED
		if(!Security::canManage()) throw new Exception(L\Access_denied);
		$user_id = $this->extractId($user_id);
		$res = mysqli_query_params('update users set deleted = 1, did = $2 where id = $1 and ((pid = $2) or (1 = $3))', array($user_id, $_SESSION['user']['id'], Security::getUserRole()) ) or die(mysqli_query_error());
		return Array('success' => affected_rows() ? true : false, 'data' => array($user_id, $_SESSION['user']['id'], Security::getUserRole()));
	}

	public function changeRole($user_id, $office_id, $role_id){//added to tests //NOT USED IN INTERFACE
		if(!Security::canManage()) throw new Exception(L\Access_denied);
		$user_id = $this->extractId($user_id);
		$office_id = $this->extractId($office_id);
		if(!$this->validRole($role_id)) throw new Exception(L\Wrong_input_data);
		if(!in_array($office_id, Security::getManagedOfficeIds())) throw new Exception(L\No_manage_access_for_office);
		if($role_id < Security::getUserRole()) throw new Exception(L\Cannot_give_higher_access);
		$res = mysqli_query_params('update users_roles_association set role_id = $1, uid = $4 where user_id = $2 and office_id = $3', Array($role_id, $user_id, $office_id, $_SESSION['user']['id']) ) or die(mysqli_query_error());
		//mysqli_query_params('CALL p_estimate_user_effective_access($1)', $user_id) or die(mysqli_query_error());
		return Array('success' => true);
	}

	public function setRoleActive($user_id, $office_id, $active){//added to tests //NOT USED IN INTERFACE
		if(!Security::canManage()) throw new Exception(L\Access_denied);
		$user_id = $this->extractId($user_id);
		$office_id = $this->extractId($office_id);
		if(!in_array($office_id, Security::getManagedOfficeIds())) throw new Exception(L\No_manage_access_for_office);
		mysqli_query_params('update users_roles_association set `active` = $1, uid = $4 where user_id = $2 and office_id = $3', Array($active, $user_id, $office_id, $_SESSION['user']['id']) ) or die(mysqli_query_error());
		//mysqli_query_params('CALL p_estimate_user_effective_access($1)', $user_id) or die(mysqli_query_error());
		return Array('success' => true);
	}
	public function changePassword($p){ //CHECKED
		//var_dump($p);
		/* passord could be changed by: admin, user owner, user himself */
		if(empty($p['password']) || ($p['password'] != $p['confirmpassword'])) throw new exception(L\Wrong_input_data);
		//echo $p['password'].$p['confirmpassword'];
		$user_id = $this->extractId($p['id']);

		/* check for old password if users changes password for himself */
		if($_SESSION['user']['id'] == $user_id){
			$res = mysqli_query_params('select id from users where id = $1 and `password` = MD5(CONCAT(\'aero\', $2))', array($user_id, $p['currentpassword'])) or die(mysqli_query_error());
			if(!$res->fetch_row()) throw new exception(L\WrongCurrentPassword);
			$res->close();
		}
		/* end of check for old password if users changes password for himself */

		if(!Security::isAdmin() && !Security::isUsersOwner($user_id) && !($_SESSION['user']['id'] == $user_id)) throw new Exception(L\Access_denied);
		
		mysqli_query_params('update users set `password` = MD5(CONCAT(\'aero\', $2)), uid = $3 where id = $1', array($user_id, $p['password'], $_SESSION['user']['id'])) or die(mysqli_query_error());
		return array('success' => true);
	}
	public function changeUsername($p){ //CHECKED
		/* username could be changed by: admin or user owner */
		$name = trim(strtolower($p->name));
		$matches = preg_match('/^[a-z0-9\._]+$/', $name);
		if(empty($p->name) || empty($matches) ) throw new exception(L\Wrong_input_data);
		
		$user_id = $this->extractId($p->id);

		if(!Security::isAdmin() && !Security::isUsersOwner($user_id)) throw new Exception(L\Access_denied);
		
		mysqli_query_params('update users set `name` = $2, uid = $3 where id = $1', array($user_id, $name, $_SESSION['user']['id'])) or die(mysqli_query_error());
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
		$res = mysqli_query_params('select id, tag_id, name, '.$_SESSION['languages']['string'].', sex, email, language_id, short_date_format, long_date_format from users where enabled = 1 and deleted = 0 and id = $1', $id) or die(mysqli_query_error());
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
	private function getNodeChildren($node_kind, $node_id, $with_read_access = false, $case_id = 0){ //OK
		$rez = array();
		switch($node_kind){
			case 'root':
				$sql = $with_read_access ? ',(SELECT 1 FROM cases_rights WHERE case_id = $2 AND tag_id = o.id AND access = 1) checked ' : '';
				//kind: 1 - user, 2 - office, 3 - no office, 4 - other offices(virtual node)
				$sql = 'SELECT o.id, 2 `kind`, o.l'.UL_ID().' `text`, \'icon-office\' `iconCls`, 0 expanded'.
						',(select min(role_id) from users_roles_association where user_id = $1 and ((office_id = 0) or(office_id = o.id))) `role_id` '.$sql.
						',(select count(*) from users_roles_association ura join users u on ura.user_id = u.id and u.deleted = 0 and u.enabled = 1 where ura.office_id = o.id and ura.active = 1) `users` '.
						'FROM tag_groups__tags_result tr join tags o on tr.tag_id = o.id '.
						'where tr.tags_group_id = 1 '.
						'UNION SELECT 0, 3, \''.l\Users_out_of_office.'\', \'icon-no-office\', false, 0'.($with_read_access ? ', 0' : '').
						',(SELECT count(*) FROM users where deleted = 0 and enabled = 1 and id not in (select distinct user_id from users_roles_association where office_id <> 0 or role_id = 1)) `users` order by kind, 3';
				$res = mysqli_query_params($sql, array($_SESSION['user']['id'], $case_id)) or die(mysqli_query_error());
				$other_offices = array();
				while($r = $res->fetch_assoc()){
					$r['nid'] = 'o-'.$r['id'];
					$r['expanded'] = ($r['expanded'] == 1);
					$r['loaded'] = empty($r['users']);
					if($r['id'] == 0) $r['draggable'] = false;
					$r['cls'] = 'fwB pt10';
					if($with_read_access){
						$r['checked'] = !empty($r['checked']);
						if($r['id'] == 0) unset($r['checked']);
					}
					if( (($r['role_id'] <=2) && ($r['role_id'] > 0)) || ($r['id'] == 0)) $rez[] = $r; else $other_offices[] = $r;
				}
				$res->close(); 
				if(!empty($other_offices))
					array_push($rez, array('nid' => 'w-0', 'kind' => 4, 'text' => L\Other_offices, 'loaded' => false, 'expanded' => false, 'children' => $other_offices, 'cls' => 'pt10', 'iconCls' => 'icon-folder'));
				break;
			case 'o':
				$gid = intval($node_id);
				if($gid == '0') {
					$sql = $with_read_access ? ',(SELECT 1 FROM cases_rights WHERE case_id = $2 AND user_id = u.tag_id AND access = 1) checked ' : '';
					$sql = 	'SELECT u.id, u.tag_id, u.pid, 1 `kind`, 0 `role_id`, u.`name`, u.l'.UL_ID().', sex, \'icon-user-gray\' iconCls'.$sql.
							' FROM users u where u.deleted = 0 and u.id not in (select distinct user_id from users_roles_association where office_id <> 0 or role_id = 1)';
				}else{
					$sql = $with_read_access ? ',(SELECT 1 FROM cases_rights WHERE case_id = $2 AND tag_id = u.tag_id AND access = 1) checked ' : '';
					$sql = 'SELECT u.id, u.tag_id, u.pid, 1 `kind`, ur.role_id, u.`name`, u.l'.UL_ID().', sex, ur.`active`'.$sql.
						' FROM users u JOIN users_roles_association ur ON u.id = ur.user_id AND ur.office_id = $1'.
						' where u.deleted = 0 and role_id > 1 order by role_id, u.name';
				}
				$res2 = mysqli_query_params($sql, array($gid, $case_id)) or die(mysqli_query_error());
				while($r2 = $res2->fetch_assoc()){
					$r2['nid'] = 'u-'.$r2['tag_id'];
					$r2['leaf'] = true;
					$r2['text'] =  (empty($r2['l'.UL_ID()])? $r2['name']: $r2['l'.UL_ID()]);
					if($with_read_access) $r2['checked'] = !empty($r2['checked']);
					$rez[] = $r2;
				}
				$res2->close();
				break;
		}
		return $rez;
	}
}
?>