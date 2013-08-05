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
		
		$res = DB\mysqli_query_params('SELECT id `nid`
			     , `system`
			     , `type`
			     , `subtype`
			     , `name`
			FROM tree
			WHERE ((user_id = $1)
			       OR (user_id IS NULL))
			        AND (SYSTEM = 1)
			        AND (pid IS NULL)
			ORDER BY user_id DESC, is_main'

			,$_SESSION['user']['id']
		) or die(DB\mysqli_query_error());

		while($r = $res->fetch_assoc()){
			$r['expanded'] = true;
			if(!empty($data)) $r['cls'] = 'cb-group-padding';
			$data[] = $r; 	
		}
		$res->close();
		
		return $data;
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
