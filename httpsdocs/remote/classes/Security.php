<?php 
class Security {
	/* groups methods */
	public function getUserGroups(){
		$rez = array( 'success' => true, 'data' => array() );
		$sql = 'select id, name, l'.UL_ID().' `title`, `system`, `enabled` from users_groups where type = 1 order by 3';
		$res = mysqli_query_params($sql) or die(mysqli_query_error());
		while($r = $res->fetch_assoc()) $rez['data'][] = $r;
		$res->close();
		return $rez;
	}

	public function createUserGroup($p){

		return array( 'success' => true, 'data' => array() );
	}

	public function updateUserGroup($p){

		return array( 'success' => true, 'data' => array() );
	}

	public function destroyUserGroup($p){

		return array( 'success' => true, 'data' => array() );
	}
	/* end of groups methods */
	
	public function searchUserGroups($p){
		/*{"editor":"form","source":"users","renderer":"listObjIcons","autoLoad":true,"multiValued":true,"maxInstances":1,"showIn":"grid","query":"test","objectId":"237","path":"/1"}*/
		$rez = array('success' => true, 'data' => array());
		
		$where = array();
		$params = array();

		if(!empty($p->source)){
			switch($p->source){
				case 'users': $where[] = '`type` = 2' ; break;
				case 'groups': $where[] = '`type` = 1' ; break;
			}
		}elseif(!empty($p->types)){
			$a = toNumericArray($p->types);
			if(!empty($a)) $where[] = '`type` in ('.implode(',', $a).')';
		}

		if(!empty($p->query)){
			$where[] = 'searchField like $1'; 
			$params[] = ' %'.trim($p->query).'% ';
		}

		$sql = 'select id, l'.UL_ID().' `name`, `system`, `enabled`, `type`, `sex` from users_groups where deleted = 0 '.( empty($where) ? '' : ' and '.implode(' and ', $where) ).' order by `type`, 2 limit 50';
		$res = mysqli_query_params($sql, $params) or die(mysqli_query_error());
		while($r = $res->fetch_assoc()){
			$r['iconCls'] = ($r['type'] == 1) ? 'icon-group' : 'icon-user-'.$r['sex'];
			unset($r['type']);
			unset($r['sex']);
			$rez['data'][] = $r;
		}
		$res->close();
		return $rez;
	}
	
	/* objects acl methods*/
	public function getObjectAcl($p){
		$rez = array( 'success' => true, 'data' => array(), 'name' => '');
		if(!is_numeric($p->id)) return $rez;
		
		/* set object title, path and inheriting access ids path*/
		$obj_ids = array();
		$sql = 'select f_get_tree_path($1) `path`, name, f_get_tree_inherit_ids(id) `obj_ids` from tree where id = $1';
		$res = mysqli_query_params($sql, $p->id) or die(mysqli_query_error());
		if($r = $res->fetch_assoc()){
			$rez['path'] = Path::replaceCustomNames($r['path']);
			$rez['name'] = Path::replaceCustomNames($r['name']);
			$obj_ids = explode('/', substr($r['obj_ids'], 1));
		}
		$res->close();
		/* end of set object title and path*/

		/* get the full set of access credentials(users and/or groups) including inherited from parents */
		$lid = function_exists('UL_ID') ? UL_ID(): 1;
		$sql = 'select distinct u.id, u.l'.$lid.' `name`, u.`system`, u.`enabled`, u.`type`, u.`sex` from tree_acl a '.
			'join users_groups u on a.user_group_id = u.id where a.node_id in('.implode(',', $obj_ids).') order by u.`type`, 2';
		$res = mysqli_query_params($sql, $p->id) or die(mysqli_query_error());
		while($r = $res->fetch_assoc()){
			$r['iconCls'] = ($r['type'] == 1) ? 'icon-group' : 'icon-user-'.$r['sex'];
			// unset($r['type']); // used internaly by setSolrAccess function
			unset($r['sex']);
			$access = $this->getUserGroupAccessForObject($r['id'], $p->id);
			$r['allow'] = implode(',', $access[0]);
			$r['deny'] = implode(',', $access[1]);
			$rez['data'][] = $r;
		}
		$res->close();
		/* end of get the full set of access credentials(users and/or groups) including inherited from parents */

		return $rez;
	}
	/**
	* getUserGroupAccessForObject - returns estimated bidimentional array of access bits from acl for a user or a group
	* Each bit can have the following values:
	*	-2 - deny, inherited from a parent
	*	-1 - deny, directly set for the object
	*	 0 - not set 
	*	 1 - allow, directly set for the object
	*	 2 - allow, inherited from a parent
	*
	*	Permission Precedence: 
	*		Explicit Deny
	*		Explicit Allow
	*		Inherited Deny
	*		Inherited allow
	*/
	public function getUserGroupAccessForObject($user_group_id, $object_id){
		//0 List Folder/Read Data
		//1 Create Folders
		//2 Create Files
		//3 Create Actions
		//4 Create Tasks
		//5 Read
		//6 Write
		//7 Delete child nodes
		//8 Delete
		//9 Change permissions
		//10 Take Ownership
		//11 Download
		$rez = array( array_fill(0,12, 0), array_fill(0,12, 0) );

		/* getting object ids that have inherit set to true */
		$sql = 'select f_get_tree_inherit_ids(id) `ids` from tree where id = $1';
		$res = mysqli_query_params($sql, $object_id) or die(mysqli_query_error());
		$ids = array();
		if($r = $res->fetch_assoc()) $ids = explode('/', substr($r['ids'], 1));
		$res->close();

		/* reversing array for iterations from object to top parent */
		$ids = array_reverse($ids);

		$user_group_ids = array($user_group_id);
		/* getting group ids where passed $user_group_id is a member*/
		$sql = 'select distinct group_id from users_groups_association where user_id = $1'.
			' union select id from users_groups where `type` = 1 and `system` = 1 and name = \'everyone\''; // adding everyone group to our group ids
		$res = mysqli_query_params($sql, $user_group_id) or die(mysqli_query_error());
		while($r = $res->fetch_row()) if(!in_array($r[0], $user_group_ids)) $user_group_ids[] = $r[0];
		$res->close();
		/* end of getting group ids where passed $user_group_id is a member*/
		
		$acl_order = array_flip($ids);
		$acl = array();
		// selecting access list set for our path ids
		$sql = 'select node_id, user_group_id, allow, deny from tree_acl where node_id in ('.implode(',', $ids).') and user_group_id in ('.implode(',', $user_group_ids).')';
		$res = mysqli_query_params($sql, array()) or die(mysqli_query_error());
		while($r = $res->fetch_assoc())
			$acl[$acl_order[$r['node_id']]][$r['user_group_id']] = array($r['allow'], $r['deny']);
		$res->close();
		/* now iterating the $acl table and determine final set of bits/**/
		$set_bits = 0;
		$i=0;
		ksort($acl, SORT_NUMERIC);
		reset($acl);
		while( ( current($acl) !== false ) && ($set_bits < 12) ){
		 	$i = key($acl);
		 	$inherited = ($i > 0);
			/* check firstly if direct access is specified for passed user_group_id */
			if(!empty($acl[$i][$user_group_id])){
				$deny = intval($acl[$i][$user_group_id][1]);
				for ($j=0; $j < sizeof($rez[1]); $j++){ 
					if( empty($rez[1][$j]) && ($deny & 1) ){
						$rez[1][$j] = -(1 + $inherited); 
						$set_bits++;
					}
					$deny = $deny >> 1;
				}
				$allow = intval($acl[$i][$user_group_id][0]);
				for ($j=0; $j < sizeof($rez[0]); $j++){ 
					// if($user_group_id == 18) echo $rez[0][$j].':'.($allow &1)."\n";
					if( empty($rez[0][$j]) && ($allow & 1) ){
						$rez[0][$j] = (1 + $inherited);
						$set_bits++;
					}
					$allow = $allow >> 1;
				}
					
			}
			if(!empty($acl[$i]))
			foreach($acl[$i] as $key => $value) {
				if($key == $user_group_id) continue;
				$deny = intval($value[1]);
				for ($j=0; $j < sizeof($rez[1]); $j++){ 
					if( empty($rez[1][$j]) && ($deny & 1) ){
						$rez[1][$j] = -(1 + $inherited); 
						$set_bits++;
					}
					$deny = $deny >> 1;
				}
				$allow = intval($value[0]);
				for ($j=0; $j < sizeof($rez[0]); $j++){ 
					// if($user_group_id == 18) echo $rez[0][$j].':'.($allow &1)."\n";
					if( empty($rez[0][$j]) && ($allow & 1) ){
						$rez[0][$j] = (1 + $inherited);
						$set_bits++;
					}
					$allow = $allow >> 1;
				}
			}
			next($acl);
		}
					
		return $rez;
	}

	// transferred to client side just for display purpose
	// public function getAccessGroups($accessArray){
	// 	$accessGroups = array(
	// 		'FullControl' 	=> Array( 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1 )
	// 		,'Modify' 	=> Array( 1, 1, 1, 1, 1, 1, 1, 0, 1, 0, 0, 1 )
	// 		,'Read' 	=> Array( 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 1 )
	// 		,'Write' 	=> Array( 0, 1, 1, 1, 1, 0, 1, 0, 0, 0, 0, 0 )
	// 	);
	// 	$rez = array();
	// 	foreach($accessGroups as $g => $gv){ 
	// 		$lastBit = null;
	// 		$bitsMatch = true;
	// 		$i = 0;
	// 		while( ($i < sizeof($accessArray) ) && $bitsMatch){
	// 			if(is_null($lastBit) || (($gv[$i] == 1) && ($accessArray[$i] == $lastBit))  ){
	// 				$bitsMatch = true;
	// 				$lastBit = $accessArray[$i];
	// 			}else $bitsMatch = false;
	// 			$i++;
	// 		}
	// 		$rez[$g] = $bitsMatch ? $lastBit : 0; 
	// 	}
	// 	return $rez;
	// }

	public function addObjectAccess($p){
		$rez = array('success' => true, 'data' => array());
		if(empty($p->data)) return $rez;
		mysqli_query_params('insert into tree_acl (node_id, user_group_id, cid, uid) values ($1, $2, $3, $3) on duplicate key update id = last_insert_id(id), uid = $3', array($p->id, $p->data->id, $_SESSION['user']['id']) ) or die(mysqli_query_error());
		
		$rez['data'][] = $p->data;
		SolrClient::RunCron();
		return $rez;
	}

	public function updateObjectAccess($p){
		$allow = explode(',', $p->data->allow);
		$deny = explode(',', $p->data->deny);
		for ($i=0; $i < 12; $i++) { 
			$allow[$i] = ($allow[$i] == 1) ? '1' : '0';
			$deny[$i] = ($deny[$i] == -1) ? '1' : '0';
		}
		$allow = array_reverse($allow);
		$deny = array_reverse($deny);
		$allow = bindec( implode('', $allow) ) ;
		$deny = bindec( implode('', $deny) );
		$sql = 'insert into tree_acl (node_id, user_group_id, allow, deny, cid) values($1, $2, $3, $4, $5) on duplicate key update allow = $3, deny = $4, uid = $5, udate = CURRENT_TIMESTAMP';
		mysqli_query_params($sql, array($p->id, $p->data->id, $allow, $deny, $_SESSION['user']['id']) ) or die(mysqli_query_error());
		SolrClient::RunCron();
		return array('succes' => true, 'data' => $p->data );
	}
	public function destroyObjectAccess($p){
		if(empty($p->data)) return;
		mysqli_query_params('delete from tree_acl where node_id = $1 and user_group_id = $2', array($p->id, $p->data)) or die(mysqli_query_error());
		SolrClient::RunCron();
		return array('success' => true, 'data'=> array());
	}

	/* end of objects acl methods*/
	
	function setObjectSolrAccessFields(&$objectRecord){
		
		$acl = $this->getObjectAcl((Object)array('id' => $objectRecord['id']));
		$acl = $acl['data'];
		
		/* iterate acl and select user list for every group 
			users will be stored in a array that will determine groups from which their access depends (if no directly the user is specified)
			if the user is dependent only by one group then his access is set to groups (already calculated) access
			if the user belongs to more groups then we can look if he has read access for all groups and no deny specified, if so then user is granted read
			Otherwise (when user belong to more than one group - the access is calculated for this user)

			when everyone group is present in access list (allow or deny) then only it will be specified in corresponding field without any other users
		*/
		$everyoneGroupId = $this->EveryoneGroupId();
		$users = array();
		/*
		users:
			user_id [explicit_user_access, explicit_group_access, inherited_user_access, inherited_group_access]
		*/
		/* collecting accesses for users */
		foreach($acl as $access){
			$allow = explode(',',$access['allow']);
			$deny = explode(',',$access['deny']);
			if($deny[5] < 0){
				if( ($access['type'] == 2) || ($access['id'] == $everyoneGroupId)){
					$idx = ($deny[5] == -1) ? 0 : 2;
					$users[$access['id']][$idx] = -1;
				}else{
					$idx = ($deny[5] == -1) ? 1 : 3;
					$groupUsers = $this->getGroupUserIds($access['id']);
					foreach($groupUsers as $uid) $users[$uid][$idx] = -1;
				}
			}elseif($allow[5] > 0){
				if( ($access['type'] == 2) || ($access['id'] == $everyoneGroupId)){
					$idx = ($allow[5] == 1) ? 0 : 2;
					if(empty($users[$access['id']][$idx])) $users[$access['id']][$idx] = 1;
				}else{
					$idx = ($deny[5] == -1) ? 1 : 3;
					$groupUsers = $this->getGroupUserIds($access['id']);
					foreach($groupUsers as $uid) 
						if(empty($users[$uid][$idx]))$users[$uid][$idx] = 1;
				}
			}
		}
		/* end of collecting accesses for users (including everyone group if present) */
		
		/* grouping  user ids in allow and deny sets /**/
		$allow_users = array();
		$deny_users = array();
		foreach($users as $uid => $ua){
			$access = array_shift($ua);
			while(empty($access) && !empty($ua)) $access = array_shift($ua);
			if($access < 0) $deny_users[] = $uid;
			elseif($access > 0) $allow_users[] = $uid;
		}
		/* selecting node owner and store him into allow and remove from deny */
		$sql = 'select oid from tree where id = $1';
		$res = mysqli_query_params($sql, $objectRecord['id']) or die(mysqli_query_error());
		if(($r = $res->fetch_row()) && !empty($r[0])){
			if(!in_array($r[0], $allow_users)) $allow_users[] = $r[0];
			$deny_users = array_diff($deny_users, array($r[0])); 
		}
		$res->close();
		/* end of selecting node owner and store him into allow and remove from deny */

		if(in_array($everyoneGroupId, $allow_users)) $allow_users = array($everyoneGroupId);
		if(in_array($everyoneGroupId, $deny_users)) $deny_users = array();//array($everyoneGroupId);

		if(!empty($allow_users)) $objectRecord['allow_user_ids'] = $allow_users;
		if(!empty($deny_users)) $objectRecord['deny_user_ids'] = $deny_users;

		// foreach($acl as $access){
		// 	$allow = explode(',',$access['allow']);
		// 	$deny = explode(',',$access['deny']);
		// 	if($deny[5] < 0){
		// 		if($deny[5] == -2){
		// 			$allow = ($allow[5] == 1);
		// 			$deny = !$allow;
		// 		}else{
		// 			$allow = false;
		// 			$deny = true;
		// 		}
		// 	}else{
		// 		$allow = ($allow[5] > 0);
		// 		$deny = false;
		// 	}

		// 	if( ($access['id'] == $everyoneGroupId) && $access['allow'])
		// 	if($access['type'] == 1){//group

		// 	}else{
		// 		if($allow) $users['allow'][] = $access['id'];
		// 		if($deny) $users['deny'][] = $access['id'];
		// 	}

		// }


		// /* selecting user ids that have access specified for that object (including everyone object) */
		// $sql = 'select group_id from users_groups_association where user_id = $1'.
		// 	' union select id from users_groups where `type` = 1 and `system` = 1 and name = \'everyone\''; // adding everyone group to our group ids
		// $res = mysqli_query_params($sql, $user_group_id) or die(mysqli_query_error());
		// while($r = $res->fetch_row()) $user_group_ids[] = $r[0];
		// $res->close();

		// /* end of selecting user ids that have access specified for that object (including everyone object) */

	}
	static function EveryoneGroupId(){
		if(isset($GLOBALS['EVERYONE_GROUP_ID'])) return $GLOBALS['EVERYONE_GROUP_ID'];
		$GLOBALS['EVERYONE_GROUP_ID'] = null;
		$sql = "select id from users_groups where `type` = 1 and `system` = 1 and name = 'everyone'";
		$res = mysqli_query_params($sql) or die(mysqli_query_error());
		if($r = $res->fetch_row()) $GLOBALS['EVERYONE_GROUP_ID'] = $r[0];
		$res->close();
		return $GLOBALS['EVERYONE_GROUP_ID'];
	} 
	public function getGroupUserIds($groupId){
		$rez = array();
		$sql = 'select user_id from users_groups_association where group_id = $1';
		$res = mysqli_query_params($sql, $groupId) or die(mysqli_query_error());
		while($r = $res->fetch_row()) $rez[] = $r[0];
		$res->close();
		return $rez;
	}
	/* ----------------------------------------------------  OLD METHODS ------------------------------------------ */

	static function getCaseUsersRole($case_id, $user_id = false){
		return 2;
		/* return users role for specified case. If no user is specified then current sessions user is used  */
		// $rez = false;
		// if($user_id == false) $user_id = $_SESSION['user']['id'];
		// $res = mysqli_query_params( 'select access from cases_rights_effective where case_id = $1 and user_id = $2', array($case_id, $user_id) ) or die(mysqli_query_error());
		// while( $r = $res->fetch_row() ) $rez = $r[0];
		// $res->close();
		// if(!$rez) $rez = Security::isAdmin() ? 1 : false ;
		// return $rez;
	}
	static function getUserRole($user_id = false){
		return false;
		/* return users role. If no user is specified then current sessions user is used  */
		$rez = false;
		if($user_id == false) $user_id = $_SESSION['user']['id'];
		$sql = 'select min(role_id) from users_groups_association where user_id = $1 and active = 1';
		// $sql = 'SELECT MIN(role_id) FROM '.
		// 	'(SELECT MIN(role_id) `role_id` FROM users_groups_association WHERE user_id = $1 AND active = 1 '.
		// 	'UNION '.
		// 	'SELECT MIN(access) `role_id` FROM cases_rights cr JOIN users u ON cr.tag_id = u.tag_id WHERE u.id = $1 AND cr.valid = 1) t';
		$sql = 'SELECT MIN(role_id) `role_id` FROM users_groups_association WHERE user_id = $1 AND active = 1';
		$res = mysqli_query_params( $sql, array($user_id) ) or die(mysqli_query_error());
		while( $r = $res->fetch_row() ) $rez = $r[0];
		$res->close();
		return $rez;
	}
	static function canManageCase($case_id, $user_id = false){
		return true;
		// $role = Security::getCaseUsersRole($case_id, $user_id);
		// return ( (!empty($role)) && ($role < 3) );
	}
	static function canWriteCase($case_id, $user_id = false){
		$role = Security::getCaseUsersRole($case_id, $user_id);
		return ( (!empty($role)) && ($role < 4) );
	}
	static function canReadCase($case_id, $user_id = false){
		return true;
		// $role = Security::getCaseUsersRole($case_id, $user_id);
		// return ( (!empty($role)) && ($role < 5) );
	}
	static function checkCaseReadAction($case_id, $user_id = false){
		return true;
		// if(!is_numeric($case_id)) throw new Exception(L\Wrong_input_data);
		// // SECURITY: check if case is opened by the user 
		// if(!Security::checkIfCaseOpened($case_id, $user_id)) throw new Exception(L\case_not_oppened);
		// // end of SECURITY: check if case is opened by the user 
		// // SECURITY: check if user has read access to case
		// if(!Security::canReadCase($case_id, $user_id)) throw new Exception(L\Access_denied);
		// // end of SECURITY: check if user has read access to this case
		// return true;
	}
	static function checkCaseWriteAction($case_id, $user_id = false){
		return true;
		// if(!is_numeric($case_id)) throw new Exception(L\Wrong_input_data);
		// // SECURITY: check if case is opened by the user 
		// if(!Security::checkIfCaseOpened($case_id, $user_id)) throw new Exception(L\case_not_oppened);
		// // end of SECURITY: check if case is opened by the user 
		// // SECURITY: check if user has manage access to case
		// if(!Security::canWriteCase($case_id, $user_id)) throw new Exception(L\Access_denied);
		// // end of SECURITY: check if user has manage access to this case
		// return true;
	}
	
	static function getManagedOfficeIds(){
		$rez = array();
		$mr = Security::getManagedOffices();
		foreach($mr['data'] as $v) $rez[] = $v['id'];
		return $rez;
	}

	static function getManagedOffices($p = null){
		$rez = Array('success' => true, 'data' => array());
		return $rez;
		// if($p && isset($p->withNoOffice) && $p->withNoOffice) $rez['data'][] = array('id' => 0, 'name' => L\Out_of_office);
		// $sql = 'SELECT DISTINCT th.id, th.l'.UL_ID().' `name` '.
		// 	'FROM users_groups_association ura '.
		// 	'join users u on u.id = ura.user_id AND u.enabled = 1 AND u.deleted = 0 '.
		// 	'join tag_groups__tags_result tr on tr.tags_group_id = $2 and (ura.office_id IN (tr.tag_id, 0)) '.
		// 	'join tags th ON tr.tag_id = th.id '.
		// 	'WHERE ura.user_id = $1 AND ura.role_id < 3  AND ura.active = 1';
		// $res = mysqli_query_params( $sql, Array($_SESSION['user']['id'], $_SESSION['sysGroups'][1]) ) or die(mysqli_query_error());
		// while($r = $res->fetch_assoc()) $rez['data'][] = $r;
		// $res->close();
		// mysqli_clean_connection();
		// return $rez;
	}
	static function getVisibleOffices(){
		/* return office ids where the user has at least read access  */
		$rez = array();
		return $rez;
		// $res = mysqli_query_params( 'SELECT DISTINCT th.id, th.l'.UL_ID().' `name` '.
		// 	'FROM users_groups_association ura '.
		// 	'join users u on u.id = ura.user_id AND u.enabled = 1 AND u.deleted = 0 '.
		// 	'join tag_groups__tags_result tr on tr.tags_group_id = $2 and (ura.office_id IN (tr.tag_id, 0)) '.
		// 	'join tags th ON tr.tag_id = th.id '.
		// 	'WHERE ura.user_id = $1 AND ura.role_id < 5  AND ura.active = 1', array($_SESSION['user']['id'], $_SESSION['sysGroups'][1]) ) or die(mysqli_query_error());
		// while( $r = $res->fetch_row() ) $rez[] = $r[0];
		// $res->close();
		// mysqli_clean_connection();
		// return $rez;
	}
	function getOfficeUsers($params){
		$rez = Array('success' => true, 'data' => array());
		return $rez;
		// if(empty($params->office_id)) return $rez;
		// $sql = 'SELECT u.id, coalesce(u.l'.UL_ID().', u.`name`) `name` FROM users u JOIN users_groups_association ur ON u.id = ur.user_id WHERE u.enabled = 1 and u.deleted = 0 and ur.active = 1 and ur.office_id = $1 ORDER BY ur.role_id';
		// $res = mysqli_query_params( $sql, $params->office_id ) or die(mysqli_query_error());
		// while($r = $res->fetch_assoc()) $rez['data'][] = $r;
		// $res->close();
		// return $rez;
	}
	static function getCaseLawyers( $case_id ){
		if(!is_numeric($case_id) || ($case_id < 1)) throw new Exception(L\Wrong_input_data);
		if(!Security::canReadCase($case_id)) throw new Exception(L\Access_denied);
		$rez = Array('success' => true, 'data' => array());
		return $rez;
		// $res = mysqli_query_params( 'SELECT u.id, u.sex, u.l'.UL_ID().
		// 	' FROM cases_rights cr '.
		// 	'JOIN users u ON cr.tag_id = u.tag_id AND u.enabled = 1 AND u.deleted = 0 '.
		// 	'WHERE cr.case_id = $1 AND (cr.access = 3) and cr.valid = 1', $case_id ) or die(mysqli_query_error());
		// while($r = $res->fetch_assoc()) $rez['data'][] = $r;
		// $res->close();
		// return $rez;
	}
	static function getCaseLowerLevelUsers($params){
		$case_id = is_array($params) ? $params['case_id'] : $params->case_id;
		$rez = Array('success' => true, 'data' => array());
		return $rez;
		// if(!is_numeric($case_id) || ($case_id < 1)) throw new Exception(L\Wrong_input_data);
		// $res = mysqli_query_params( 'select u.id, u.l'.UL_ID().' `name` from cases_rights_effective cr join users u on cr.user_id = u.id where cr.case_id = $1 and access >= $2', array($case_id, (Security::isAdmin() ? 1: 2)) ) or die(mysqli_query_error());
		// while($r = $res->fetch_assoc()) $rez['data'][] = $r;
		// $res->close();
		// return $rez;
	}
	static function getLowerLevelUsers(){
		$rez = Array('success' => true, 'data' => array());
		$user_id = $_SESSION['user']['id'];
		// $sql= 'SELECT DISTINCT u.id, u.l'.UL_ID().' `name`, concat(\'icon-user-\', coalesce(u.sex, \'\')) `iconCls` FROM users_groups_association ura1 '.
		// 	'JOIN users_groups_association ura2 ON ((ura1.office_id = 0) OR (ura2.office_id = 0) OR (ura1.office_id = ura2.office_id)) AND ura2.active = 1 '. //AND ura1.role_id <= ura2.role_id
		// 	',users u '.
		// 	'WHERE ura1.active = 1 AND ura1.user_id = $1 AND ura2.user_id = u.id order by 2';
		$sql = 'select id, l'.UL_ID().' `name`, concat(\'icon-user-\', coalesce(sex, \'\')) `iconCls` from users_groups where `type` = 2 and deleted = 0 and enabled = 1 order by 2';
		$res = mysqli_query_params( $sql, $user_id) or die(mysqli_query_error());
		while($r = $res->fetch_assoc()) $rez['data'][] = $r;
		$res->close();
		return $rez;
	}
	static function checkIfCaseOpened($case_id){
		return true;
		// require_once 'Cases.php';
		// if(!Cases::isOpened($case_id))  throw new Exception(L\case_not_oppened);
		// return true;
	}
	static function isAdmin($user_id = false){
		$rez = false;
		if($user_id == false) $user_id = $_SESSION['user']['id'];
		$res = mysqli_query_params('select min(group_id) from users_groups_association where user_id = $1', $user_id) or die(mysqli_query_error());
		if($r = $res->fetch_row()) $rez = ($r[0] == 1); else throw new Exception(L\User_not_found);
		$res->close();
		return $rez;
		//return (!empty($_SESSION['user']['admin']));
	}
	static function canManage($user_id = false){
		return true; // TODO: Review
		$role_id = Security::getUserRole($user_id);
		return (($role_id > 0) && ($role_id <=2)); //Managers and administrators
	}
	static function isUsersOwner($user_id){
		$res = mysqli_query_params('select cid from users_groups where id = $1', $user_id) or die(mysqli_query_error());
		if($r = $res->fetch_row()) $rez = ($r[0] == $_SESSION['user']['id']); else throw new Exception(L\User_not_found);
		$res->close();
		return $rez;
	}
	static function canManageTask($task_id, $user_id = false){
		$rez = false;
		if($user_id == false) $user_id = $_SESSION['user']['id'];
		$res = mysqli_query_params('select t.cid, ru.user_id from tasks t left join tasks_responsible_users ru  on ru.task_id = t.id and ((t.cid = $2) or (ru.user_id = $2)) where t.id = $1', array($task_id, $user_id)) or die(mysqli_query_error());
		if($r = $res->fetch_row()) $rez = true;
		$res->close();
		if(!$rez) $rez = Security::isAdmin($user_id);
		return $rez;
	}
}
?>