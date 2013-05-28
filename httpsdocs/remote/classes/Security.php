<?php 

namespace CB;

class Security {
	/* groups methods */
	
	/**
	 * Retreive defined groups
	 * 
	 * @returns array of groups records
	 */
	public static function getUserGroups(){
		$rez = array( 'success' => true, 'data' => array() );
		
		// if( !Security::isAdmin() ) throw new \Exception(L\Access_denied);
		
		$sql = 'select id, name, l'.USER_LANGUAGE_INDEX.' `title`, `system`, `enabled` from users_groups where type = 1 order by 3';
		$res = DB\mysqli_query_params($sql) or die(DB\mysqli_query_error());
		while($r = $res->fetch_assoc()) $rez['data'][] = $r;
		$res->close();
		return $rez;
	}

	/**
	 * Create group
	 * 
	 * Create a security group
	 * 
	 * @returns group properties
	 */
	public function createUserGroup($p){
		$p->success = true;

		if( !Security::isAdmin() ) throw new \Exception(L\Access_denied);
		$p->data->name = trim( strip_tags( $p->data->name) );
		
		// check if group with that name already exists 
		$res = DB\mysqli_query_params('select id from users_groups where type = 1 and name = $1', $p->data->name) or die(DB\mysqli_query_error());
		if($r = $res->fetch_row()) throw new \Exception(L\Group_exists);
		$res->close();
		// end of check if group with that name already exists 
		
		DB\mysqli_query_params('insert into users_groups (type, name, l1, l2, l3, l4, cid) values(1, $1, $1, $1, $1, $1, $2)', array($p->data->name, $_SESSION['user']['id']) ) or die(DB\mysqli_query_error());
		$p->data->id = DB\last_insert_id();

		return $p;
	}

	/**
	 * Update a group
	 */
	public function updateUserGroup($p){
		if( !Security::isAdmin() ) throw new \Exception(L\Access_denied);
		return array( 'success' => true, 'data' => array() );
	}

	/**
	 * Delete a securoty group
	 */
	public function destroyUserGroup($p){
		if( !Security::isAdmin() ) throw new \Exception(L\Access_denied);
		DB\mysqli_query_params('delete from users_groups where id = $1', $p ) or die(DB\mysqli_query_error());
		return array( 'success' => true, 'data' => $p );
	}
	/* end of groups methods */
	
	/**
	 * search users or groups for fields of type "objects"
	 * 
	 * This function receives field config as parameter (inluding text query) and returns the matched results.
	 */
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
			$a = Util\toNumericArray($p->types);
			if(!empty($a)) $where[] = '`type` in ('.implode(',', $a).')';
		}

		if(!empty($p->query)){
			$where[] = 'searchField like $1'; 
			$params[] = ' %'.trim($p->query).'% ';
		}

		$sql = 'select id, l'.USER_LANGUAGE_INDEX.' `name`, `system`, `enabled`, `type`, `sex` from users_groups where deleted = 0 '.( empty($where) ? '' : ' and '.implode(' and ', $where) ).' order by `type`, 2 limit 50';
		$res = DB\mysqli_query_params($sql, $params) or die(DB\mysqli_query_error());
		while($r = $res->fetch_assoc()){
			$r['iconCls'] = ($r['type'] == 1) ? 'icon-users' : 'icon-user-'.$r['sex'];
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
		
		if( empty($this->internalAccessing) && !Security::canRead($p->id) ) throw new \Exception(L\Access_denied);
		
		/* set object title, path and inheriting access ids path*/
		$obj_ids = array();
		$sql = 'select f_get_tree_path($1) `path`, name, f_get_tree_inherit_ids(id) `obj_ids` from tree where id = $1';
		$res = DB\mysqli_query_params($sql, $p->id) or die(DB\mysqli_query_error());
		if($r = $res->fetch_assoc()){
			$rez['path'] = Path::replaceCustomNames($r['path']);
			$rez['name'] = Path::replaceCustomNames($r['name']);
			$obj_ids = explode('/', substr($r['obj_ids'], 1));
		}
		$res->close();
		/* end of set object title and path*/

		/* get the full set of access credentials(users and/or groups) including inherited from parents */
		$lid = defined('CB\\USER_LANGUAGE_INDEX') ? USER_LANGUAGE_INDEX: 1;
		$sql = 'select distinct u.id, u.l'.$lid.' `name`, u.`system`, u.`enabled`, u.`type`, u.`sex` from tree_acl a '.
			'join users_groups u on a.user_group_id = u.id where a.node_id in('.implode(',', $obj_ids).') order by u.`type`, 2';
		$res = DB\mysqli_query_params($sql, $p->id) or die(DB\mysqli_query_error());
		while($r = $res->fetch_assoc()){
			$r['iconCls'] = ($r['type'] == 1) ? 'icon-users' : 'icon-user-'.$r['sex'];
			// unset($r['type']); // used internaly by setSolrAccess function
			unset($r['sex']);
			$access = $this->getUserGroupAccessForObject($p->id, $r['id']);
			$r['allow'] = implode(',', $access[0]);
			$r['deny'] = implode(',', $access[1]);
			$rez['data'][] = $r;
		}
		$res->close();
		/* end of get the full set of access credentials(users and/or groups) including inherited from parents */

		return $rez;
	}
	
	/**
	* Returns estimated bidimentional array of access bits, from object acl, for a user or group
	* 
	* Used for access display in interface
	* Returned array has to array elements: 
	* 	first - array bits for allow access 
	* 	second - array bits for deny access 
	* Each bit can have the following values:
	*	-2 - deny, inherited from a parent
	*	-1 - deny, directly set for the object
	*	 0 - not set 
	*	 1 - allow, directly set for the object
	*	 2 - allow, inherited from a parent
	*
	*	Permission Precedence: 
	*		Explicit Deny (access set for input object_id, not estimated in summary with near accesses for input object_id)
	*		Explicit Allow (access set for input object_id, not estimated in summary with near accesses for input object_id)
	*		Inherited Deny (access inherited from all parents)
	*		Inherited allow (access inherited from all parents)
	*/
	private static function getUserGroupAccessForObject($object_id, $user_group_id = false){
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
		/* if no user is specified as parameter then calculating for current loged user */
		if($user_group_id === false) $user_group_id = $_SESSION['user']['id'];

		/* prepearing result array (filling it with zeroes)*/
		$rez = array( array_fill(0,12, 0), array_fill(0,12, 0) );
		
		$user_group_ids = array($user_group_id);
		$everyoneGroupId = Security::EveryoneGroupId();
		if( $user_group_id !== $everyoneGroupId) $user_group_ids[] = $everyoneGroupId;

		/* getting object ids that have inherit set to true */
		$sql = 'select f_get_tree_inherit_ids(id) `ids` from tree where id = $1';
		$res = DB\mysqli_query_params($sql, $object_id) or die(DB\mysqli_query_error());
		$ids = array();
		if($r = $res->fetch_assoc()) $ids = explode('/', substr($r['ids'], 1));
		$res->close();

		/* reversing array for iterations from object to top parent */
		$ids = array_reverse($ids);


		/* getting group ids where passed $user_group_id is a member*/
		$sql = 'select distinct group_id from users_groups_association where user_id = $1';
		$res = DB\mysqli_query_params($sql, $user_group_id) or die(DB\mysqli_query_error());
		while($r = $res->fetch_row()) if(!in_array($r[0], $user_group_ids)) $user_group_ids[] = $r[0];
		$res->close();
		/* end of getting group ids where passed $user_group_id is a member*/
		
		$acl_order = array_flip($ids);
		$acl = array();
		// selecting access list set for our path ids
		$sql = 'select node_id, user_group_id, allow, deny from tree_acl where node_id in ('.implode(',', $ids).') and user_group_id in ('.implode(',', $user_group_ids).')';
		$res = DB\mysqli_query_params($sql, array()) or die(DB\mysqli_query_error());
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
		 	$inherited = ($i > 0) || (!isset($acl_order[$object_id]));
			$direct_allow_user_group_access = array_fill(0,12, 0);
			/* check firstly if direct access is specified for passed user_group_id */
			if(!empty($acl[$i][$user_group_id])){
				$deny = intval($acl[$i][$user_group_id][1]);
				for ($j=0; $j < sizeof($rez[1]); $j++){ 
					if( empty($rez[0][$j]) && empty($rez[1][$j]) && ($deny & 1) ){
						$rez[1][$j] = -(1 + $inherited); 
						$set_bits++;
					}
					$deny = $deny >> 1;
				}
				$allow = intval($acl[$i][$user_group_id][0]);
				for ($j=0; $j < sizeof($rez[0]); $j++){ 
					if( empty($rez[0][$j]) && empty($rez[1][$j]) && ($allow & 1) ){
						$rez[0][$j] = (1 + $inherited);
						$direct_allow_user_group_access[$j] = (1 + $inherited);
						$set_bits++;
					}
					$allow = $allow >> 1;
				}
			}
			
			/* if we have direct access specified to requested user_group for input object_id then return just this direct access  and exclude any other access at the same level (for our object_id) */
			if( isset($acl_order[$object_id]) && ($acl_order[$object_id]== $i) ){
				next($acl);
				continue;
			}


			if(!empty($acl[$i]))
			foreach($acl[$i] as $key => $value) {
				if( ($key == $user_group_id) || ($key == $everyoneGroupId) ) continue; //skip direct access setting because analized above and everyone group id will be analized last
				$deny = intval($value[1]);

				for ($j=0; $j < sizeof($rez[1]); $j++){ 
					if( empty($rez[0][$j]) && empty($rez[1][$j]) && ($deny & 1) && empty($direct_allow_user_group_access[$j]) ){ //set deny access only if not set directly for that credential allow access
						$rez[1][$j] = -(1 + $inherited); 
						$set_bits++;
					}
					$deny = $deny >> 1;
				}
				$allow = intval($value[0]);
				for ($j=0; $j < sizeof($rez[0]); $j++){ 
					if( empty($rez[0][$j]) && empty($rez[1][$j]) && ($allow & 1) ){
						$rez[0][$j] = (1 + $inherited);
						$set_bits++;
					}
					$allow = $allow >> 1;
				}
			}

			// now analize for everyone group id if set, but only for higher levels (inherited parents)
			if( !empty($acl[$i][$everyoneGroupId]) ){
				$value = $acl[$i][$everyoneGroupId];
				$deny = intval($value[1]);
				for ($j=0; $j < sizeof($rez[1]); $j++){ 
					if( empty($rez[0][$j]) && empty($rez[1][$j]) && ($deny & 1) && empty($direct_allow_user_group_access[$j]) ){ //set deny access only if not set directly for that credential allow access
						$rez[1][$j] = -(1 + $inherited); 
						$set_bits++;
					}
					$deny = $deny >> 1;
				}
				$allow = intval($value[0]);
				for ($j=0; $j < sizeof($rez[0]); $j++){ 
					if( empty($rez[0][$j]) && empty($rez[1][$j]) && ($allow & 1) ){
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

	private static function getEstimatedUserAccessForObject($object_id, $user_id = false){
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
		/* if no user is specified as parameter then calculating for current loged user */
		if($user_id === false) $user_id = $_SESSION['user']['id'];

		/* prepearing result array (filling it with zeroes)*/
		$rez = array( array_fill(0,12, 0), array_fill(0,12, 0) );
		
		$user_group_ids = array($user_id);
		$everyoneGroupId = Security::EveryoneGroupId();
		if( $user_id !== $everyoneGroupId) $user_group_ids[] = $everyoneGroupId;

		/* getting object ids that have inherit set to true */
		$sql = 'select f_get_tree_inherit_ids(id) `ids` from tree where id = $1';
		$res = DB\mysqli_query_params($sql, $object_id) or die(DB\mysqli_query_error());
		$ids = array();
		if($r = $res->fetch_assoc()) $ids = explode('/', substr($r['ids'], 1));
		$res->close();

		/* reversing array for iterations from object to top parent */
		$ids = array_reverse($ids);


		/* getting group ids where passed $user_id is a member*/
		$sql = 'select distinct group_id from users_groups_association where user_id = $1';
		$res = DB\mysqli_query_params($sql, $user_id) or die(DB\mysqli_query_error());
		while($r = $res->fetch_row()) if(!in_array($r[0], $user_group_ids)) $user_group_ids[] = $r[0];
		$res->close();
		/* end of getting group ids where passed $user_id is a member*/
		
		$acl_order = array_flip($ids);
		$acl = array();
		// selecting access list set for our path ids
		$sql = 'select node_id, user_group_id, allow, deny from tree_acl where node_id in ('.implode(',', $ids).') and user_group_id in ('.implode(',', $user_group_ids).')';
		$res = DB\mysqli_query_params($sql, array()) or die(DB\mysqli_query_error());
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
			$direct_allow_user_group_access = array_fill(0,12, 0);
			/* check firstly if direct access is specified for passed user_id */
			if(!empty($acl[$i][$user_id])){
				$deny = intval($acl[$i][$user_id][1]);
				for ($j=0; $j < sizeof($rez[1]); $j++){ 
					if( empty($rez[0][$j]) && empty($rez[1][$j]) && ($deny & 1) ){
						$rez[1][$j] = -(1 + $inherited); 
						$set_bits++;
					}
					$deny = $deny >> 1;
				}
				$allow = intval($acl[$i][$user_id][0]);
				for ($j=0; $j < sizeof($rez[0]); $j++){ 
					if( empty($rez[0][$j]) && empty($rez[1][$j]) && ($allow & 1) ){
						$rez[0][$j] = (1 + $inherited);
						$direct_allow_user_group_access[$j] = (1 + $inherited);
						$set_bits++;
					}
					$allow = $allow >> 1;
				}
	
				/* if we have direct access specified to requested user for input object_id then return just this direct access  and exclude any other access at the same level (for our object_id) */
				if( $acl_order[$object_id] == $i ){
					next($acl);
					continue;
				}
			}
			


			if(!empty($acl[$i]))
			foreach($acl[$i] as $key => $value) {
				if( ($key == $user_id) || ($key == $everyoneGroupId) ) continue; //skip direct access setting because analized above and everyone group id will be analized last
				$deny = intval($value[1]);

				for ($j=0; $j < sizeof($rez[1]); $j++){ 
					if( empty($rez[0][$j]) && empty($rez[1][$j]) && ($deny & 1) && empty($direct_allow_user_group_access[$j]) ){ //set deny access only if not set directly for that credential allow access
						$rez[1][$j] = -(1 + $inherited); 
						$set_bits++;
					}
					$deny = $deny >> 1;
				}
				$allow = intval($value[0]);
				for ($j=0; $j < sizeof($rez[0]); $j++){ 
					if( empty($rez[0][$j]) && empty($rez[1][$j]) && ($allow & 1) ){
						$rez[0][$j] = (1 + $inherited);
						$set_bits++;
					}
					$allow = $allow >> 1;
				}
			}

			// now analize for everyone group id if set, but only for higher levels (inherited parents)
			if( !empty($acl[$i][$everyoneGroupId]) ){
				$value = $acl[$i][$everyoneGroupId];
				$deny = intval($value[1]);
				for ($j=0; $j < sizeof($rez[1]); $j++){ 
					if( empty($rez[0][$j]) && empty($rez[1][$j]) && ($deny & 1) && empty($direct_allow_user_group_access[$j]) ){ //set deny access only if not set directly for that credential allow access
						$rez[1][$j] = -(1 + $inherited); 
						$set_bits++;
					}
					$deny = $deny >> 1;
				}
				$allow = intval($value[0]);
				for ($j=0; $j < sizeof($rez[0]); $j++){ 
					if( empty($rez[0][$j]) && empty($rez[1][$j]) && ($allow & 1) ){
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

	public static function getAccessBitForObject($object_id, $access_bit_index, $user_id = false){
		if($user_id === false) $user_id = $_SESSION['user']['id'];
		$accessArray = Security::getEstimatedUserAccessForObject($object_id, $user_id);
		if(!empty($accessArray[0][$access_bit_index])) return $accessArray[0][$access_bit_index];
		if(!empty($accessArray[1][$access_bit_index])) return $accessArray[1][$access_bit_index];
		return 0;
	}

	public static function canListFolderOrReadData($object_id, $user_group_id = false){
		return (Security::getAccessBitForObject($object_id, 0, $user_group_id) > 0);
	}
	public static function canCreateFolders($object_id, $user_group_id = false){
		return (Security::getAccessBitForObject($object_id, 1, $user_group_id) > 0);
	}
	public static function canCreateFiles($object_id, $user_group_id = false){
		return (Security::getAccessBitForObject($object_id, 2, $user_group_id) > 0);
	}
	public static function canCreateActions($object_id, $user_group_id = false){
		return (Security::getAccessBitForObject($object_id, 3, $user_group_id) > 0);
	}
	public static function canCreateTasks($object_id, $user_group_id = false){
		return (Security::getAccessBitForObject($object_id, 4, $user_group_id) > 0);
	}
	public static function canRead($object_id, $user_group_id = false){
		return (Security::getAccessBitForObject($object_id, 5, $user_group_id) > 0);
	}
	public static function canWrite($object_id, $user_group_id = false){
		return (Security::getAccessBitForObject($object_id, 6, $user_group_id) > 0);
	}
	public static function canDeleteChilds($object_id, $user_group_id = false){
		return (Security::getAccessBitForObject($object_id, 7, $user_group_id) > 0);
	}
	public static function canDelete($object_id, $user_group_id = false){
		return (Security::getAccessBitForObject($object_id, 8, $user_group_id) > 0);
	}
	public static function canChangePermissions($object_id, $user_group_id = false){
		return (Security::getAccessBitForObject($object_id, 9, $user_group_id) > 0);
	}
	public static function canTakeOwnership($object_id, $user_group_id = false){
		return (Security::getAccessBitForObject($object_id, 10, $user_group_id) > 0);
	}
	public static function canDownload($object_id, $user_group_id = false){
		return (Security::getAccessBitForObject($object_id, 11, $user_group_id) > 0);
	}
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

	public function addObjectAccess($p){
		$rez = array('success' => true, 'data' => array());
		if(empty($p->data)) return $rez;

		if( !Security::canChangePermissions($p->id) ) throw new \Exception(L\Access_denied);

		DB\mysqli_query_params('insert into tree_acl (node_id, user_group_id, cid, uid) values ($1, $2, $3, $3) on duplicate key update id = last_insert_id(id), uid = $3', array($p->id, $p->data->id, $_SESSION['user']['id']) ) or die(DB\mysqli_query_error());
		
		$rez['data'][] = $p->data;
		SolrClient::RunCron();
		return $rez;
	}

	public function updateObjectAccess($p){
		if( !Security::canChangePermissions($p->id) ) throw new \Exception(L\Access_denied);

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
		DB\mysqli_query_params($sql, array($p->id, $p->data->id, $allow, $deny, $_SESSION['user']['id']) ) or die(DB\mysqli_query_error());
		SolrClient::RunCron();
		return array('succes' => true, 'data' => $p->data );
	}
	public function destroyObjectAccess($p){
		if(empty($p->data)) return;
		if( !Security::canChangePermissions($p->id) ) throw new \Exception(L\Access_denied);
		DB\mysqli_query_params('delete from tree_acl where node_id = $1 and user_group_id = $2', array($p->id, $p->data)) or die(DB\mysqli_query_error());
		SolrClient::RunCron();
		return array('success' => true, 'data'=> array());
	}

	/* end of objects acl methods*/
	
	/* Update the acl rules result for nodes that associated with any of given group or user */
	public static function updateUserGroupAccess( $user_group_ids ){
		$affected_nodes = Security::getAffectedNodes( $user_group_ids );
		Security::updateNodesSecurity( $affected_nodes );
	}

	public static function getAffectedNodes( $user_group_ids ){
		$rez = array();
		$sql = 'select node_id from tree_acl where user_group_id in ('.implode(',', $user_group_ids).')';
		$res = DB\mysqli_query_params($sql) or die( DB\mysqli_query_error() );
		while($r = $res->fetch_assoc())
			$rez[] = $r['node_id'];
		$res->close();
		return $rez;
	}
	
	public static function updateNodesSecurity( $affected_node_ids ){
		foreach( $affected_node_ids as $id)
			DB\mysqli_query_params('call p_mark_all_childs_as_updated($1, 10)', $id) or die( DB\mysqli_query_error() );
		SolrClient::runCron();
	}

	/**
	 * Set access field values for a given record from tree
	 */
	function setObjectSolrAccessFields(&$objectRecord){
		
		$this->internalAccessing = true;
		
		$acl = array();
		// $acl = $this->getObjectAcl((Object)array('id' => $objectRecord['id']));

		/* get inheriting access ids path*/
		$inherit_ids = '';
		$sql = 'select f_get_tree_inherit_ids(id) `inherit_ids` from tree where id = $1';
		$res = DB\mysqli_query_params($sql, $objectRecord['id']) or die(DB\mysqli_query_error());
		if($r = $res->fetch_assoc()) $inherit_ids = $r['inherit_ids'];
		$res->close();
		/* end of get inheriting access ids path*/
		
		/* check if already calculated for these acl ids and return cached result if so */
		if(isset($GLOBALS['tree_acl'][$inherit_ids])){
			if(!empty($GLOBALS['tree_acl'][$inherit_ids]['a'])) $objectRecord['allow_user_ids'] = $GLOBALS['tree_acl'][$inherit_ids]['a'];
			if(!empty($GLOBALS['tree_acl'][$inherit_ids]['d'])) $objectRecord['deny_user_ids'] = $GLOBALS['tree_acl'][$inherit_ids]['d'];
			return;
		}
		/* end of check if already calculated for these acl ids and return cached result if so */
		$obj_ids = explode('/', substr($inherit_ids, 1));
		$everyoneGroupId = Security::EveryoneGroupId();
		$users = array();

		
		/* iterate the full set of access credentials(users and/or groups) and estimate access for every user including everyone group */
		if(!empty($inherit_ids)){
			$sql = 'select distinct u.id, u.`type` from tree_acl a '.
				'join users_groups u on a.user_group_id = u.id '.
				'where a.node_id in('.implode(',', $obj_ids).') order by u.`type`';
			$res = DB\mysqli_query_params($sql, $objectRecord['id']) or die(DB\mysqli_query_error());
			while($r = $res->fetch_assoc()){
				$group_users = array();
				if( ($r['id'] == $everyoneGroupId) || ($r['type'] == 2) ) $group_users[] = $r['id'];
				else $group_users = $this->getGroupUserIds($r['id']);
				foreach($group_users as $user_id) 
					if(empty($users[$user_id]))
						$users[$user_id] = $this->getEstimatedUserAccessForObject( $objectRecord['id'], $user_id );
			}
			$res->close();
		}
		/* end of iterate the full set of access credentials(users and/or groups) and estimate access for every user including everyone group */

		$allow_users = array();
		$deny_users = array();
		foreach($users as $user_id => $access){
			if($access[1][5] < 0) $deny_users[] = $user_id;
			elseif($access[0][5] > 0) $allow_users[] = $user_id;
		}
		
		/* selecting node owner and store him into allow and remove from deny */
		$sql = 'select oid from tree where id = $1';
		$res = DB\mysqli_query_params($sql, $objectRecord['id']) or die(DB\mysqli_query_error());
		if(($r = $res->fetch_row()) && !empty($r[0])){
			if(!in_array($r[0], $allow_users)) $allow_users[] = $r[0];
			$deny_users = array_diff($deny_users, array($r[0])); 
		}
		$res->close();
		/* end of selecting node owner and store him into allow and remove from deny */

		if(in_array($everyoneGroupId, $allow_users)) $allow_users = array($everyoneGroupId);
		if(in_array($everyoneGroupId, $deny_users)) $deny_users = array();

		/* caching in globals */
		$GLOBALS['tree_acl'][$inherit_ids] = array(
			'a' => $allow_users
			,'d' => $deny_users
		);
		/* end of caching in globals */

		if(!empty($allow_users)) $objectRecord['allow_user_ids'] = $allow_users;
		if(!empty($deny_users)) $objectRecord['deny_user_ids'] = $deny_users;
	}

	/**
	 * Retreive everyone group id
	 */
	static function EveryoneGroupId(){
		if(isset($GLOBALS['EVERYONE_GROUP_ID'])) return $GLOBALS['EVERYONE_GROUP_ID'];
		$GLOBALS['EVERYONE_GROUP_ID'] = null;
		$sql = "select id from users_groups where `type` = 1 and `system` = 1 and name = 'everyone'";
		$res = DB\mysqli_query_params($sql) or die(DB\mysqli_query_error());
		if($r = $res->fetch_row()) $GLOBALS['EVERYONE_GROUP_ID'] = $r[0];
		$res->close();
		return $GLOBALS['EVERYONE_GROUP_ID'];
	} 

	/**
	 * Get an array of user ids associated to the given group
	 */
	public function getGroupUserIds($group_id){
		$rez = array();
		$sql = 'select user_id from users_groups_association where group_id = $1';
		$res = DB\mysqli_query_params($sql, $group_id) or die(DB\mysqli_query_error());
		while($r = $res->fetch_row()) $rez[] = $r[0];
		$res->close();
		return $rez;
	}

	/**
	 * Get the list of active users with basic data
	 */
	static function getActiveUsers(){
		$rez = Array('success' => true, 'data' => array());
		$user_id = $_SESSION['user']['id'];
		$sql = 'select id, l'.USER_LANGUAGE_INDEX.' `name`, concat(\'icon-user-\', coalesce(sex, \'\')) `iconCls` from users_groups where `type` = 2 and deleted = 0 and enabled = 1 order by 2';
		$res = DB\mysqli_query_params( $sql, $user_id) or die(DB\mysqli_query_error());
		while($r = $res->fetch_assoc()) $rez['data'][] = $r;
		$res->close();
		return $rez;
	}
	/* ----------------------------------------------------  OLD METHODS ------------------------------------------ */

	/**
	 * Check if user_id (or current loged user) is an administrator
	 */
	static function isAdmin($user_id = false){
		$rez = false;
		if($user_id == false) $user_id = $_SESSION['user']['id'];
		$res = DB\mysqli_query_params('select $1 from users_groups g  '.
			'join users_groups_association uga on g.id = uga.group_id and uga.user_id = $1 '.
			'where g.system = 1  and g.name = \'system\'', $user_id) or die(DB\mysqli_query_error());
		if($r = $res->fetch_row()) $rez = !empty($r[0]);
		$res->close();
		return $rez;
	}
	static function canManage($user_id = false){
		return true; // TODO: Review
		// $role_id = Security::getUserRole($user_id);
		// return (($role_id > 0) && ($role_id <=2)); //Managers and administrators
	}
	static function isUsersOwner($user_id){
		$res = DB\mysqli_query_params('select cid from users_groups where id = $1', $user_id) or die(DB\mysqli_query_error());
		if($r = $res->fetch_row()) $rez = ($r[0] == $_SESSION['user']['id']); else throw new \Exception(L\User_not_found);
		$res->close();
		return $rez;
	}
	static function canManageTask($task_id, $user_id = false){
		$rez = false;
		if($user_id == false) $user_id = $_SESSION['user']['id'];
		$res = DB\mysqli_query_params('select t.cid, ru.user_id from tasks t left join tasks_responsible_users ru  on ru.task_id = t.id and ((t.cid = $2) or (ru.user_id = $2)) where t.id = $1', array($task_id, $user_id)) or die(DB\mysqli_query_error());
		if($r = $res->fetch_row()) $rez = true;
		$res->close();
		if(!$rez) $rez = Security::isAdmin($user_id);
		return $rez;
	}
}
?>