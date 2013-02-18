<?php
require_once 'BrowserTree.php';
class BrowserView extends BrowserTree{
	public function getChildren($p){
		$p->showFoldersContent = true;
		$rez = array( 
			'success' => true
			,'pathtext' => Path::getPathText($p)
			,'folderProperties' => Path::getPathProperties($p)
		);
			
		$pid = null;
		if(!empty($p->path)) $pid = Path::getId($p->path); elseif(!empty($p->pid)) $pid = is_numeric($p->pid) ? $p->pid : Browser::getRootFolderId();
		if(empty($p->showDescendants)) $p->pid = $pid; else $p->pids = $pid;
		$s = new Search();
		$rez = array_merge($rez, $s->query($p));
		if(!empty($rez['data']))
		for ($i=0; $i < sizeof($rez['data']); $i++) { 
			$d = &$rez['data'][$i];
			$d['nid'] = $d['id'];
			unset($d['id']);
			if(!empty($d['name'])) $d['name'] = htmlentities($d['name']);
			//if(empty($d['description'])) $d['description'] = htmlentities($d['description']);

			$res = mysqli_query_params('select 1 from tree where pid = $1 limit 1', $d['nid']) or die(mysqli_query_error());
			if($r = $res->fetch_row()) $d['has_childs'] = true;
			$res->close();
		}
		parent::updateLabelsAndIcons($rez['data']);
		return $rez;
	}
	public function getSummaryData($p){
		/* result columns order : id, name, type, iconCls, total, total2*/
		$rez = array(
			'success' => true
			,'data' => array()
		);
		$path = '/';
		$default_filters = array(
			'activeTasks' => (object)array(
				'sort' => 'status'
				,'types' => array(6)
				,'filters' => (object)array(
					'status' => array( (object)array('mode' => 'OR', 'values' => array(1, 2) ) )
					,'user_ids' => array( (object)array('mode' => 'OR', 'values' => array($_SESSION['user']['id']) ) )
				)
			)
			,'completeTasks' => (object)array(
				'sort' => 'status'
				,'types' => array(6)
				,'filters' => (object)array(
					'status' => array( (object)array('mode' => 'OR', 'values' => array(1, 2) ) )
					,'user_ids' => array( (object)array('mode' => 'OR', 'values' => array($_SESSION['user']['id']) ) )
				)
			)
			,'actions' => (object)array(
				'sort' => 'status'
				,'types' => array(6)
				,'filters' => (object)array(
					'status' => array( (object)array('mode' => 'OR', 'values' => array(1, 2) ) )
					,'user_ids' => array( (object)array('mode' => 'OR', 'values' => array($_SESSION['user']['id']) ) )
				)
			)
			,'files' => (object)array(
				'sort' => 'status'
				,'types' => array(6)
				,'filters' => (object)array(
					'status' => array( (object)array('mode' => 'OR', 'values' => array(1, 2) ) )
					,'user_ids' => array( (object)array('mode' => 'OR', 'values' => array($_SESSION['user']['id']) ) )
				)
			)
			,'tasksUsers' => (object)array(
				'sort' => 'status'
				,'types' => array(6)
				,'filters' => (object)array(
					'status' => array( (object)array('mode' => 'OR', 'values' => array(1, 2) ) )
					,'user_ids' => array( (object)array('mode' => 'OR', 'values' => array($_SESSION['user']['id']) ) )
				)
			)
		);
		$search = new Search();
		foreach($p as $k => $v){
			if(empty($default_filters[$k])) continue;
			$params = coalesce(@$p->{$k}, $default_filters[$k]);
			if(!empty($v->path)){
				$path = $v->path;
				// var_dump($path);
				// echo "=".Path::getId($path);
				if(empty($v->showDescendants) ) $params->pid = Path::getId($path); else $params->pids = Path::getId($path);
			}
			//var_dump($params);
			$sr = $search->query( $params );
			$d = array();
			switch($k){
				case 'tasksUsers':
					foreach($sr['facets'] as $f)
						@$d[] = array($f['id'], null, null, null, null, null, $f['total'], $f['total2'] );
					break;
				default:
					if( !empty($sr['data']) ){
						foreach ($sr['data'] as $r) 
							@$d[] = array($r['id'], $r['name'], $r['type'], $r['status'], $r['template_id'] );
					}
			}
			$rez['data'][$k] = $d;
			$rez['params'][$k] = $search->params;
		}
		
		$path = (object)array('path' => $path);
		$rez['pathtext'] = Path::getPathText($path);
		$rez['folderProperties'] = Path::getPathProperties($path);
		return $rez;
	}
}