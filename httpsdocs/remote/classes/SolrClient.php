<?php

namespace CB;

class SolrClient{
	var $connected = false;
	var $solr = null;
	var $solr_fields = array('id'
		,'pid'
		,'pids'
		,'path'
		,'name'
		,'system'
		,'type'
		,'subtype'
		,'size'
		,'date'
		,'date_end'
		,'oid'
		,'cid'
		,'cdate'
		,'uid'
		,'udate'
		,'did'
		,'ddate'
		,'dstatus'
		,'case_id'
		,'case'
		,'template_id'
		,'user_ids'
		,'allow_user_ids'
		,'deny_user_ids'
		,'status'
		,'category_id'
		,'importance'
		,'completed'
		,'versions'
		,'sys_tags'
		,'tree_tags'
		,'user_tags'
		,'metas'
		,'content'
		,'ntsc'
		,'role_ids1'
		,'role_ids2'
		,'role_ids3'
		,'role_ids4'
		,'role_ids5'
		);
	function SolrClient( $p = array() ){
		$this->host = empty($p['host']) ? config\solr_host : $p['host'];
		$this->port = empty($p['port']) ? config\solr_port : $p['port'];
		$this->core = empty($p['core']) ? config\solr_core : $p['core'];
	}
	function connect(){
		if($this->connected) return $this->solr;
		if(empty($this->host)) $this->host = config\solr_host;
		if(empty($this->port)) $this->port = config\solr_port;
		if(empty($this->core)) $this->core = config\solr_core;

		require_once SOLR_CLIENT;
		$this->solr = new \Apache_Solr_Service($this->host, $this->port, $this->core );
		if (! $this->solr->ping()){
			throw new \Exception(L\get('Solr_connection_error').( is_debug_host() ? ' ('.$this->host.':'.$this->port.' -> '.$this->core.' )' : ''), 1);
		}
		$this->connected = true;
		return $this->solr;
	}
	function add($d){
		$doc = new \Apache_Solr_Document();
		foreach($d as $fn => $fv) 
			if( in_array($fn, $this->solr_fields) && ( ($fn == 'dstatus') || !empty($fv) || ($fv === false)) )
				$doc->$fn = $fv;
		try {
			fireEvent('beforeNodeSolrUpdate', $doc);
			$this->solr->addDocument($doc);
			fireEvent('nodeSolrUpdate', $doc);
		} catch (\Exception $e) {
			echo "\n\n-------------------------------------------";
			echo "\n\nError (id={$d['id']}): {$e->__toString()}\n";	
			return false;
		}
		return true;
	}
	function commit(){
		$this->solr->commit();
	}

	static public function runCron(){
		$solr = new SolrClient();
		$solr->connect();
		$solr->updateTree();
		unset($solr);
	}
	public function updateTree($all = false){
		$sql = 'select t.id, t.pid, f_get_tree_pids(t.id) `pids`, f_get_tree_path(t.id) `path`, t.name, t.`system`, t.`type`, t.subtype, t.template_id, t.target_id
			,case when t.type = 2 then (select `type` from tree where id = t.target_id) else null end `target_type`
			,DATE_FORMAT(t.`date`, \'%Y-%m-%dT%H:%i:%sZ\') `date`
			,DATE_FORMAT(t.`date_end`, \'%Y-%m-%dT%H:%i:%sZ\') `date_end`
			,t.oid
			,t.cid
			,DATE_FORMAT(t.cdate, \'%Y-%m-%dT%H:%i:%sZ\') `cdate`		
			,t.uid
			,DATE_FORMAT(t.udate, \'%Y-%m-%dT%H:%i:%sZ\') `udate`
			,t.did
			,DATE_FORMAT(t.ddate, \'%Y-%m-%dT%H:%i:%sZ\') `ddate`
			,t.dstatus
			,f_get_objects_case_id(t.id) `case_id`
			,nt.`type` template_type
			from tree t 
			left join templates nt on t.template_id = nt.id
			'.($all ? '' : ' where t.updated = 1');
		$res = DB\mysqli_query_params($sql) or die(DB\mysqli_query_error());
		$k = 0;
		if($r = $res->fetch_assoc()){
			$security = new Security();
			do{
				$security->setObjectSolrAccessFields($r);
				$id = $r['id'];
				$type = $r['type'];
				if($r['type'] == 2){
					$id = $r['target_id']; //link
					$type = $r['target_type']; //link
				}
				if(!empty($r['case_id'])){
					$cres = DB\mysqli_query_params('select coalesce(custom_title, title) name from objects where id = $1', $r['case_id']) or die(DB\mysqli_query_error());
					if($cr = $cres->fetch_row()) $r['case'] = $cr[0];
					$cres->close();
					Objects::setCaseRolesFields($r);
				}
				$r['ntsc'] = sizeof($GLOBALS['folder_templates'])+1;
				switch($r['template_type']){
					case 'case':
						$r['ntsc']--;
						$r = array_merge($r, Objects::getSorlData($id));
						break;
					case 'object':
					case 'email':
						$r = array_merge($r, Objects::getSorlData($id));
						break;
					case 'file':
						$r = array_merge($r, Files::getSorlData($id));
						break;
					case 'task':
						$r = array_merge($r, Tasks::getSorlData($id));
						break;
				}

				$folder_index = array_search($r['template_id'], $GLOBALS['folder_templates']);
				if($folder_index !== false) $r['ntsc'] = $folder_index;

				$r['ntsc'] = intval($r['ntsc']);
				$r['system'] = intval($r['system']);
				$r['type'] = intval($r['type']);
				$r['subtype'] = intval($r['subtype']);
				$r['pids'] = empty($r['pids']) ? null : explode(',', $r['pids']);
				$this->add( $r );
				
				DB\mysqli_query_params('update tree set updated = -1 where id = $1', $r['id']) or die(DB\mysqli_query_error()); 			
				
				$k++;

				if ($k % 200 == 0) $this->commit();
			}while($r = $res->fetch_assoc());
			$this->commit();
			unset($security);
		}
		$res->close();
	}
	public function deleteId($id){
		$this->deleteByQuery('id:'.$id.' OR pids:'.$id);
	}
	
	public function deleteByQuery($query){
		$this->connect();
		$this->solr->deleteByQuery($query);
		$this->commit();
	}
	
	public function optimize(){
		$this->connect();
		$this->solr->optimize();
		$this->commit();
	}

/* ----------------------- functions ---------------------------------*/
	function current_week_diapazon(){
	  	$time1 = strtotime('previous monday');
	  	$time2 = strtotime('previous monday + 1 week');
		return date('Y-m-d\TH:i:s\Z', $time1).' TO '.date('Y-m-d\TH:i:s\Z', $time2);
	} 
}
?>