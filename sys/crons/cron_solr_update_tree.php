#!/usr/bin/php
<?php
require_once 'crons_init.php';
require_once CB_SOLR_CLIENT;
$cron_id = 'solr_update_tree';
$where = '';
$where2 = '';
foreach($CB_cores as $core){
	$GLOBALS['CB_LANGUAGE'] = $core['default_language'];

	$res = mysqli_query_params('use `'.$core['db_name'].'`');
	if(!$res) continue;
	
	echo 'Processing core '.$core['db_name'].' ... ';
	if(empty($update_all)){
		$cd = prepare_cron($cron_id, 2, 'core: '.$core['db_name']);
		if(!$cd['success']) exit(1);
		if(empty($update_all)){
			$where = ' where updated = 1';
			if(!empty($cd['last_start_time'])) $where2 = ' where udate >= \''.$cd['last_start_time'].'\'';
		}
	}//$solr variable created outside by manual update all script
	
	
	$rez = Array('Total' => 0, 'Processed' => 0, 'Not found'=> 0);
	
	$sql = 'select id, pid, f_get_tree_pids(id) `pids`, f_get_tree_path(id) `path`, name, `system`, `type`, subtype, target_id
		,case when type = 2 then (select `type` from tree where id = t.target_id) else null end `target_type`
		,DATE_FORMAT(`date`, \'%Y-%m-%dT%H:%i:%sZ\') `date`
		,DATE_FORMAT(`date_end`, \'%Y-%m-%dT%H:%i:%sZ\') `date_end`
		,cid
		,DATE_FORMAT(cdate, \'%Y-%m-%dT%H:%i:%sZ\') `cdate`		
		,uid
		,DATE_FORMAT(udate, \'%Y-%m-%dT%H:%i:%sZ\') `udate`
		,f_get_objects_case_id(id) `case_id`
		from tree t '.$where;
		//,f_get_sort_path(id) sort_path
	$res = mysqli_query_params($sql, array()) or die(mysqli_query_error());
	$k = 0;
	if($r = $res->fetch_assoc()){
		$solr = new SolrClient(array('core' => '/solr/'.$core['db_name'] ));
		$solr->connect();
		initLanguages();

		do{
			$id = $r['id'];
			$type = $r['type'];
			if($r['type'] == 2){
				$id = $r['target_id']; //link
				$type = $r['target_type']; //link
			}
			if(!empty($r['case_id'])){
				// echo "\n".$r['case_id'];
				$cres = mysqli_query_params('select name from cases where id = $1', $r['case_id']) or die(mysqli_query_error());
				if($cr = $cres->fetch_row()) $r['case'] = $cr[0];
				$cres->close();
				// echo " ".$r['case'];
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
					$r = array_merge($r, Objects::getSorlData($id));
					//echo "\n".@$r['id'].', '.$r['name'].', '.$r['case_id'].', '.$r['template_id']."\n";
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
					//var_dump($r);
					break;
				case 8: //Emails
					$r = array_merge($r, Objects::getSorlData($id));
					$r['ntsc'] = 4;
					break;
				case 9: //Contact
					$r = array_merge($r, Objects::getSorlData($id));
					$r['ntsc'] = 4;
					break;
			}
			$r['system'] = intval($r['system']);
			$r['type'] = intval($r['type']);
			$r['subtype'] = intval($r['subtype']);

			$r['pids'] = empty($r['pids']) ? null : explode(',', $r['pids']);
			$r['sort_path'] = mb_strtolower($r['name'], 'UTF-8');
			//echo $r['sort_path']."\n";
			//echo $r['path']."\n";
			//if($type == 4) var_dump($r);
			$solr->add( $r );
			
			mysqli_query_params('update tree set updated = -1 where id = $1', $r['id']) or die(mysqli_query_error()); 			
			
			if ($k % 10 == 0) echo '.';
			$k++;

			if ($k % 200 == 0) $solr->commit();
		}while($r = $res->fetch_assoc());
		echo "Commit ...";
		$solr->commit();
	}
	$res->close();
	echo "OK ... ";
	$rez['Total'] = $rez['Processed'] + $rez['Not found'];
	mysqli_query_params('update crons set last_end_time = CURRENT_TIMESTAMP, execution_info = $2 where cron_id = $1', array($cron_id, json_encode($rez)) ) or die(mysqli_query_error()."\n".$sql);
	unset($solr);
	echo "Done\n";
}
?>