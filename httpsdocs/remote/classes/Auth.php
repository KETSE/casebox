<?php
require_once 'Security.php';
require_once 'Log.php';
class Auth {

	public static function login($login, $pass){
		$ips = '|'.getIPs().'|';

		//unset($_SESSION['user']);
		//$dbh = $_SESSION['dbh'];
	
		session_regenerate_id(false);
		//session_destroy();
		//session_start();
		//$_SESSION['dbh'] = $dbh;
		$_SESSION['ips'] = $ips;
		$_SESSION['key'] = md5($ips.$login.$pass.time());
		setcookie('key', $_SESSION['key'], 0, '/', $_SERVER['SERVER_NAME'], !empty($_SERVER['HTTPS']), true);
		
		$rez = Array('success' => false, 'msg' => L\Auth_fail);
		$user_id = false;
		$res = mysqli_query_params('CALL p_user_login($1, $2, $3)', array($login, $pass, $ips)) or die(mysqli_query_error());
		if (($r = $res->fetch_row()) && ($r[1] == 1))  $user_id = $r[0];
		$res->close();
		mysqli_clean_connection();
		if($user_id){
			$rez = Array('success' => true, 'user' => array());
			/*fetching core languages and store them in the session for any use */
			$_SESSION['languages'] = array('per_id' => array(), 'per_abrev' => array(), 'string' => '', 'count' => 0);
			$sql = 'SELECT id, name, abreviation, locale, long_date_format, short_date_format, time_format FROM languages order by name';
			$res = mysqli_query_params($sql) or die(mysqli_query_error());
			while($r = $res->fetch_assoc()){
				$_SESSION['languages']['per_id'][$r['id']] = $r;
				$_SESSION['languages']['per_abrev'][$r['abreviation']] = &$_SESSION['languages']['per_id'][$r['id']];
			}
			$res->close();
			$_SESSION['languages']['count'] = sizeof($_SESSION['languages']['per_id']);
			$_SESSION['languages']['string'] = 'l'.implode(',l', array_keys($_SESSION['languages']['per_id']));
			/*end of fetching core languages and store them in the session for any use */
			
			$sql = 'SELECT u.id, u.tag_id, u.`language_id`, '.$_SESSION['languages']['string'].', short_date_format, long_date_format, sex, cfg FROM users_groups u WHERE u.id = $1';
			$res = mysqli_query_params($sql, $user_id) or die(mysqli_query_error());
			if ($r = $res->fetch_assoc()) {
				$r['admin'] = Security::isAdmin($user_id);
				$r['manage'] = Security::canManage($user_id);
				$r['role'] = Security::getUserRole($user_id);
				
				$r['language'] = $_SESSION['languages']['per_id'][$r['language_id']]['abreviation'];
				$r['locale'] = 	$_SESSION['languages']['per_id'][$r['language_id']]['locale'];
				if(empty($r['short_date_format']))$r['short_date_format'] = $_SESSION['languages']['per_id'][$r['language_id']]['short_date_format'];
				if(empty($r['long_date_format'])) $r['long_date_format'] = $_SESSION['languages']['per_id'][$r['language_id']]['long_date_format'];
				$r['time_format'] = $_SESSION['languages']['per_id'][$r['language_id']]['time_format'];
				
				/* personal tags has priority, if set, for core then for user then for template */
				$r['cfg'] = empty($r['cfg']) ? array() : (array)json_decode($r['cfg']);

				$system_tags = CB_get_param('system_tags');
				if(isset($r['cfg']['system_tags'])) $system_tags = $r['cfg']['system_tags'];
				$_SESSION['system_tags'] = ($system_tags == 1);
				$r['cfg']['system_tags'] = $_SESSION['system_tags'];

				$personal_tags = CB_get_param('personal_tags');
				if(isset($r['cfg']['personal_tags'])) $personal_tags = $r['cfg']['personal_tags'];
				$_SESSION['personal_tags'] = ($personal_tags == 1);
				$r['cfg']['personal_tags'] = $_SESSION['personal_tags'];
				
				$rez['user'] = $r;
				$_SESSION['user'] = $r;
				setcookie('L', $r['language']);
			}
			$res->close();
			/* selecting and storing system groups */
			$_SESSION['sysGroups'] = array();
			$res = mysqli_query_params('select `system`, id from tag_groups where `system` > 0 order by `system`') or die(mysqli_query_error());
			while($r = $res->fetch_row()) $_SESSION['sysGroups'][$r[0]] = $r[1];
			$res->close();
			/* end of selecting and storing system groups */

			/* get config variables */
			$_SESSION['config'] = array();
			$res = mysqli_query_params('select `param`, `value`, `default_value` from `config`') or die(mysqli_query_error());
			while($r = $res->fetch_row()) $_SESSION['config'][$r[0]] = array($r[1], $r[2]);
			$res->close();
			$rez['config'] = $_SESSION['config'];
			/* end of get config variables */
			
			/* storing max file versions count (mfvc) configuration fr core in session */
			//*:1;doc,docx,xls,xlsx,pdf:5;
			$_SESSION['mfvc'] = array('*' => 0);//default is no versions if nothing specified in config

			$v = CB_get_param('max_files_version_count');
			if(!empty($v)){
				$v = explode(';', $v);
				foreach($v as $vc){
					$vc = explode(':', $vc);
					if(sizeof($vc) == 2){
						$ext = trim($vc[0]);
						$count = trim($vc[1]);
						if(is_numeric($count)){
							$ext = explode(',', $ext);
							foreach($ext as $e){
								$e = trim($e);
								$e = mb_strtolower($e);
								$_SESSION['mfvc'][$e] = $count;
							}
						}
					}
				}
			}
			/* end of storing max file versions configuration fr core in session */
		}
		Log::add(Array('action_type' => 1, 'result' => isset($_SESSION['user']), 'info' => 'user: '.$login."\nip: ".$ips));
		return $rez;
	}

	public function getLoginInfo() {
		$rez = array('success' => true, 'config' => $_SESSION['config'], 'user' => $_SESSION['user'] );
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
		$res = mysqli_query_params('select id, abreviation from languages where id = $1', intval($id)) or die(mysqli_query_error());
		if ($r = $res->fetch_row()) {
			$_SESSION['user']['language_id'] = $r[0];
			$_SESSION['user']['language'] = $r[1];
			setcookie('L', $r[1]);
		} else return array('success' => false);
		$res->close();
		$res = mysqli_query_params('update users_groups set language_id = $2 where id = $1', array($_SESSION['user']['id'], $id)) or die(mysqli_query_error());
		return Array('success' => true);
	}
}