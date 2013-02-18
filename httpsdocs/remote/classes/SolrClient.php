<?php

class SolrClient{
	var $connected = false;
	var $solr = null;
	function SolrClient($p = array() ){
		$this->host = empty($p['host']) ? CB_SOLR_HOST : $p['host'];
		$this->port = empty($p['port']) ? CB_SOLR_PORT : $p['port'];
		$this->core = empty($p['core']) ? CB_SOLR_CORE : $p['core'];
	}
	function connect(){
		if($this->connected) return $this->solr;
		require_once CB_SOLR_CLIENT;
		$this->solr = new Apache_Solr_Service($this->host, $this->port, $this->core );
		if (! $this->solr->ping()){
			throw new Exception(L('Solr_connection_error').( is_debug_host() ? ' ('.$this->host.':'.$this->port.' -> '.$this->core.' )' : ''), 1);
		}
		$this->connected = true;
		return $this->solr;
	}
	function add($d){
		$solr_fields = 'id,pid,pids,path,name,system,type,subtype,size,date,date_end,iconCls,cid,cdate,uid,udate,case_id,case,template_id,user_ids,access_user_ids,status,category_id,importance,versions,sys_tags,user_tags,metas,content,ntsc,sort_path';
		$solr_fields = explode(',', $solr_fields);
		$doc = new Apache_Solr_Document();
		//var_dump($d);
		foreach($d as $fn => $fv) if( in_array($fn, $solr_fields) && (!empty($fv) || ($fv === false)) ){
			$doc->$fn = $fv;
		}
		try {
			$this->solr->addDocument($doc);
		} catch (Exception $e) {
			var_dump($doc);
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
		$sql = 'select last_end_time from crons where cron_id = \'solr_update_tree\'';
		$res = mysqli_query_params($sql) or die(mysqli_query_error());
		$running = false;
		if($r = $res->fetch_row())
			if(empty($r[0])) $running = true;
		$res->close();
		if(!$running){
			//echo 'php -f "'.CB_CRONS_PATH.'cron_solr_update_tree.php"';
			exec('php -f "'.CB_CRONS_PATH.'cron_solr_update_tree.php"');
		}
	}
	public function deleteId($id){
		$this->deleteByQuery('id:'.$id.' OR pids:'.$id);
	}
	
	public function deleteByQuery($query){
		$this->connect();
		$this->solr->deleteByQuery($query);
		$this->commit();
	}

/* ----------------------- functions ---------------------------------*/
	function current_week_diapazon(){
	  	$time1 = strtotime('previous monday');
	  	$time2 = strtotime('previous monday + 1 week');
		return date('Y-m-d\TH:i:s\Z', $time1).' TO '.date('Y-m-d\TH:i:s\Z', $time2);
		//echo $time1.' = '.date('Y-m-d H:i:s', $time1)."\n";
	 	//echo $time2.' = '.date('Y-m-d H:i:s', $time2)."\n";
	} 
}
?>