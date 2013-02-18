<?php
require_once 'Browser.php';
class BrowserTree extends Browser{
	
	public function getChildren($p){
		$path = empty($p->path) ? '/' : $p->path;
		$rez = array();
		$this->showFoldersContent = isset($p->showFoldersContent) ? $p->showFoldersContent : false;
		if($path == '/'){
			$rez = $this->getRootChildren();
		}else{
			while($path[0] == '/') $path = substr($path, 1);
			$path = explode('/', $path);
			/* look at which root node we are to call the appropriate controller */
			switch (Path::getNodeSubtype($path[0])) {
				case 2: // Favorites
					$rez = $this->getFavoritesChildren($path); break;
				case 3: // My CaseBox
				case 8: // Casebox
					$rez = $this->getCaseboxChildren($path); break;
				case 0: // Root
				default: $rez = $this->getCasesChildren($path); break;
			}
		}
		return $this->updateLabels($rez);
	}

	private function getRootChildren(){
		$data = array();
		$res = mysqli_query_params('select id `nid`, `system`, `type`, `subtype`, `name` from tree where ((user_id = $1) or (user_id is null)) and (system = 1) and (pid is null) order by user_id desc, is_main', $_SESSION['user']['id']) or die(mysqli_query_error());
		while($r = $res->fetch_assoc()){
			$r['expanded'] = true;
			if(!empty($data)) $r['cls'] = 'cb-group-padding';
			$data[] = $r; 	
		}
		$res->close();
		
		require_once 'User.php';
		if(User::checkUserRootFolders($data)) return $data;
		return $this->getRootChildren();
	}
	private function getFavoritesChildren($path){
		$data = array();
		$sql = 'select id `nid`, `system`, `type`, `subtype`, `name`, (select count(*) from tree where pid = t.id) `children` from tree t where pid = $2 order by system, `type`, `subtype`, `name`';
		$res = mysqli_query_params($sql, array($_SESSION['user']['id'], $path[sizeof($path) - 1])) or die(mysqli_query_error());
		while($r = $res->fetch_assoc()){
			if(empty($r['children'])){
				$r['loaded'] = true;
			}
			unset($r['children']);
			$data[] = $r;
		}
		$res->close();
		return $data;
	}
	private function getCaseboxChildren($path){
		$data = array();
		$in_my_casebox = (Path::getNodeSubtype($path[0]) == 3);
		if( (sizeof($path) > 1) && (Path::getNodeSubtype($path[1]) == 4) ){ // cases folder
			switch(sizeof($path)){
				case 2: //offices
					//$data = $this->getCasesOffices($in_my_casebox ? 3 : "read");
					break;
				case 3: //years
					$office = array_pop($path);
					$data = $this->getCasesYears($in_my_casebox ? 3 : "read", $office);
					break;
				case 4: //months
					$year = array_pop($path);
					$office = array_pop($path);
					$data = $this->getCasesMonths($in_my_casebox ? 3 : "read", $office, $year);
					break;
				case 5: //get cases list for the month
					$month = array_pop($path);
					$year = array_pop($path);
					$office = array_pop($path);
					$data = $this->getCasesForMonth($in_my_casebox ? 3 : "read", $office, $year, $month);
					break;
				default: //cases objects
					$data = $this->getCasesChildren($path);
					break;
			}
		}else{
			$sql = 'select id `nid`, `system`, `type`, `subtype`, `name`, cid, cdate, udate '.
				',(select count(*) from tree where pid = t.id) `children` '.
				',case when `type` = 4 then (select template_id from objects where id = t.id) else null end `template_id` '.
				'from tree t where ( (user_id = $1) or (user_id is null)) and (pid = $2)';
			$res = mysqli_query_params($sql, array($_SESSION['user']['id'], $path[sizeof($path) - 1])) or die(mysqli_query_error());
			while($r = $res->fetch_assoc()){
				if(empty($r['children']) && ($r['subtype'] != 4)){
					$r['loaded'] = true;
				}
				unset($r['children']);
				$data[] = $r;
			}
			$res->close();
		}
		return $data;
	}

	private function getCasesYears($access, $office){
		$data = array();
		if( ($access == 'read') && (Security::isAdmin()) ){
			if(!empty($office)) $sql = 'SELECT DISTINCT `year` FROM cases c ORDER BY 1 DESC';
			else $sql = 'SELECT DISTINCT c.year FROM cases c '.
				'LEFT JOIN cases_offices_access coa ON c.id = coa.case_id '.
				'WHERE coa.tag_id is null ORDER BY 1 DESC';
		}elseif($access == 'read')
			$sql = 'SELECT DISTINCT c.year FROM '.
			'cases c ORDER BY 1 DESC';
		else $sql = 'SELECT DISTINCT c.year FROM '.
			'cases c ORDER BY 1 DESC';
		$res = mysqli_query_params($sql, array($office, $_SESSION['user']['id'], $_SESSION['user']['tag_id']) ) or die(mysqli_query_error());
		while($r = $res->fetch_row()){
			$data[] = array('nid' => $r[0], 'system' => 1, 'type' => 0, 'subtype' => 1, 'name' => coalesce($r[0], L\noYear));
		}
		$res->close();
		return $data;
	}

	private function getCasesMonths($access, $office, $year){
		$data = array();
		$year_param = '= $2';
		if(Security::isAdmin() ){
			if(!empty($office)) $sql = 'SELECT DISTINCT `month` FROM cases c WHERE c.year = $2 ORDER BY 1';
			else $sql = 'SELECT DISTINCT c.month FROM cases c '.
				'LEFT JOIN cases_offices_access coa ON c.id = coa.case_id '.
				'WHERE coa.tag_id is null and c.year = $2 ORDER BY 1';
		}elseif($access == 'read')
			$sql = 'SELECT DISTINCT c.month FROM '.
			'cases c '.
			'WHERE c.year = $2 ORDER BY 1 DESC';
		else $sql = 'SELECT DISTINCT c.month FROM '.
			' cases c '.
			'WHERE c.year = $2 ORDER BY 1 DESC';
		$res = mysqli_query_params($sql, array($office, $year, $_SESSION['user']['id'], $_SESSION['user']['tag_id'])) or die(mysqli_query_error());
		while($r = $res->fetch_row()){
			$data[] = array('nid' => $r[0], 'system' => 1, 'type' => 0, 'subtype' =>1, 'name' =>  coalesce($r[0], L\noMonth));
		}
		$res->close();
		return $data;
	}
	private function getCasesForMonth($access, $office, $year, $month){
		$data = array();
		$year_param = '=$2'; //empty($year) ? 'is null' : '= $2';
		$month_param = '=$3'; //empty($month) ? 'is null' : '= $3';
		if( Security::isAdmin() ) {
			if(!empty($office)) $sql = 'SELECT c.id `nid`, c.name, c.`date`, c.cid `cid`, c.cdate `cdate`, c.udate FROM cases c WHERE c.year = $2 and c.month = $3 ORDER BY 2';
			else $sql = 'SELECT c.id `nid`, c.name, c.`date`, c.cid `cid`, c.cdate `cdate`, c.udate FROM cases c '.
				'LEFT JOIN cases_offices_access coa ON c.id = coa.case_id '.
				'WHERE coa.tag_id is null and c.year = $2 and c.month = $3 ORDER BY 1';
		}elseif($access == 'read')
			$sql = 'SELECT c.id `nid`, c.name, c.`date`, c.cid `cid`, c.cdate `cdate`, c.udate FROM '.
			' cases c '.
			'WHERE c.year = $2 and c.month = $3 ORDER BY 1 DESC';
		else $sql = 'SELECT c.id `nid`, c.name, c.`date`, c.cid `cid`, c.cdate `cdate`, c.udate FROM '.
			' cases c '.
			'WHERE c.year = $2 and c.month = $3 ORDER BY 1 DESC';

		$res = mysqli_query_params($sql, array($office, $year, $month, $_SESSION['user']['id'], $_SESSION['user']['tag_id'])) or die(mysqli_query_error());
		while($r = $res->fetch_assoc()){
			$r['system'] = 0;
			$r['type'] = 3;
			$r['tags'] = $this->getObjectTags($r['nid'], 3);
			$data[] = $r; 
		}
		$res->close();
		return $data;
	}
	private function getCasesChildren($path){
		$a = array_filter($path, 'is_numeric');
		if(empty($a)) return array();
		$id = array_pop($path);
		$rez = array();

		$sql = 'select id `nid`, `system`, `type`, `subtype`, `name`, date, size, cid, cdate, udate'. 
			', (select count(*) from tree where pid = t.id'.($this->showFoldersContent ? '' : ' and `type` in (1, 3)').') `loaded` '.
			',case when `type` = 4 then (select template_id from objects where id = t.id) else null end `template_id` '.
			'from tree t where (pid = $1)'.($this->showFoldersContent ? '' : ' and `type` in (1, 3)').' order by `system` desc, `type`, `subtype`, `name`';
		$res = mysqli_query_params($sql, $id) or die(mysqli_query_error());
		while($r = $res->fetch_assoc()){
			$r['loaded'] = empty($r['loaded']);
			$r['tags'] = $this->getObjectTags($r['nid'], $r['type']);
			$rez[] = $r;
		}
		$res->close();
		return $rez;
	} 
	private function getObjectTags($id, $type){
		$tags = null;
		switch($type){
			case 3: require_once('Cases.php');
				$tags = Cases::getCaseTagIds($id);
				break;
			case 4: require_once('Objects.php');
				$tags = Objects::getObjectTagIds($id);
				break;
			case 5: require_once('Cases.php');
				$tags = Cases::getFileTagIds($id);
				break;
		}
		if(empty($tags) || empty($tags[3])) return null;
		return implode(',',$tags[3]);
	}

	public function updateLabels(&$data){
		for ($i=0; $i < sizeof($data); $i++) {
			$d = &$data[$i];
			unset($d['iconCls']);
			@$d['nid'] = intval($d['nid']);
			@$d['system'] = intval($d['system']);
			@$d['type'] = intval($d['type']);
			@$d['subtype'] = intval($d['subtype']);
			switch($d['type']){
				case 0: break; 
				case 1: switch ($d['subtype']) {
						case 1:	if( (substr($d['name'], 0, 1) == '[') && (substr($d['name'], -1, 1) == ']') )
								$d['name'] = L(substr($d['name'], 1, strlen($d['name']) -2));
							break;
						case 2:	$d['name'] = L\Favorites; break;
						case 3:	$d['name'] = L\MyCaseBox; break;
						case 4:	$d['name'] = L\Cases; break;
						case 5:	$d['name'] = L\Tasks; break;
						case 6:	$d['name'] = L\Messages; break;
						case 7:	$d['name'] = L\PrivateArea; break;
						case 8:	break;
						case 9: break;
						case 10: $d['name'] = L\PublicFolder; break;
						default: break;
					}
					break;
				case 2: break;
				case 3: break;
				case 4: break;
				case 5: break;
				case 6: break;
				case 7: break;
			}
		}
		return $data;
	}
}
