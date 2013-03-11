<?php 
class Security {

	public function initTest(){
		$rez = array('success' => true, 'data' => array());
		$res = mysqli_query_params( 'select id from tags where pid = 197 and name =\'testoffice\'') or die(mysqli_query_error());
		while( $r = $res->fetch_row() ) $rez['data']['testoffice_id'] = $r[0];
		$res->close();
		$res = mysqli_query_params( 'select id from users where name =\'writeuser\'') or die(mysqli_query_error());
		while( $r = $res->fetch_row() ) $rez['data']['writeuser_id'] = $r[0];
		$res->close();
		$res = mysqli_query_params( 'select id from users where name =\'readuser\'') or die(mysqli_query_error());
		while( $r = $res->fetch_row() ) $rez['data']['readuser_id'] = $r[0];
		$res->close();
		$res = mysqli_query_params( 'select id from users where name =\'denyuser\'') or die(mysqli_query_error());
		while( $r = $res->fetch_row() ) $rez['data']['denyuser_id'] = $r[0];
		$res->close();
		mysqli_query_params( 'delete from cases where name =\'testcase\'') or die(mysqli_query_error());
		return $rez;
	}
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
		/* return users role. If no user is specified then current sessions user is used  */
		$rez = false;
		if($user_id == false) $user_id = $_SESSION['user']['id'];
		$sql = 'select min(role_id) from users_roles_association where user_id = $1 and active = 1';
		// $sql = 'SELECT MIN(role_id) FROM '.
		// 	'(SELECT MIN(role_id) `role_id` FROM users_roles_association WHERE user_id = $1 AND active = 1 '.
		// 	'UNION '.
		// 	'SELECT MIN(access) `role_id` FROM cases_rights cr JOIN users u ON cr.tag_id = u.tag_id WHERE u.id = $1 AND cr.valid = 1) t';
		$sql = 'SELECT MIN(role_id) `role_id` FROM users_roles_association WHERE user_id = $1 AND active = 1';
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
		if($p && isset($p->withNoOffice) && $p->withNoOffice) $rez['data'][] = array('id' => 0, 'name' => L\Out_of_office);
		$sql = 'SELECT DISTINCT th.id, th.l'.UL_ID().' `name` '.
			'FROM users_roles_association ura '.
			'join users u on u.id = ura.user_id AND u.enabled = 1 AND u.deleted = 0 '.
			'join tag_groups__tags_result tr on tr.tags_group_id = $2 and (ura.office_id IN (tr.tag_id, 0)) '.
			'join tags th ON tr.tag_id = th.id '.
			'WHERE ura.user_id = $1 AND ura.role_id < 3  AND ura.active = 1';
		$res = mysqli_query_params( $sql, Array($_SESSION['user']['id'], $_SESSION['sysGroups'][1]) ) or die(mysqli_query_error());
		while($r = $res->fetch_assoc()) $rez['data'][] = $r;
		$res->close();
		mysqli_clean_connection();
		return $rez;
	}
	static function getVisibleOffices(){
		/* return office ids where the user has at least read access  */
		$rez = array();
		$res = mysqli_query_params( 'SELECT DISTINCT th.id, th.l'.UL_ID().' `name` '.
			'FROM users_roles_association ura '.
			'join users u on u.id = ura.user_id AND u.enabled = 1 AND u.deleted = 0 '.
			'join tag_groups__tags_result tr on tr.tags_group_id = $2 and (ura.office_id IN (tr.tag_id, 0)) '.
			'join tags th ON tr.tag_id = th.id '.
			'WHERE ura.user_id = $1 AND ura.role_id < 5  AND ura.active = 1', array($_SESSION['user']['id'], $_SESSION['sysGroups'][1]) ) or die(mysqli_query_error());
		while( $r = $res->fetch_row() ) $rez[] = $r[0];
		$res->close();
		mysqli_clean_connection();
		return $rez;
	}
	function getOfficeUsers($params){
		$rez = Array('success' => true, 'data' => array());
		if(empty($params->office_id)) return $rez;
		$sql = 'SELECT u.id, coalesce(u.l'.UL_ID().', u.`name`) `name` FROM users u JOIN users_roles_association ur ON u.id = ur.user_id WHERE u.enabled = 1 and u.deleted = 0 and ur.active = 1 and ur.office_id = $1 ORDER BY ur.role_id';
		$res = mysqli_query_params( $sql, $params->office_id ) or die(mysqli_query_error());
		while($r = $res->fetch_assoc()) $rez['data'][] = $r;
		$res->close();
		return $rez;
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
		// $sql= 'SELECT DISTINCT u.id, u.l'.UL_ID().' `name`, concat(\'icon-user-\', coalesce(u.sex, \'\')) `iconCls` FROM users_roles_association ura1 '.
		// 	'JOIN users_roles_association ura2 ON ((ura1.office_id = 0) OR (ura2.office_id = 0) OR (ura1.office_id = ura2.office_id)) AND ura2.active = 1 '. //AND ura1.role_id <= ura2.role_id
		// 	',users u '.
		// 	'WHERE ura1.active = 1 AND ura1.user_id = $1 AND ura2.user_id = u.id order by 2';
		$sql = 'select id, l'.UL_ID().' `name`, concat(\'icon-user-\', coalesce(sex, \'\')) `iconCls` from users where deleted = 0 and enabled = 1 order by 2';
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
		$res = mysqli_query_params('select min(role_id) from users_roles_association where user_id = $1', $user_id) or die(mysqli_query_error());
		if($r = $res->fetch_row()) $rez = ($r[0] == 1); else throw new Exception(L\User_not_found);
		$res->close();
		return $rez;
		//return (!empty($_SESSION['user']['admin']));
	}
	static function canManage($user_id = false){
		$role_id = Security::getUserRole($user_id);
		return (($role_id > 0) && ($role_id <=2)); //Managers and administrators
	}
	static function isUsersOwner($user_id){
		$res = mysqli_query_params('select pid from users where id = $1', $user_id) or die(mysqli_query_error());
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