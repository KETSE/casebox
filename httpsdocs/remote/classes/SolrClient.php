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
		$sql = 'select id, pid, f_get_tree_pids(id) `pids`, f_get_tree_path(id) `path`, name, `system`, `type`, subtype, target_id
			,case when type = 2 then (select `type` from tree where id = t.target_id) else null end `target_type`
			,DATE_FORMAT(`date`, \'%Y-%m-%dT%H:%i:%sZ\') `date`
			,DATE_FORMAT(`date_end`, \'%Y-%m-%dT%H:%i:%sZ\') `date_end`
			,oid
			,cid
			,DATE_FORMAT(cdate, \'%Y-%m-%dT%H:%i:%sZ\') `cdate`		
			,uid
			,DATE_FORMAT(udate, \'%Y-%m-%dT%H:%i:%sZ\') `udate`
			,did
			,DATE_FORMAT(ddate, \'%Y-%m-%dT%H:%i:%sZ\') `ddate`
			,dstatus
			,f_get_objects_case_id(id) `case_id`
			from tree t '.($all ? '' : ' where updated = 1');
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
					$cres = DB\mysqli_query_params('select name from cases where id = $1', $r['case_id']) or die(DB\mysqli_query_error());
					if($cr = $cres->fetch_row()) $r['case'] = $cr[0];
					$cres->close();
					Objects::setCaseRolesFields($r);
				}
				
				switch($type){
					case 1: //folder
						$r['content'] = $r['name'];
						$r['ntsc'] = 1;
						break;
					case 3: //case
						$r = array_merge($r, Cases::getSorlData($id));
						$r['ntsc'] = 2;
						break;
					case 4: //case object
					case 8: //Emails
					case 9: //Contact
						$r = array_merge($r, Objects::getSorlData($id));
						$r['ntsc'] = 4;
						break;
					case 5: //file
						$r = array_merge($r, Files::getSorlData($id));
						$r['ntsc'] = 4;
						break;
					case 6: //tasks
					case 7: //tasks
						$r = array_merge($r, Tasks::getSorlData($id));
						$r['ntsc'] = 4;
						break;
				}
				$r['system'] = intval($r['system']);
				$r['type'] = intval($r['type']);
				$r['subtype'] = intval($r['subtype']);

				$r['pids'] = empty($r['pids']) ? null : explode(',', $r['pids']);
				// $r['sort_path'] = mb_strtolower($r['name'], 'UTF-8');
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