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
		,'template_type'
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
		// custom core fields
		,'substatus'
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
	function addDocument($d){
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
			echo "\n\nError (id={".$doc->id."}): {$e->__toString()}\n";	
			return false;
		}
		return true;
	}

	function updateDocuments($docs){
		$url = 'http://localhost:8983'.$this->core.'/update/json';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type:application/json; charset=utf-8") );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($docs) );
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLINFO_HEADER_OUT, 1);

		$data = curl_exec($ch);

		if (curl_errno($ch)) {
			print "curl_error:" . curl_error($ch);
		}
	}

	function addDocuments(&$docs){
		$addDocs = array();
		$updateDocs = array();
		for ($i=0; $i < sizeof($docs); $i++) { 
			if( empty($docs[$i]['update']) ){
				$doc = new \Apache_Solr_Document();
				foreach($docs[$i] as $fn => $fv) 
					if( in_array($fn, $this->solr_fields) && ( ($fn == 'dstatus') || !empty($fv) || ($fv === false)) )
						$doc->$fn = $fv;
				fireEvent('beforeNodeSolrUpdate', $doc);
				$addDocs[] = $doc;
			}else{
				$doc = array();
				foreach($docs[$i] as $fn => $fv) 
					if( in_array($fn, $this->solr_fields) )
						$doc[$fn] = $fv;
				//htmlspecialchars($multivalue, ENT_NOQUOTES, 'UTF-8')
				$updateDocs[] = $doc;
			}
		}
		try {
			if(!empty($addDocs)) $this->solr->addDocuments($addDocs);
			if(!empty($updateDocs)) $this->updateDocuments($updateDocs);

		} catch (\Exception $e) {
			echo "\n\n-------------------------------------------";
			echo "\n\nError (adding multiple documents): {$e->__toString()}\n";
			print_r($addDocs);
			print_r($updateDocs);
			return false;
		}
		for ($i=0; $i < sizeof($addDocs); $i++)
			fireEvent('nodeSolrUpdate', $addDocs[$i]);
		for ($i=0; $i < sizeof($updateDocs); $i++)
			fireEvent('nodeSolrUpdate', $updateDocs[$i]);
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
	static public function runBackgroundCron(){
		$cmd = 'php -f '.CRONS_PATH.'run_cron.php solr_update_tree '.CORENAME.' all > '.CRONS_PATH.'bg_solr_update_tree.log &';
		if(is_windows()) $cmd = 'start /D "'.CRONS_PATH.'" php -f run_cron.php '.CORENAME.' > '.CRONS_PATH.'bg_solr_update_tree.log';
		pclose(popen($cmd, "r"));
	}
	public function updateTree($all = false, $cron_id = false){
		$this->connect();
		// $log_file = dirname(__FILE__).DIRECTORY_SEPARATOR.'update_tree_'.CORENAME.'_log';
		// error_log("\n\rStart at ".date('H:i:s')."\n\r", 3, $log_file);
		
		$lastId = 0;
		$sql = 'SELECT t.id, t.pid, f_get_tree_pids(t.id) `pids`, f_get_tree_path(t.id) `path`, t.name, t.`system`, t.`type`, t.subtype, t.template_id, t.target_id
			,CASE WHEN t.type = 2 then (SELECT `type` FROM tree WHERE id = t.target_id) ELSE null END `target_type`
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
			,t.case_id
			,nt.`type` template_type
			,t.updated
			FROM tree t 
			LEFT JOIN templates nt ON t.template_id = nt.id
			where '.($all ? ' (t.id > $1) ORDER BY t.id ' : ' t.updated > 0 ').
			'limit 200';
		
		$security = new Security();
		
		$cases_info = array();
		
		$docs = true;
		while( !empty($docs) ){
			$docs = array();

			$res = DB\mysqli_query_params($sql, $lastId) or die(DB\mysqli_query_error());
			while($r = $res->fetch_assoc()){
				$lastId = $r['id'];
				if( $all || ($r['updated'] & 1) ){ //update all object info
					$security->setObjectSolrAccessFields($r);
					
					$id = $r['id'];
					$type = $r['type'];
					if($r['type'] == 2){
						$id = $r['target_id']; //link
						$type = $r['target_type']; //link
					}
					
					if(!empty($r['case_id'])){
						if(!isset($cases_info[$r['case_id']])){
							$cases_info[$r['case_id']] = array('id' => $r['id']);
							$cres = DB\mysqli_query_params('select coalesce(custom_title, title) name from objects where id = $1', $r['case_id']) or die(DB\mysqli_query_error());
							if($cr = $cres->fetch_row()) $cases_info[$r['case_id']]['case'] = $cr[0];
							$cres->close();
							Objects::setCaseRolesFields($cases_info[$r['case_id']]);
							unset($cases_info[$r['case_id']]['id']);
						}
						$r = array_merge($r, $cases_info[$r['case_id']] );
					}
					
					$r['ntsc'] = sizeof($GLOBALS['folder_templates']) + 1;
					$r['content'] = $r['name'];
					
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
					$docs[] = $r;
				
				}elseif( $r['updated'] & 10 ){ // atomic update security info
					$security->setObjectSolrAccessFields($r);
					$docs[] = array(
						'update' => true
						,'id' => $r['id']
						,'allow_user_ids' => array( 'set' => empty($r['allow_user_ids']) ? null : $r['allow_user_ids'] )
						,'deny_user_ids' => array( 'set' => empty($r['deny_user_ids']) ? null : $r['deny_user_ids'] )
					);
				}
				if( !empty($cron_id) ) DB\mysqli_query_params('update crons set last_action = CURRENT_TIMESTAMP where cron_id = $1', $cron_id) or die('error updating crons last action');
				DB\mysqli_query_params('update tree set updated = 0 where id = $1', $r['id']) or die(DB\mysqli_query_error()); 			
			}
			$res->close();
			if( !empty($docs) ){
				// error_log(print_r($docs, 1), 3, $log_file);
				try {
					$this->addDocuments($docs);
				} catch (\Exception $e) {
					// error_log( " \n\r CANNOT add documents\n", 3, $log_file);
				}
				if( !empty($cron_id) ) DB\mysqli_query_params('update crons set last_action = CURRENT_TIMESTAMP where cron_id = $1', $cron_id) or die('error updating crons last action');
				
				try {
					$this->commit();
				} catch (\Exception $e) {
					// error_log( " \n\r CANNOT COMMIT\n", 3, $log_file);
					exit();
				}
				
			}
		}
		unset($security);
		// error_log( "\n\rEnd at ".date('H:i:s')."\n\r", 3, $log_file);
	}

	public function deleteId($id){
		$this->deleteByQuery('id:'.$id.' OR pids:'.$id);
	}
	
	public function deleteByQuery($query){
		$this->connect();
		$this->solr->deleteByQuery($query);
		try {
			$this->commit();
		} catch (\Exception $e) {
			die("Cannot commit after delete\n");
		}
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