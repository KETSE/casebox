<?php
require_once 'Security.php';
require_once 'SolrClient.php';

class Search extends SolrClient{
	function query($p){
		$this->results = false;
		$this->inputParams = $p;
		$this->prepareParams();
		$this->connect();
		$this->executeQuery();
		$this->processResult();
		return $this->results;

	}

	private function prepareParams(){
		$p = &$this->inputParams;
		//TODO: check if we are in a virtual folder
		$this->query = empty($p->query)? '' : $p->query;
		$this->start = empty($p->start)? 0 : intval($p->start);
		$this->rows = empty($p->rows)? CB_MAX_ROWS : intval($p->rows);

		$this->params = array(
			'defType' => 'dismax'
			,'q.alt' => '*:*'
			,'qf' => "name content^0.5"
			,'tie' => '0.1'
			,'fl' => "id, pid, path, name, type, subtype, system, size, date, date_end, cid, cdate, udate, case_id, case, sys_tags,user_tags, template_id, user_ids, status, category_id, importance, versions"//iconCls, 
			,'sort' => 'ntsc asc'
		);
		
		if(!empty($p->fl)) $this->params['fl'] = $p->fl;
		//status asc,date_end asc
		if(isset($p->sort)){
			$sort = array();
			if(!is_array($p->sort)) $sort = array($p->sort => empty($p->dir) ? 'asc' : strtolower($p->dir) );
			else foreach($p->sort as $s){
				$s = explode(' ', $s);
				$sort[$s[0]] = empty($s[1]) ? 'asc' : strtolower($s[1]);
			}
			foreach($sort as $f => $d){
		 		if(!in_array($f, array('name', 'path', 'size', 'date', 'date_end', 'importance', 'category_id', 'status', 'cid', 'uid', 'cdate', 'udate', 'case'))) continue;
		 		$this->params['sort'] .= ",$f $d"; 	
		 	}
		}else $this->params['sort'] .= ', subtype asc, sort_path asc';
		
		$fq = array();
		if(!empty($p->pid)) $fq[] = 'pid:'.intval($p->pid);
		if(!empty($p->ids)){
			if(!is_array($p->ids)) $p->ids = explode(',', $p->ids);
			if(!empty($p->ids)) $fq[] = 'id:('.implode(' OR ', $p->ids).')';
		} 
		if(!empty($p->pids)){
			if(!is_array($p->pids)) $p->pids = explode(',', $p->pids);
			if(!empty($p->pids)) $fq[] = 'pids:('.implode(' OR ', $p->pids).')';
		} 
		if(!empty($p->types)) $fq[] = 'type:('.implode(' OR ', $p->types).')';
		if(!empty($p->templates)) $fq[] = 'template_id:('.implode(' OR ', $p->templates).')';
		if(!empty($p->tags)) $fq[] = 'sys_tags:('.implode(' OR ', $p->tags).')';
		if(!empty($p->dateStart)) $fq[] = 'date:['.$p->dateStart.' TO '.$p->dateEnd.']';

		$this->params['fq'] = $fq;

		if(!empty($this->query)){
			$this->params['hl'] = 'true';
			$this->params['hl.fl'] = 'name,content';
			$this->params['hl.simple.pre'] = '<em class="hl">';
			$this->params['hl.simple.post'] = '</em>';
			$this->params['hl.usePhraseHighlighter'] = 'true';
			$this->params['hl.highlightMultiTerm'] = 'true';
			$this->params['hl.fragsize'] = '256';
		}
		$this->prepareFacetsParams();
		$this->setFilters();
	}
	private function setFilters(){
		$p = &$this->inputParams;
		if(!empty($p->filters)){
			$p->filters = $p->filters;
			foreach($p->filters as $k => $v){
				if($k == 'OR'){
					$conditions = array();
					foreach($v as $sk => $sv){ 
						$condition = $this->analizeFilter($sk, $sv, false);
						if(!empty($condition)) $conditions[] = $condition;
					}
					if(!empty($conditions)) $this->params['fq'][] = '('.implode(' OR ', $conditions).')';
				}else{
					$condition = $this->analizeFilter($k, $v);
					if(!empty($condition)) $this->params['fq'][] = $condition;
				}
				
			}
		}
	}
	private function analizeFilter(&$k, &$v, $withtag = true){
		$rez = null;
		if($k == 'due'){
			$k = 'date_end';
			foreach($v as $sv){
				for($i = 0; $i < sizeof($sv->values); $i++ )
					switch(substr($sv->values[$i], 1)){
						case 'next7days': $sv->values[$i] = '[NOW/DAY TO NOW/DAY+6DAY]'; break;
						case 'overdue': 
							$k = 'status';
							$sv->values[$i] = '1'; break;
						case 'today': $sv->values[$i] = '[NOW/DAY TO NOW/DAY]'; break;
						case 'next31days': $sv->values[$i] = '[NOW/DAY TO NOW/DAY+31DAY]'; break;
						case 'thisWeek': $sv->values[$i] = '['.$this->current_week_diapazon().']'; break;
						case 'thisMonth': $sv->values[$i] = '[NOW/MONTH TO NOW/MONTH+1MONTH]'; break;
					}
			}
		}elseif( ($k == 'date') || ($k == 'cdate') ){
			foreach($v as $sv){
				for($i = 0; $i < sizeof($sv->values); $i++ )
					switch(substr($sv->values[$i], 1)){
						case 'today': $sv->values[$i] = '[NOW/DAY TO NOW/DAY+1DAY]'; break;
						case 'yesterday': $sv->values[$i] = '[NOW/DAY-1DAY TO NOW/DAY]'; break;
						case 'thisWeek': $sv->values[$i] = '['.$this->current_week_diapazon().']'; break;
						case 'thisMonth': $sv->values[$i] = '[NOW/MONTH TO NOW/MONTH+1MONTH]'; break;
					}
			}
		}elseif($k == 'assigned') $k = 'user_ids';
		elseif(substr($k, 0, 4) == 'stg_'){
			$k = 'sys_tags';
		}

		if(!is_array($v)) $v = array($v);
		foreach($v as $sv){
			if(!empty($sv->values)){
				if($k == 'user_ids')
					for ($i=0; $i < sizeof($sv->values); $i++) {
						if($sv->values[$i] == -1){
							$this->params['fq'][] = $this->getFacetTag('user_ids').'!user_ids:[* TO *]';//{!tag=unassigned}
							array_splice($sv->values, $i, 1);
						}
					}
				if(!empty($sv->values)) $rez = ($withtag ? $this->getFacetTag($k): '' ).$k.':('.implode(' '.$sv->mode.' ', $sv->values).')';//'{!tag='.$k.'}'
			}
		}
		return $rez;
	}
	private function getFacetTag($k){
		if(empty($this->params['facet.field'])) return '';
		foreach($this->params['facet.field'] as $f){
			if(substr($f, -strlen($k) -1) == '}'.$k){
				if(preg_match('/ex=([^\s}]+)/', $f, $matches))
					return '{!tag='.$matches[1].'}';

			}
		}
		return '';
	}

	private function prepareFacetsParams(){
		$p = &$this->inputParams;
		if(empty($p->facets)) return false;
		switch($p->facets){
			case 'general':
				$this->params['facet.field'] = array(
					'{!ex=type key=0type}type'
					,'{!ex=cid key=1cid}cid'
					,'{!ex=sys_tags key=2sys_tags}sys_tags'
				);
				//Created: Today / Yesterday / This week / This month
				$this->params['facet.query'] = array(
					'{!key=0today}cdate:[NOW/DAY TO NOW/DAY+1DAY ]'
					,'{!key=1yesterday}cdate:[NOW/DAY-1DAY TO NOW/DAY]'
					,'{!key=2thisWeek}cdate:['.$this->current_week_diapazon().']'
					,'{!key=3thisMonth}cdate:[NOW/MONTH TO NOW/MONTH+1MONTH]'
				);/**/
				break;
			case 'actions':
				$this->params['facet.field'] = array(
					'{!ex=subtype key=0subtype}subtype'
					,'{!ex=cid key=1cid}cid'
					,'{!ex=sys_tags key=2sys_tags}sys_tags'
				);
				$this->params['facet.query'] = array(
					//Date: Today / Yesterday / This week / This month
					'{!key=date_0today}date:[NOW/DAY TO NOW/DAY+1DAY ]'
					,'{!key=date_1yesterday}date:[NOW/DAY-1DAY TO NOW/DAY]'
					,'{!key=date_2thisWeek}date:['.$this->current_week_diapazon().']'
					,'{!key=date_3thisMonth}date:[NOW/MONTH TO NOW/MONTH+1MONTH]'
					//Created: Today / Yesterday / This week / This month
					,'{!key=cdate_0today}cdate:[NOW/DAY TO NOW/DAY+1DAY ]'
					,'{!key=cdate_1yesterday}cdate:[NOW/DAY-1DAY TO NOW/DAY]'
					,'{!key=cdate_2thisWeek}cdate:['.$this->current_week_diapazon().']'
					,'{!key=cdate_3thisMonth}cdate:[NOW/MONTH TO NOW/MONTH+1MONTH]'
				);/**/
				break;
			case 'actiontasks':
				$this->params['facet.field'] = array(
					'{!ex=status key=0status}status'
					,'{!ex=user_ids key=1assigned}user_ids'
					,'{!ex=cid key=2cid}cid'
				);
				break;
			case 'calendar':
				$this->params['facet.query'] = array(
					//Due date: Next 7 days / Overdue / Today / Next 31 days / This week / This month
					'{!key=0next7days}date_end:[NOW/DAY TO NOW/DAY+6DAY ]'
					,'{!key=1overdue}status:1'
					,'{!key=2today}date_end:[NOW/DAY TO NOW/DAY]'
					,'{!key=3next31days}date_end:[NOW/DAY TO NOW/DAY+31DAY ]'
					,'{!key=4thisWeek}date_end:['.$this->current_week_diapazon().']'
					,'{!key=5thisMonth}date_end:[NOW/MONTH TO NOW/MONTH+1MONTH]'
					,'{!ex=unassigned key=unassigned}!user_ids:[* TO *]'
				);/**/
				$this->params['facet.field'] = array(
					/*Status: Overdue / Active / Completed / Pending
					there were following task statuses: Pending, Active, Closed 
						with following substatuses/flags: Active:  Completed + Missed , Closed: Completed + Missed 
					Now we'll transfer to statuses: 
						1 Overdue - all tasks that passes their deadline will be moved to this status (from pending or active)
						2 Active
						3 Completed - it's equivalent to a completed and closed task (all tasks will be with autoclose = true, so that when all responsible users mark task as completed - it'll be automatically closed)
						4 Pending /**/
					'{!ex=type key=0type}type'
					,'{!ex=status key=1status}status'
					,'{!ex=category_id key=2category_id}category_id'
					,'{!ex=importance key=3importance}importance'
					//Assigned: Me / Unassigned / Ben Batros / Amrit Singh / Indira Goris 
					,'{!ex=user_ids key=4assigned}user_ids'
					//Created: Me / Ben Batros / Rupert Skillbeck
					,'{!ex=cid key=5cid}cid'
				);
				break;	
			case 'tasks':
				$this->params['facet.query'] = array(
					//Due date: Next 7 days / Overdue / Today / Next 31 days / This week / This month
					'{!key=0next7days}date_end:[NOW/DAY TO NOW/DAY+6DAY ]'
					,'{!key=1overdue}status:1'
					,'{!key=2today}date_end:[NOW/DAY TO NOW/DAY]'
					,'{!key=3next31days}date_end:[NOW/DAY TO NOW/DAY+31DAY ]'
					,'{!key=4thisWeek}date_end:['.$this->current_week_diapazon().']'
					,'{!key=5thisMonth}date_end:[NOW/MONTH TO NOW/MONTH+1MONTH]'
					,'{!ex=unassigned key=unassigned}!user_ids:[* TO *]'
				);/**/
				$this->params['facet.field'] = array(
					/*Status: Overdue / Active / Completed / Pending
					there were following task statuses: Pending, Active, Closed 
						with following substatuses/flags: Active:  Completed + Missed , Closed: Completed + Missed 
					Now we'll transfer to statuses: 
						1 Overdue - all tasks that passes their deadline will be moved to this status (from pending or active)
						2 Active
						3 Completed - it's equivalent to a completed and closed task (all tasks will be with autoclose = true, so that when all responsible users mark task as completed - it'll be automatically closed)
						4 Pending /**/
					'{!ex=status key=1status}status'
					,'{!ex=category_id key=2category_id}category_id'
					,'{!ex=importance key=3importance}importance'
					//Assigned: Me / Unassigned / Ben Batros / Amrit Singh / Indira Goris 
					,'{!ex=user_ids key=4assigned}user_ids'
					//Created: Me / Ben Batros / Rupert Skillbeck
					,'{!ex=cid key=5cid}cid'
				);

				break;
			case 'activeTasksPerUsers':
				if(!empty($p->facetPivot)){
					$this->rows = 0;
					$this->params['facet.pivot'] = $p->facetPivot;
				}
				break;
		}
		if(!empty($this->params['facet.field']) || !empty($this->params['facet.pivot']) ){
			$this->params['facet'] = 'true';
			$this->params['facet.mincount'] = 1;
		}
	}

	private function executeQuery(){
		try {
			$this->results = $this->solr->search($this->query, $this->start, $this->rows, $this->params);
		} catch( Exception $e ) {
			throw new Exception("An error occured: \n\n {$e->__toString()}");
		}
	}

	private function processResult(){
		$rez = array( 'total' => $this->results->response->numFound, 'data' => array() );
		if(is_debug_host()) $rez['search'] = array('query' => $this->query, 'start' => $this->start, 'rows' => $this->rows, 'params' => $this->params);
		$sr = &$this->results;
		foreach($sr->response->docs as $d){
			$rd = array();
			foreach($d as $fn => $fv) $rd[$fn] = is_array($fv) ? implode(',', $fv) : $fv;
			if(!empty($sr->highlighting)){
				if(!empty($sr->highlighting->{$rd['id']}->{'name'})) $rd['hl'] = $sr->highlighting->{$rd['id']}->{'name'}[0];
				if(!empty($sr->highlighting->{$rd['id']}->{'content'})) $rd['content'] = $sr->highlighting->{$rd['id']}->{'content'}[0];
			}
			$res = mysqli_query_params('select f_get_tree_path($1)', array($rd['id'])) or die(mysqli_query_error());
			if($r = $res->fetch_row()) $rd['path'] = $r[0];
			$res->close();
			$rez['data'][] = $rd;
		}
		$rez['facets'] = $this->processResultFacets();
		$this->results = $rez;
	}

	private function processResultFacets(){
    		$rez = array();
		$sr = &$this->results;
		if(empty($sr->facet_counts)) return false;
		
		$fc = &$sr->facet_counts;
		switch($this->inputParams->facets){
			case 'general':
				foreach($fc->facet_fields as $k => $v){
					$k = substr($k, 1);
					switch($k){
						case 'sys_tags': 
							if($this->analizeSystemTagsFacet($v, $rez))  break;
						default: 
							$rez[$k] = array('f' => $k, 'items' => $v);
							break;
					}
				}
				foreach($fc->facet_queries as $k => $v)
					if($v > 0) $rez['cdate']['items'][$k] = $v;
				
				break;
			case 'actiontasks':
				$sql = 'select count(*) from tree where pid = $1 and `type` = 6';//active and overdue
				$res = mysqli_query_params($sql, $this->inputParams->pid) or die(mysqli_query_error());
				if($r = $res->fetch_row()) $rez['total'] = $r[0];
				$res->close();
				$sql = 'select count(*) from tree t join tasks tt where t.pid = $1 and t.`type` = 6 and t.id = tt.id and tt.status < 3';//active and overdue
				$res = mysqli_query_params($sql, $this->inputParams->pid) or die(mysqli_query_error());
				if($r = $res->fetch_row()) $rez['active'] = $r[0];
				$res->close();
				break;
			case 'actions':
				foreach($fc->facet_fields as $k => $v){
					$k = substr($k, 1);
					switch($k){
						case 'sys_tags': 
							if($this->analizeSystemTagsFacet($v, $rez))  break;
						default: 
							$rez[$k] = array('f' => $k, 'items' => $v);
							break;
					}
				}
				foreach($fc->facet_queries as $k => $v){
					$k = explode('_', $k);
					if($v > 0) $rez[$k[0]]['items'][$k[1]] = $v;
				}
				break;
			case 'calendar':
			case 'tasks':
				foreach($fc->facet_queries as $k => $v){
					if($k == 'unassigned') continue;
					if($v > 0) $rez['due']['items'][$k] = $v;
				}
				foreach($fc->facet_fields as $k => $v){
					$k = substr($k, 1);
					$rez[$k] = array('f' => $k, 'items' => $v);
					if($k == 'assigned' && !empty($fc->facet_queries) && !empty($fc->facet_queries->{'unassigned'}) ) $rez[$k]['items']->{'-1'} = $fc->facet_queries->{'unassigned'};
				}
				break;
			case 'activeTasksPerUsers':
				if(!empty($fc->facet_pivot))
				foreach($fc->facet_pivot->{$this->inputParams->facetPivot} as $f){
					$row = array('id' => $f->value, 'total' => $f->count );
					if(!empty($f->pivot)){
						foreach($f->pivot as $st){
							if($st->value == 1) $row['total2'] = $st->count;
						}
					}
					$rez[] = $row;
				}
				break;
		}

		return $rez;
	}

	public function analizeSystemTagsFacet($values, &$rez){
		$groups = empty($_SESSION['config']['tags_facet_grouping']) ? 'pids' : $_SESSION['config']['tags_facet_grouping'][0];
		$ids = array();
		foreach($values as $k => $v) $ids[] = $k;
		if(empty($ids)) return false;
		switch($groups){
			case 'all': return false;
			case 'pids': 
				$res = mysqli_query_params('select t.id, p.pid, p.l'.UL_ID().' `title` from tags t join tags p on t.pid = p.id where t.id in ('.implode(',', $ids).')') or die(mysqli_query_error());
				while($r = $res->fetch_assoc()){
					$rez['stg_'.$r['pid']]['f'] = 'sys_tags';
					$rez['stg_'.$r['pid']]['title'] = $r['title'];
					$rez['stg_'.$r['pid']]['items'][$r['id']] = $values->{$r['id']};
				}
				$res->close();
				break;
			default: 
				$res = mysqli_query_params('select t.id, p.pid, p.l'.UL_ID().' `title` from tags t join tags p on t.pid = p.id where t.id in ('.implode(',', $ids).') and p.id in('.$groups.')') or die(mysqli_query_error());
				while($r = $res->fetch_assoc()){
					$rez['stg_'.$r['pid']]['f'] = 'sys_tags';
					$rez['stg_'.$r['pid']]['title'] = $r['title'];
					$rez['stg_'.$r['pid']]['items'][$r['id']] = $values->{$r['id']};
					unset($values->{$r['id']});
				}
				$res->close();
				if(!empty($values))
					foreach($values as $k => $v){
						$rez['sys_tags']['items'][$k] = $v;
					}
				break;
		}
		return true;
	}

	public function searchObjects($p){
		/* searching case objects */
		$rez = array('success' => true, 'data' => array() );

		if(!empty($p->object_pid) && is_numeric($p->object_pid)){
			$res = mysqli_query_params('select f_get_objects_case_id($1)', array($p->object_pid)) or die(mysqli_query_error());
			if($r = $res->fetch_row()){
				if(!empty($r[0])) $p->object_pid = $r[0];
			}
			$res->close();
			$p->pids = $p->object_pid;
		}
		$p->fl = 'id,name,type,subtype,status,date,sys_tags,template_id';
		return $this->query($p);
	}

}