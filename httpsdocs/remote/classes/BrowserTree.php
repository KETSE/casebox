<?php

namespace CB;

class BrowserTree extends Browser{
	
	public function getChildren($p){
		$path = empty($p->path) ? '/' : $p->path;
		$rez = array();
		$this->showFoldersContent = isset($p->showFoldersContent) ? $p->showFoldersContent : false;
		if($path == '/') $rez = $this->getRootChildren();
		else{
			$rez = $this->getCustomControllerResults($path);
			if($rez === false) $rez = $this->getDefaultControllerResults($path);
		}
		$this->prepareResults($rez);
		return $this->updateLabels($rez);
	}

	private function getRootChildren(){
		$data = array();
		$res = DB\mysqli_query_params('select id `nid`, `system`, `type`, `subtype`, `name` from tree where ((user_id = $1) or (user_id is null)) and (system = 1) and (pid is null) order by user_id desc, is_main', $_SESSION['user']['id']) or die(DB\mysqli_query_error());
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

	private function getDefaultControllerResults($path){
		$path = explode('/', $path);
		$a = array_filter($path, 'is_numeric');
		if(empty($a)) return array();
		$id = array_pop($path);
		
		$p = (Object)array('pid' => $id, 'fl' => 'id,system,type,subtype,name,date,size,cid,cdate,uid,udate,template_id');

		if(!$this->showFoldersContent) $p->templates = $GLOBALS['folder_templates'];

		$s = new Search();
		$rez = $s->query($p);
		$rez = $rez['data'];
		return $rez;
	}

}
