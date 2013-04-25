<?php

namespace CB;

class Calendar{

	public function getEvents($p){
		$rez = array('success' => true, 'data' => array() );
		
		if(empty($p->start) || empty($p->end)) return $rez;

		if(!empty($p->path)){
			$rez['pathtext'] = Path::getPathText($p);
			$rez['folderProperties'] = Path::getPathProperties($p);
		}

		$pid = explode('/', @$p->path);
		$pid = array_pop($pid);
		$pid = is_numeric($pid) ? $pid : Browser::getRootFolderId();
		if(empty($p->descendants)) $p->pid = $pid; else $p->pids = $pid;
		$p->dateStart = $p->start;//.'Z';
		unset($p->start);
		$p->dateEnd = $p->end;//substr($p->end, 0, 10).'T23:59:59.999Z';
		unset($p->end);

		$p->types = array(6, 7);
		
		$s = new Search();
		$sr = $s->query($p);
		if(!empty($sr['data']))
		for ($i=0; $i < sizeof($sr['data']); $i++) { 
			$d = $sr['data'][$i];
			$catIcon = '';
			if(!empty($d['category_id'])){
				$catIcon = Util\getThesauryIcon($d['category_id']);
				if(!empty($catIcon)) $catIcon = ' cal-cat-'.$catIcon;
			}
			@$rez['data'][] = array(
				'id' => $d['id']
				,'ad' => ($d['allday'] == 1)
				,'type' => $d['type']
				,'cid' => $d['cid']
				,'title' => $d['name']
				,'start' => $d['date']
				,'category_id' => $d['category_id']
				,'end' => Util\coalesce($d['date_end'], $d['date'])
				//,'iconCls' => $d['iconCls']
				,'cls' => 'cal-evt-bg-t'.$d['type'].$catIcon.(empty($d['iconCls']) ? '' : ' icon-padding '.$d['iconCls'])
			);
		}
		if(!empty($sr['facets'])) $rez['facets'] = $sr['facets'];

		return $rez;
	}
}
?>