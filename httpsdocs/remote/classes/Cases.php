<?php
require_once 'Security.php';
require_once 'Log.php';
class Cases{

	public function getList($in){
		/* SECURITY: get visible offices */
		$visible_offices = Security::getVisibleOffices();
		/* end of SECURITY: get visible offices */
		$data = Array();
		$sql = 'SELECT c.id, c.nr, c.name title FROM cases c';
		// if(!Security::isAdmin()) $sql .= ' join cases_rights cr on c.id = cr.case_id and cr.tag_id in (0'.implode(',', $visible_offices).') '.
		// 	'and cr.active = 1 and ((cr.from is null) or (cr.from <= current_date)) and ((cr.until is null) or (cr.until > current_date))';
		$sql .= ' order by c.id desc limit 200';
		$res = mysqli_query_params($sql) or die(mysqli_query_error());
		while($r = $res->fetch_assoc()){
			$sql = 'SELECT COUNT(*) action_count, MAX(cl.date_start) FROM objects cl JOIN templates t ON cl.case_id = $1 AND cl.template_id = t.id AND t.pid IN (5, 6)';
			$res2 = mysqli_query_params($sql, $r['id']) or die(mysqli_query_error());
			if($r2 = $res2->fetch_row()){
				$r['action_count'] = $r2[0];
				$r['last_action_date'] = $r2[1];
			}
			$res2->close();

			$sql = 'SELECT ap.title FROM objects ap JOIN templates t ON ap.case_id = $1 and ap.template_id = t.id AND t.pid = 47 order by date_start desc limit 1';
			$res2 = mysqli_query_params($sql, $r['id']) or die(mysqli_query_error());
			if($r2 = $res2->fetch_row()) $r['authorities_phase'] = $r2[0];
			$res2->close();

			$sql = 'SELECT cp.title FROM objects cp JOIN templates t ON cp.case_id = $1 and cp.template_id = t.id AND t.pid = 53 ORDER BY date_start DESC LIMIT 1';
			$res2 = mysqli_query_params($sql, $r['id']) or die(mysqli_query_error());
			if($r2 = $res2->fetch_row()) $r['committee_phase'] = $r2[0];
			$res2->close();
			array_push($data, $r);
		}
		$res->close();
		return $data;
	}

	public function create($params){//added to tests
		/* SECURITY: check if current user can add cases for specified office */
		//$managed_offices = Security::getManagedOfficeIds();
		//if(!Security::isAdmin() && !in_array($params->office_id, $managed_offices)) throw new Exception(L\No_manage_access_for_office);
		if(empty($params->nr)) $params->nr = null;
		/* end of SECURITY: check if current user can add cases for specified office */
		if(empty($params->pid)) $params->pid = Browser::getRootFolderId();
		mysqli_query_params('insert into tree (pid, name, `type`, cid, uid) values ($1, $2, 3, $2, $2)', array($params->pid, $params->name, $_SESSION['user']['id'])) or die(mysqli_query_error());
		$id = last_insert_id();
		$sql = 'insert into cases (id, nr, name, `date`, cid, type_id) values($1, $2, $3, $4, $5, $6)';
		mysqli_query_params($sql, Array($id, $params->nr, $params->name, function_exists('clientToMysqlDate') ? clientToMysqlDate($params->date) : $params->date, $_SESSION['user']['id'], $params->case_type) ) or die(mysqli_query_error());

		// if(!empty($params->case_type)) //setting case type tag if set
		// 	mysqli_query_params('INSERT INTO cases_tags (case_id, tag_id, level) VALUES ($1, $2, 0)', array($id, $params->case_type)) or die(mysqli_query_error());

		//mysqli_query_params('INSERT INTO cases_rights (case_id, tag_id, access, `from`, active) VALUES ($1, $2, 3, current_date, 1)', array($id, $params->office_id)) or die(mysqli_query_error());
		// if(!empty($params->user_id)) //a default lawyer is set for the case
		// 	mysqli_query_params('INSERT INTO cases_rights (case_id, tag_id, access, `from`, active) select $1, tag_id, 3, current_date, 1 from users where id = $2', array($id, $params->user_id)) or die(mysqli_query_error());
		/* calling procedure to create system folders for this case*/
			mysqli_query_params('call p_create_case_system_folders($1, null)', $id) or die(mysqli_query_error());
		/* end of calling procedure to create system folders for this case*/

		//mysqli_query_params('CALL p_estimate_case_effective_access($1)', $id) or die(mysqli_query_error());
		Log::add(Array('action_type' => 3, 'case_id' => $id, 'info' => 'name: '.$params->name));
		SolrClient::runCron();
		return Array('success' => true, 'data' => Array('id' => $id, 'pid' => $params->pid, 'title' => $params->name));
	}

	public function save($in){
		// return false;
		// /* actually this function is not used in current case structure */
		
		// $rez = Array('success' => false, 'msg' => L\Error);
		// $data = (array)$in->data;
		// if(!is_numeric($in->id)) return $rez;
		// $id = $in->id;
		// /* SECURITY check */
		// if( !Security::canManageCase($id) ) throw new Exception(L\No_access_for_this_action);
		// /* end of SECURITY check */
		
		// if(empty($data['forcedSave'])){
			// $li = Cases::getLockInfo($id);
			// if(!empty($li)){
				// $rez['already_opened_by'] = str_replace(array('{user}', '{time_ago}'), 
					// array('<span style="font-weight: bold !important">'.$li['name'].'</span>', $li['time_passed'] ), L\case_already_editing_message);
				// return $rez;
			// } 
		// }

		// mysqli_query_params('update cases set name=$1 where id=$2', Array('', $id)) or die(mysqli_query_error());
		// Log::add(Array('action_type' => 4, 'case_id' => $id));
		// if(@$data['forcedSave']) Cases::lock($id);
		// if(@$data['close']) Cases::unlock($id);

		// $rez['success'] = true;
		// unset($rez['msg']);
		// return $rez;
	}

	public function close($id){
		$name = '';
		$res = mysqli_query_params('SELECT id, name `title` FROM cases WHERE id = $1', $id) or die(mysqli_query_error());
		if($r = $res->fetch_row()) $name = $r[1]; else throw new Exception(L('Object_not_found'));
		$res->close();
		/* SECURITY check */
		if( !Security::canManageCase($id) ) throw new Exception(L\No_access_for_this_action);
		/* end of SECURITY check */
		Log::add(Array('action_type' => 16, 'case_id' => $id, 'info' => 'name: '.$name));
		mysqli_query_params('update cases set close_date = CURRENT_TIMESTAMP WHERE id = $1 and closed = 0', $id) or die(mysqli_query_error());

		$close_date = null;
		$res = mysqli_query_params('SELECT close_date FROM cases WHERE id = $1', $id) or die(mysqli_query_error());
		if($r = $res->fetch_row()) $close_date = $r[0];
		$res->close();

		return array('success' => true, 'close_date' => $close_date );
	}

	public function load($p){
		$rez = Array('success' => true);
		if(!is_numeric($p->id)) throw new Exception(L\Wrong_input_data);
		$rez['id'] = $p->id;

		$sql = 'select id, nr, name, date, cid, cdate, uid, type_id from cases where id = $1';
		$res = mysqli_query_params($sql, $p->id) or die(mysqli_query_error());
		if($r = $res->fetch_assoc()) $rez['data'] = $r; 
		$res->close();
		
		$rez['data']['gridData'] = Templates::getObjectsData($p->id);
		
		return $rez;
	}

	public function getCasePropertiesObjectId($caseId){
		$rez = array('success' => true, 'id' => null);
		if(!is_numeric($caseId)) return $rez;
		$case = array();
		$sql = 'select c.id, c.name, c.date, c.cid, c.cdate, c.uid, c.udate, o.id `object_id`, c.type_id from cases c left join objects o on c.id = o.id where c.id = $1';
		$res = mysqli_query_params($sql, $caseId) or die(mysqli_query_error());
		if($r = $res->fetch_assoc()) $case = $r;
		$res->close();
		if(empty($case)) return $rez; //case does not exist

		if(!empty($case['object_id'])){
			$rez['id'] = $case['object_id'];
			return $rez;
		}
		if(empty($case['type_id'])) return $rez; //no case type defined
		
		/*try to get associated template for case_type and create corresponding object */
		$tpl = Templates::getCaseTypeTempleId($case['type_id']);
		if(!empty($tpl['id'])){
			$sql = 'insert into objects (id, title, custom_title, template_id, date_start, cid, cdate, uid, udate) values ($1, $2, $2, $3, $4, $5, $6, $7, $8)';
			mysqli_query_params($sql, array( $caseId, $case['name'], $tpl['id'], $case['date'], $case['cid'], $case['cdate'], $case['uid'], $case['udate'] ) ) or die(mysqli_query_error());
			$rez['id'] = last_insert_id();
		}
		return $rez;
	}

	public function queryCaseData($queries){
		$rez = array('success' => true);
		foreach($queries as $key => $query){
			$query->pids = $query->id;
			switch($key){
				case 'properties': 
					$rez[$key] = $this->load($query);/* load general case properties */
					
					$r = $this->getCasePropertiesObjectId($query->id);
					if( !empty($r['id']) ){
						$template_id = null;
						$properties = array();
						$sql = 'select template_id from objects where id = $1';
						$res = mysqli_query_params($sql, $r['id']) or die(mysqli_query_error());
						if($r = $res->fetch_assoc()) $template_id = $r['template_id'];
						$res->close();
						
						$tf = Templates::getTemplateFieldsWithData($template_id, $query->id);
						// var_dump($tf);
						if(!empty($tf))
						foreach($tf as $f){
							if($f['name'] == '_title') continue;
							if($f['name'] == '_date_start') continue;
							$v = Templates::getTemplateFieldValue($f);
							if(is_array($v)) $v = implode(', ', $v);
							$f['value'] = $v;
							$properties[] = array(
								'name' => $f['name']
								,'title' => $f['title']
								,'type' => $f['type']
								,'cfg' => $f['cfg']
								,'value' => $v
							);
						}
						$rez[$key]['data']['properties'] = $properties;
					}

					break;
				case 'actions':
					$s= new Search();
					$query->fl = 'id,pid,name,type,subtype,date,template_id,cid';
					$query->types = array(4);
					$query->sort = array('date desc');
					$rez[$key] = $s->query($query);
					unset($s);
					break;
				case 'tasks':
					$s= new Search();
					$query->fl = 'id,name,type,date,date_end,cid,user_ids';
					$query->types = array(6,7);
					$query->sort = array('date desc');
					$rez[$key] = $s->query($query);
					unset($s);
					break;
				case 'milestones':
					$rez[$key] = array();
					break;
			}
		}
		return $rez;
	}
	
	private function getLockInfo($case_id){
		$rez = false;
		return $rez;
		$st = "SELECT u.id user_id, u.name, DATE_FORMAT(l.opened, '%d.%m.%Y числа, в %H:%i') `full_date`, HOUR(diff) `hours`, MINUTE(diff) `minutes`, ".
			  "CONCAT(CASE HOUR(diff)  WHEN 0 THEN '' WHEN 1 THEN '1 ".L\hour."' WHEN 2 THEN '2 ".L\hours."' WHEN 3 THEN '3 ".L\hours."' WHEN 4 THEN '4 ".L\hours."' ELSE CONCAT(HOUR(diff), ' ".L\ofHours."') END, ".
			  "CASE WHEN HOUR(diff) + MINUTE(diff)  = 0 THEN ' 1 ".L\minute."' WHEN MINUTE(diff) = 0 THEN '' ELSE CONCAT(MINUTE(DIFF), ' ".L\min."') END) `time_passed` ".
			  "FROM (SELECT od.user_id, od.opened, TIMEDIFF(NOW(), opened) AS diff FROM opened_cases od WHERE od.case_id =$case_id ORDER BY opened DESC LIMIT 1) l ".
			  "LEFT JOIN users_groups u ON l.user_id = u.id";
		$res = mysqli_query_params($st) or die(mysqli_query_error());
		if($row = $res->fetch_assoc())
			if($row['user_id'] != $_SESSION['user']['id']) $rez = $row;
		$res->close();
		return $rez;
	}

	public function lock($id){
		/* SECURITY check */
		if( !Security::canReadCase($id) ) throw new Exception(L\No_access_for_this_action);
		/* end of SECURITY check */
		$st = 'INSERT INTO opened_cases (case_id, user_id, opened) VALUES ($1, $2, CURRENT_TIMESTAMP) '.
			  'ON DUPLICATE KEY UPDATE user_id = $2, opened = CURRENT_TIMESTAMP';
		mysqli_query_params($st, Array($id, $_SESSION['user']['id'])) or die(mysqli_query_error());
		Log::add(Array('action_type' => 6, 'case_id' => $id));
		return Array('success' => true);
	}

	public function unlock($id){
		/* SECURITY check */
		if( !Security::canReadCase($id) ) throw new Exception(L\No_access_for_this_action);
		/* end of SECURITY check */
		$st = 'delete from opened_cases where case_id = $1 and user_id = $2';
		mysqli_query_params($st, Array($id, $_SESSION['user']['id'])) or die(mysqli_query_error());
		Log::add(Array('action_type' => 7, 'case_id' => $id));
		return Array('success' => true);
	}

	private function satisfyTagsLimit($filterValue, $filterCriteria = 'or', $value){
		if(empty($filterValue)) return true;
		if(!is_array($filterValue)) $filterValue = explode(',', $filterValue);
		if(!is_array($value)) $value = explode(',', $value);
		switch($filterCriteria){
			case 'or': 
				$a = array_intersect ($filterValue, $value);
				if(empty($a)) return false;
				break;
			case 'and':
				$a = array_diff ($filterValue, $value);
				if(!empty($a)) return false;
				break;
			case 'exact':
				$a = array_intersect ($filterValue, $value);
				$s = sizeof($a);
				if( ($s != sizeof($filterValue)) || ($s != sizeof($value)) ) return false;
				break;
		}
		return true;
	}
	static function getCaseTagIds($case_id){
		$rez = array();
		$res = mysqli_query_params('select tag_id, level from cases_tags where case_id = $1', $case_id) or die(mysqli_query_error());
		while($r = $res->fetch_row()) $rez[$r[1]][] = intval($r[0]);
		$res->close();
		return $rez;
	}
	static function getFileTagIds($file_id){
		$rez = array();
		$res = mysqli_query_params('select level, tag_id from files_tags where file_id = $1 and level >= 3', $file_id) or die(mysqli_query_error());
		while($r = $res->fetch_row()) $rez[$r[0]][] = intval($r[1]);
		$res->close();
		return $rez;
	}
	private function getFileTags($file_id){
		$rez = array();
		$res = mysqli_query_params('select ot.level , t.id, t.l'.UL_ID().' from files_tags ot join tags t on ot.tag_id = t.id '.
			'where ot.file_id = $1 order by ot.level, t.order, 3', $file_id) or die(mysqli_query_error());
		while($r = $res->fetch_row()) $rez[$r[0]][] = array( 'id' => $r[1], 'name' => $r[2]);
		$res->close();
		return $rez;
	}
	/*private function getFileDerivedTags($file_id){
		$rez = array();
		$res = mysqli_query_params('SELECT level, tag_id, l'.UL_ID().' FROM v_files_derived_tags WHERE file_id = $1 order by level, `order`, 3', $file_id) or die(mysqli_query_error());
		while($r = $res->fetch_row()) $rez[$r[0]][] = array( 'id' => $r[1], 'name' => $r[2]);
		$res->close();
		return $rez;
	}/**/
	public function toggleCaseTag($params){
		//case_id, $tag_id
		/* SECURITY check */
		if( $params->tag_level < 3) throw new Exception(L\No_access_for_this_action);
		if( !Security::canWriteCase($params->case_id) ) throw new Exception(L\No_access_for_this_action);
		/* end of SECURITY check */
		if($params->action)
			mysqli_query_params('insert into cases_tags (case_id, tag_id, level) values($1, $2, $3)', Array($params->case_id, $params->tag_id, $params->tag_level )) or die(mysqli_query_error());
		else mysqli_query_params('delete from cases_tags where case_id = $1 and tag_id = $2 and level = $3', Array($params->case_id, $params->tag_id, $params->tag_level )) or die(mysqli_query_error());
		return array('success' => true, 'data' => $this->getCaseTagIds($params->case_id));
	}
	
	public function updateCaseTags($params){
		//case_id, $tags
		/* SECURITY check */
		if( $params->tag_level < 2) throw new Exception(L\No_access_for_this_action);
		if( !Security::canWriteCase($params->case_id) ) throw new Exception(L\No_access_for_this_action);
		/* end of SECURITY check */
		$tags = array_filter($params->tags, 'is_numeric');
		mysqli_query_params('delete from cases_tags where case_id = $1 and level = $2 and tag_id not in(0'.implode(',', $tags).')', array($params->case_id, $params->tag_level)) or die(mysqli_query_error());
		if(!empty($tags)) mysqli_query_params('insert into cases_tags (case_id, level, tag_id) values($1, $2,'.implode('),($1, $2,', $tags).') on duplicate key update tag_id = tag_id', array($params->case_id, $params->tag_level)) or die(mysqli_query_error());
		return array('success' => true, 'data' => $this->getCaseTagIds($params->case_id));
	}
	public function changeName($params){
		//id, $name
		/* SECURITY check */
		if( !Security::canWriteCase($params->id) ) throw new Exception(L\No_access_for_this_action);
		/* end of SECURITY check */
		$params->name = trim($params->name);
		if(empty($params->name)) return array('success' => false, 'msg' => L\Error);
		mysqli_query_params('update cases set name = $1 where id = $2', array($params->name, $params->id)) or die(mysqli_query_error());
		return array('success' => true, 'name' => $params->name);
	}
	public function updateFileTags($params){
		//case_id, $tags
		/* SECURITY check */
		if( !Security::canWriteCase($this->getId(false, $params->id)) ) throw new Exception(L\No_access_for_this_action);
		/* end of SECURITY check */
		for($i =3; $i<5; $i++)
			if(isset($params->tags->{$i})){
				$tags = array_filter($params->tags->{$i}, 'is_numeric');
				mysqli_query_params('delete from files_tags where file_id = $1 and level = $2 and tag_id not in(0'.implode(',', $tags).')', array($params->id, $i)) or die(mysqli_query_error());
				if(!empty($tags)) mysqli_query_params('insert into files_tags (file_id, level, tag_id) values($1,$2,'.implode('),($1,$2,', $tags).') on duplicate key update tag_id = tag_id', array($params->id, $i)) or die(mysqli_query_error());
			}
		$t = $this->getFileTags($params->id);
		$sys_tags = array();
		$sys_tags_text = array();
		$user_tags = array();
		$user_tags_text = array();
		if(!empty($t[3]))
			foreach($t[3] as $tag){
				$sys_tags[] = $tag['id'];
				$sys_tags_text[] = $tag['name'];
			}
		$sys_tags_text = implode(', ', $sys_tags_text);
		if(!empty($t[4]))
			foreach($t[4] as $tag){
				$user_tags[] = $tag['id'];
				$user_tags_text[] = $tag['name'];
			}
		$user_tags_text = implode(', ', $user_tags_text);
		return array('success' => true, 'data' => array('id' => $params->id,'sys_tags' => $sys_tags, 'sys_tags_text' => $sys_tags_text, 'user_tags' => $user_tags, 'user_tags_text' => $user_tags_text));
	}
	
	// public function toggleFavorite($p){
	// 	if(!Security::canReadCase($p->id)) throw new exception(L\No_access_for_this_action);
	// 	if($p->favorite == 1) mysqli_query_params('insert into favorites (user_id, case_id) values($1, $2) on duplicate key update case_id = 2', array($_SESSION['user']['id'], $p->id) ) or die(mysqli_query_error());
	// 	else mysqli_query_params('delete from favorites where user_id = $1 and case_id = $2', array($_SESSION['user']['id'], $p->id) ) or die(mysqli_query_error());
	// 	return array('success' => true, 'data' => $p);
	// }
	
	public function getFavorites(){
		$rez =  array('success' => true, 'data' => array());
		$res = mysqli_query_params('select c.id, c.name, c.udate from favorites f join cases c on f.case_id = c.id where f.user_id = $1 order by c.udate desc, c.name', $_SESSION['user']['id'] ) or die(mysqli_query_error());
		while($r = $res->fetch_assoc()){
			$r['udate'] = formatPastTime($r['udate']);
			$rez['data'][] = $r;
		}
		$res->close();
		return $rez;
	}

	public function getCaseId($p){
		$rez = array('success' => false);
		$params = array();
		if(!empty($p->nr)){
			$sql = 'select id from cases where nr = $1';
			$params[] = $p->nr;
		}elseif(!empty($p->object_id)){
			$sql = 'select case_id from objects where id = $1';
			$params[] = $p->object_id;
		}
		$res = mysqli_query_params($sql, $params) or die(mysqli_query_error());
		if( ($r = $res->fetch_row()) && Security::canReadCase($r[0])) $rez = array('success' => true, 'data' => array('id' => $r[0]));
		$res->close();
		return $rez;
	}
	public static function isOpened($case_id, $user_id = false){
		/* checks if current user has opened the specified case */
		$rez = false;
		$res = mysqli_query_params('select opened from opened_cases where case_id = $1 and user_id = $2', Array($case_id, coalesce($user_id, $_SESSION['user']['id']) )) or die(mysqli_query_error());
		if($r = $res->fetch_assoc()) $rez = !empty($r['opened']);
		$res->close();
		return $rez;
	}
	public static function getId($object_id = false, $file_id = false, $task_id = false){
		$case_id = null;
		$sql = 'select f_get_objects_case_id($1)';
		$res = mysqli_query_params($sql, $object_id) or die(mysqli_query_error());
		if($r = $res->fetch_row()) $case_id = $r[0];
		$res->close();
		//if(!$case_id) throw new Exception(L('Object_not_found'));
		return $case_id;	
	}
	public static function getName($case_id = false){
		/*function deemed to get case name by its Id*/
		$rez = false;
		$res = mysqli_query_params('select name from cases where id = $1',  $case_id) or die(mysqli_query_error());
		if($r = $res->fetch_row()) $rez = $r[0];
		$res->close();
		if(!$rez) throw new Exception(L('Object_not_found'));
		return $rez;
	}

	public static function getAssociatedCases($p){
		$data = array();
		if(is_numeric($p)) $p = json_decode('{"case_id": '.$p.'}');
		if(empty($p->case_id)) return array('success' => true, 'data' => $data);
		
		// SECURITY: check if this objects case is opened by current user 
		if(!Security::checkIfCaseOpened($p->case_id)) throw new Exception(L\case_not_oppened);
		// end of SECURITY: check if this objects case is opened by current user 
		// SECURITY: check if current user has at least read access to this case
		if(!Security::canReadCase($p->case_id)) throw new Exception(L\Access_denied);
		// end of SECURITY: check if current user has at least read access to this case
		
		/* select distinct associated case ids from the case */
		$sql = 'SELECT DISTINCT d.value
		FROM objects co
		JOIN templates_structure s ON co.template_id = s.template_id AND s.type = \'_case\'
		JOIN objects_data d on. d.field_id = s.id
		WHERE co.case_id = $1';
		$case_ids = array();
		$res = mysqli_query_params($sql, $p->case_id) or die(mysqli_query_error());
		while($r = $res->fetch_row()){
			$ids = explode(',',$r[0]);
			foreach($ids as $id) if(!empty($id)) $case_ids[$id] = 1;
		}
		$res->close();
		$case_ids = array_keys($case_ids);
		if(empty($case_ids)) return array('success' => true, 'data' => array());
		/* end of select distinct case ids ids from the case */
		$sql = 'select distinct id, name from cases where id in ('.implode(',', $case_ids).') order by 2';
		$res = mysqli_query_params($sql, $query) or die(mysqli_query_error());
		while($r = $res->fetch_row()){
			/*$r[1] = empty($r[1]) ? '' : '<span class="cG">'.L\Nr.' '.$r[1].'</span> ';
			$r[1] .= $r[2];
			unset($r[2]);/**/
			$data[] = $r;
		}
		$res->close();
		return array('success' => true, 'data' => $data);
	}

	public static function getAssociatedObjects($p){
		$data = array();
		if(is_numeric($p)) $p = json_decode('{"case_id": '.$p.'}');
		if(empty($p->case_id)) return array('success' => true, 'data' => $data);
		
		// SECURITY: check if this objects case is opened by current user 
		if(!Security::checkIfCaseOpened($p->case_id)) throw new Exception(L\case_not_oppened);
		// end of SECURITY: check if this objects case is opened by current user 
		// SECURITY: check if current user has at least read access to this case
		if(!Security::canReadCase($p->case_id)) throw new Exception(L\Access_denied);
		// end of SECURITY: check if current user has at least read access to this case
		
		/* select distinct associated case ids from the case */
		$sql = 'SELECT DISTINCT d.value
		FROM objects co
		JOIN templates_structure s ON co.template_id = s.template_id AND s.type = \'_case_object\'
		JOIN objects_data d on. d.field_id = s.id
		WHERE co.case_id = $1';
		$case_objects_ids = array();
		$res = mysqli_query_params($sql, $p->case_id) or die(mysqli_query_error());
		while($r = $res->fetch_row()){
			$ids = explode(',',$r[0]);
			foreach($ids as $id) if(!empty($id)) $case_objects_ids[$id] = 1;
		}
		$res->close();
		$case_objects_ids = array_keys($case_objects_ids);
		if(empty($case_objects_ids)) return array('success' => true, 'data' => array());
		/* end of select distinct associated case ids from the case */
		$sql = 'select distinct co.id, coalesce(co.custom_title, co.title), t.iconCls from objects co left join templates t on co.template_id = t.id   where co.id in ('.implode(',', $case_objects_ids).') order by 2';
		$res = mysqli_query_params($sql) or die(mysqli_query_error());
		while($r = $res->fetch_row()) $data[] = $r;
		$res->close();
		return array('success' => true, 'data' => $data);
	}

	static function getSorlData($id){
		$rez = array();
		$sql = 'SELECT 
			c.id
			,c.cid
			,c.name
			FROM cases c where id = $1';
		$res = mysqli_query_params($sql, $id) or die(mysqli_query_error()."\n".$sql);
		if($r = $res->fetch_assoc()){
			$rez['content'] = '';//$r['name'];	
		}
		$res->close();
		return $rez;
	}
}