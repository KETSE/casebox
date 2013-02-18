<?php
require_once 'Security.php';
if(!Security::canManage()) throw new Exception(L\Access_denied);
require_once 'Log.php';
class System {

	public function tagsGetChildren($params){
		$rez = array();
		$t = explode('/', $params->path);
		$nodeId = intval(array_pop($t));
		$res = mysqli_query_params('select id, '.$_SESSION['languages']['string'].', `type`, `order`, `hidden`, (select count(*) from tags where pid = t.id) `hasChildren`, iconCls from tags t where pid'.( ($nodeId > 0) ? '=$1' : ' is NULL' ).' and user_id is null  and group_id is null order by `type`, `order`, l'.UL_ID() , $nodeId) or die(mysqli_query_error());
		while($r = $res->fetch_assoc()){
			if($r['type'] == 1){
				//$r['leaf'] = true;
				//if(empty($r['iconCls'])) $r['iconCls'] = 'icon-tag-small';
			}else{
				//$r['leaf'] = false;
				unset($r['iconCls']);
				if(empty($nodeId)) $r['expanded'] = true;
			}
			$r['loaded'] = empty($r['hasChildren']);
			unset($r['hasChildren']);
			array_push($rez, $r);
		}
		return $rez;
	}
	
	public function getTagPath($id){
		$id = explode('-', $id);
		$id = array_pop($id);
		$rez = array('success' => true, 'path' => null);
		$res = mysqli_query_params('SELECT f_get_tag_pids($1)' , $id) or die(mysqli_query_error());
		if($r = $res->fetch_row()) $rez['path'] = $r[0];
		return $rez;
	}
	
	public function tagsSaveElement($params){
		
		$p = array(
			'id' => empty($params->id) ? null: $params->id
			,'pid' => (empty($params->pid) || (!is_numeric($params->pid))) ? null: $params->pid
			,'type' => empty($params->type) ? 0: 1
			,'hidden' => empty($params->hidden) ? null: 1
			,'iconCls' => (empty($params->iconCls) || ($params->iconCls == 'icon-tag-small')) ? null: $params->iconCls
			//,'order' => $p->order
		);
		$values_string = '$1, $2, $3, $4, $5';
		$on_duplicate =  'hidden = $4, iconCls = $5';

		if(empty($params->id)){
			$p['order'] = 0;
			$res = mysqli_query_params('SELECT max(`order`) from tags where pid'.( (empty($params->pid) || (!is_numeric($params->pid))) ? ' is null ' : ' = '.$params->pid) ) or die(mysqli_query_error());
			if($r = $res->fetch_row()) $p['order'] = intval($r[0])+1;
			$res->close();
			$values_string .= ', $6';
			$on_duplicate .= ',`order` = $6';
		}

		getLanguagesParams($params, $p, $values_string, $on_duplicate);
		mysqli_query_params('insert into tags (`'.implode('`,`', array_keys($p)).'`) values ('.$values_string.') on duplicate key update '.$on_duplicate, array_values($p)) or die(mysqli_query_error());
		if(!is_numeric(@$params->id)) $p['id'] = last_insert_id();
		$p['loaded'] = true;
		$updatedTagGroups = false;
		if($params->type == 1) $updatedTagGroups = $this->updateTagGroupsResultTable($p['id']);
		return array( 'success' => true, 'data' => $p, 'updatedTagGroups' => $updatedTagGroups);
	}

	public function tagsMoveElement($params){
		/* get old pid */
		$res = mysqli_query_params('select pid, `order` from tags where id = $1', $params->id) or die(mysqli_query_error());
		$old_pid = 0;
		$old_order = 0;
		if($r = $res->fetch_row()){
			$old_pid = $r[0];
			$old_order = $r[1];
		}
		$res->close();
		/* end of get old pid */
		$params->toId = is_numeric($params->toId) ? $params->toId : null;
		$order = 1;
		switch($params->point){
			case 'above':
				/* get relative node order and pid */
				$res = mysqli_query_params('select pid, `order` from tags where id = $1', $params->toId) or die(mysqli_query_error());
				if($r = $res->fetch_row()){
					$params->toId = $r[0];
					$order = $r[1];
				}
				$res->close();
				mysqli_query_params('update tags set `order` = `order` + 1 where pid = $1 and `order` >= $2', array($params->toId, $order)) or die(mysqli_query_error());
				break;
			case 'below':
				/* get relative node order and pid */
				$res = mysqli_query_params('select pid, `order` from tags where id = $1', $params->toId) or die(mysqli_query_error());
				if($r = $res->fetch_row()){
					$params->toId = $r[0];
					$order = $r[1]+1;
				}
				$res->close();
				mysqli_query_params('update tags set `order` = `order` + 1 where pid = $1 and `order` >= $2', array($params->toId, $order)) or die(mysqli_query_error());
				break;
			default:
				$res = mysqli_query_params('select max(`order`) from tags where pid = $1', $params->toId) or die(mysqli_query_error());
				if($r = $res->fetch_row()){
					$order = $r[0]+1;
				}
				$res->close();
		}
		mysqli_query_params('update tags set pid = $2, `order` = $3 where id = $1', array($params->id, $params->toId, $order)) or die(mysqli_query_error());
		mysqli_query_params('update tags set `order` = `order` - 1 where pid = $1 and `order` > $2', array($old_pid, $old_order)) or die(mysqli_query_error());
		return array('success' => true, 'updatedTagGroups' => $this->updateTagGroupsResultTable(array(intval($old_pid), intval($params->toId))));
	}

	public function tagsSortChilds($params){
		$res = mysqli_query_params('select id from tags where pid = $1 order by l'.UL_ID().(($params->direction == 'desc') ? ' desc' : '') , $params->id) or die(mysqli_query_error());
		$i = 1;
		while($r = $res->fetch_row()){
			mysqli_query_params('update tags set `order` = $1 where id = $2' , array($i, $r[0])) or die(mysqli_query_error());
			$i++;
		}
		$res->close();	
		return array('success' => true);
	}
	
	public function tagsDeleteElement($id){ //tag or folder
		$res = mysqli_query_params('select pid, `order` from tags where id = $1', $id) or die(mysqli_query_error());
		$pid = 0;
		if($r = $res->fetch_row()){
			$pid = $r[0];
			mysqli_query_params('update tags set `order` = `order` - 1 where pid = $1 and `order` > $2', array($pid, $r[1])) or die(mysqli_query_error());
		}
		$res->close();
		mysqli_query_params('delete from tags where id = $1', $id) or die(mysqli_query_error());
		return array('success' => true, 'updatedTagGroups' => $this->updateTagGroupsResultTable($pid));
	}

	public function tagGroupsGetChildren($params){
		$rez = array();
		
		/* 	g - tag group
			f - simple folder
			t - tag element
			node_id will be of the following format: <type tag>-id[-{pid/template_id}] 
		*/
		
		if($params->path == '/'){
			$sql = 'SELECT id `nid`, '.$_SESSION['languages']['string'].', `system`,`order` FROM tag_groups g where pid is null and g.`order` >-1 order by `order`, system desc, l'.UL_ID();
			$res = mysqli_query_params($sql) or die(mysqli_query_error());
			while($r = $res->fetch_assoc()){
				if($r['nid'] == 7){
					$r['type'] = 'f';
					$r['iconCls'] = 'icon-layers';
				}else $r['iconCls'] = 'icon-folder-bookmark';
				$r['nid'] = 'g-'.$r['nid'];
				$r['expanded'] = true;
				$rez[] = $r;
			}
			$res->close();
			return $rez;
		}
		
		$t = explode('/', $params->path);
		$t = array_pop($t);

		$t = explode('-', $t);
		$tag = array_shift($t);
		$group_id = array_shift($t);
		$id = 0; // template_id or pid;
		if(!empty($t)) $id = array_shift($t);

		switch($group_id){
			case 7:
				if(empty($id)){
					$sql = 'SELECT 7 `nid`, t.id `template_id`, '.$_SESSION['languages']['string'].', 1 `system`,t.`order` FROM templates t where t.type = 7 order by t.`order`, t.l'.UL_ID(); 
					$res2 = mysqli_query_params($sql) or die(mysqli_query_error());
					while($r2 = $res2->fetch_assoc()){
						$r2['nid'] = 'g-7-'.$r2['template_id'];
						$r2['iconCls'] = 'icon-folder-bookmark';
						$r2['expanded'] = true;
						$rez[] = $r2;
					}
					$res2->close();
				}else{
					//echo '!'.$tag.'-'.$group_id.'-'.$id;
					$sql = 'SELECT t.id `nid`, t.l'.UL_ID().', t.`type`, t.iconCls, g.recursive FROM tag_groups__tags g join tags t on g.tag_id = t.id where g.tags_group_id = 7 and template_id = $1 order by t.type, 2';
					$res3 = mysqli_query_params($sql , $id) or die(mysqli_query_error());
					while($r3 = $res3->fetch_assoc()){
						$r3['nid'] = 't-7-'.$id.'-'.$r3['nid'];
						$r3['iconCls'] = 'icon-tag-small';
						$r3['leaf'] = true;
						$rez[] = $r3;
					}
					$res3->close();
				}
				break;
			case 2:
				if($id === 'f'){
					$sql = 'SELECT t.id `nid`, t.l'.UL_ID().', 1 `system`, t.`order` '.
					' FROM tag_groups g'.
					' JOIN tag_groups__tags_result gt ON g.id = gt.tags_group_id'.
					' JOIN tags t ON gt.tag_id = t.id AND t.hidden IS NULL'.
					' where g.id = 2 ORDER BY t.order, 2';
					$res = mysqli_query_params($sql, $group_id) or die(mysqli_query_error());
					while($r = $res->fetch_assoc()){
						$r['nid'] = 'g-'.$group_id.'-'.$r['nid'];
						$r['iconCls'] = 'icon-briefcase';
						$r['type'] = 'c';
						$r['expanded'] = true;
						$rez[] = $r;
					}
					$res->close();
					break;
				}elseif($id > 0){
					$sql = 'SELECT id `nid`, '.$_SESSION['languages']['string'].', `system`,`order` FROM tag_groups g where pid = $1 and g.`order` >-1 order by `order`, system desc, l'.UL_ID();
					$res = mysqli_query_params($sql, $group_id) or die(mysqli_query_error());
					while($r = $res->fetch_assoc()){
						$r['nid'] = 'g-'.$r['nid'].'-'.$id;
						$r['expanded'] = true;
						$r['iconCls'] = 'icon-folder-bookmark';
						$rez[] = $r;
					}
					$res->close();
					break;
				}
			default: 
				if($group_id == 2) $rez[] = array(
					'nid' => 'f-2-f'
					,'type' => 'f'
					,'iconCls' => 'icon-layers'
					,'l'.UL_ID() => L('Structure')

				);
				$sql = 'SELECT t.id `nid`, t.l'.UL_ID().', t.`type`, t.iconCls, g.recursive FROM tag_groups__tags g join tags t on g.tag_id = t.id where g.tags_group_id = $1 '.( empty($id) ? '' : ' and pid_value = $2').' order by t.type, 2';
				$res = mysqli_query_params($sql , array($group_id, $id)) or die(mysqli_query_error());
				while($r = $res->fetch_assoc()){
					$r['nid'] = 't-'.$group_id.'-'.(empty($id) ? '' : $id.'-').$r['nid'];
					$r['iconCls'] = 'icon-tag-small';
					$r['leaf'] = true;
					$rez[] = $r;
				}
				$res->close();
				break;
		}
		return $rez;
	}
	
	public function tagsGroupAddElement($params){
		$t = explode('-', $params->tags_group_id);
		$params->pid_value = 0;
		$params->template_id = 0;
		$params->tags_group_id  = array_shift($t);
		$params->tags_group_id  = array_shift($t);
		if(!empty($t)){
			if($params->tags_group_id == 7) $params->template_id = array_shift($t);
			else $params->pid_value = array_shift($t);
		}
		mysqli_query_params('insert into tag_groups__tags (tags_group_id, pid_value, template_id, tag_id) values($1, $2, $3, $4) on duplicate key update pid_value = $2, template_id = $3' ,
			array( $params->tags_group_id, $params->pid_value, $params->template_id, $params->tag_id )) or die(mysqli_query_error());
		
		$rez = array('success' => false);
		$sql = 'SELECT t.id `nid`, t.l'.UL_ID().' , t.`type`, g.recursive FROM tag_groups__tags g join tags t on g.tag_id = t.id where g.tags_group_id = $1 and tag_id = $2'.
			(empty($params->pid_value) ? '' : ' and pid_value = $3').(empty($params->template_id) ? '' : ' and template_id = $4');
		$res = mysqli_query_params($sql , array($params->tags_group_id, $params->tag_id, $params->pid_value, $params->template_id)) or die(mysqli_query_error());
		if($r = $res->fetch_assoc()){
			$r['nid'] = 't-'.$params->tags_group_id.'-'.(empty($params->pid_value) ? '' : $params->pid_value.'-').(empty($params->template_id) ? '' : $params->template_id.'-').$r['nid'];
			$r['iconCls'] = 'icon-tag-small';
			$r['leaf'] = true;
			$rez = array('success' => true, 'data' => $r);
		}
		$res->close();
		$rez['updatedTagGroups'] = $this->updateTagGroupsResultTable($params->tags_group_id);
		return $rez;		
	}
	
	public function tagsGroupRemoveElement($params){
		$t = explode('-', $params->id);
		$params->pid_value = null;
		$params->template_id = null;
		$params->tags_group_id  = array_shift($t);
		$params->tags_group_id  = array_shift($t);
		$params->id = array_pop($t);
		if(!empty($t)){
			if($params->tags_group_id == 7) $params->template_id = array_shift($t);
			else $params->pid_value = array_shift($t);
		}		
		mysqli_query_params('delete from tag_groups__tags where tags_group_id = $1 and tag_id = $2'.(empty($params->pid_value) ? '' : ' and pid_value = $3').(empty($params->template_id) ? '' : ' and template_id = $4'),
			array( $params->tags_group_id, $params->id, $params->pid_value, $params->template_id )) or die(mysqli_query_error());
		return array('success' => true, 'updatedTagGroups' => $this->updateTagGroupsResultTable($params->tags_group_id));
	}

	public function getTagGroupsTree($params){
		$data = array();
		$t = explode('/', $params->path);
		$depth = sizeof($t) - 1;
		$nodeId = array_pop($t);
		$t = explode('-', $nodeId);
		$case_type_id = $t[1];
		$nodeId = intval(array_pop($t));
		switch($depth){
		case 1: 
			$res = mysqli_query_params('SELECT id FROM tag_groups where system = 2' ) or die(mysqli_query_error());//case types
			if($r = $res->fetch_assoc()){
				$t = $this->getGroupTags($r['id']);
				foreach($t as $tag) $data[] = array('id' => 'ct-'.$tag['id'], 'text' =>$tag['name'], 'expanded' => true, 'system' => 2, 'order' => $tag['order'], 'iconCls' => 'icon-briefcase');
			}
			$res->close();
			break;
		case 2: 
			$sql = 'SELECT id, l'.UL_ID().' `text`, `type`, `type` `order` FROM templates where `type` between -3 and -1 order BY `type` desc, 2';
			$res = mysqli_query_params($sql ) or die(mysqli_query_error());
			while($r = $res->fetch_assoc()){
				$r['id'] = 'tg-'.$case_type_id.'-'.$r['id'];
				$r['iconCls'] = 'icon-folder';
				$r['cls'] = 'fwB';
				$data[] = $r;
			}
			$res->close();
			break;
		case 3:
			//retreive templates
			$sql = 'SELECT t.id, t.l'.UL_ID().' `text`, t.iconCls, tpt.`order`, t.type FROM templates_per_tags tpt JOIN templates t ON  tpt.template_id = t.id AND t.pid = $2 WHERE tpt.case_type_id = $1 order by tpt.`order`, 2';
			$res = mysqli_query_params($sql, array($case_type_id, $nodeId)) or die(mysqli_query_error());
			while($r = $res->fetch_assoc()){
				$data[] = array('id' => 't-'.$case_type_id.'-'.$r['id'], 'text' =>$r['text'], 'leaf' => true, 'iconCls' => $r['iconCls'], 'type' => $r['type'], 'order' => $r['order']);				
			}
			$res->close();
			break;
		}
		return $data;
	}
	
	public function templateMoveToTag($params){
		// source_id (always should be a template: tt-{case_type_id}-{tag_id}-{template_id})
		// target_id could be a case_type node, tag node or another template node (if point is not 'append')
		// point - append, below, above
		$t = explode('-', $params->source_id);
		$source_case_type_id = $t[1];
		$template_id = $t[2];
		
		$t = explode('-', $params->target_id);
		$target_case_type_id = $t[1];

		$order = 1;

		switch($params->point){
		case 'above':
			$res = mysqli_query_params('SELECT `order` FROM templates_per_tags WHERE template_id = $1 and case_type_id = $2 ', array($t[2], $target_case_type_id) ) or die(mysqli_query_error());
			if($r = $res->fetch_row()) $order = $r[0];
			$res->close();
			mysqli_query_params('update templates_per_tags  set `order` = `order` +  1 where case_type_id = $1 and `order` >= $2 ', array($target_case_type_id, $order) ) or die(mysqli_query_error());
			break;
		case 'below':
			$res = mysqli_query_params('SELECT `order` + 1 FROM templates_per_tags WHERE template_id = $1 and case_type_id = $2 ', array($t[2], $target_case_type_id) ) or die(mysqli_query_error());
			if($r = $res->fetch_row()) $order = $r[0];
			$res->close();
			mysqli_query_params('update templates_per_tags  set `order` = `order` + 1 where case_type_id = $1 and `order` >= $2 ', array($target_case_type_id, $order) ) or die(mysqli_query_error());
			break;
		default: //append
			$res = mysqli_query_params('SELECT max(`order`) + 1 FROM templates_per_tags WHERE case_type_id = $1 ', array($target_case_type_id) ) or die(mysqli_query_error());
			if($r = $res->fetch_row()) $order = $r[0];
			$res->close();
		}
		mysqli_query_params('update templates_per_tags set case_type_id = $1, `order` = $2 where case_type_id = $3 and template_id = $4', array($target_case_type_id, $order, $source_case_type_id, $template_id) ) or die(mysqli_query_error());
		$rez = array('success' => false);
		$res = mysqli_query_params('SELECT t.id, t.l'.UL_ID().' `text`, t.type, t.iconCls, tt.`order` FROM templates_per_tags tt JOIN templates t ON tt.template_id = t.id WHERE tt.template_id = $1 and tt.case_type_id = $2', array($template_id, $target_case_type_id) ) or die(mysqli_query_error());
		if($r = $res->fetch_assoc()){
			$r['id'] = 't-'.$target_case_type_id.'-'.$r['id'];
			$rez = array('success' => true, 'data' => $r);
		}
		$res->close();
		return $rez;
	}
	
	public function templateAssociateToTag($params){
		$template_id = intval($params->template_id);
		$t = explode('-', $params->target_id);
		$case_type_id = $t[1];
		//$tag_id = isset($t[2]) ? $t[2]: $t[1]; //intval(array_pop($t));
		$order = 1;
		switch($params->point){
		case 'above':
			$res = mysqli_query_params('SELECT `order` FROM templates_per_tags WHERE template_id = $1 and case_type_id = $2', array($t[2], $case_type_id) ) or die(mysqli_query_error());
			if($r = $res->fetch_row()) $order = $r[0];
			$res->close();
			mysqli_query_params('update templates_per_tags  set `order` = `order` +  1 where case_type_id = $1 and `order` >= $2 ', array($case_type_id, $order) ) or die(mysqli_query_error());
			break;
		case 'below':
			$res = mysqli_query_params('SELECT `order` + 1 FROM templates_per_tags WHERE template_id = $1 and case_type_id = $2 ', array($t[2], $case_type_id) ) or die(mysqli_query_error());
			if($r = $res->fetch_row()) $order = $r[0];
			$res->close();
			mysqli_query_params('update templates_per_tags  set `order` = `order` + 1 where case_type_id = $1 and `order` >= $2 ', array($case_type_id, $order) ) or die(mysqli_query_error());
			break;
		default: //append
			$res = mysqli_query_params('SELECT max(`order`) + 1 FROM templates_per_tags WHERE case_type_id = $1', array($case_type_id) ) or die(mysqli_query_error());
			if($r = $res->fetch_row()) $order = $r[0];
			$res->close();
		}
		
		$rez = array('success' => false);
		mysqli_query_params('insert into templates_per_tags (template_id, case_type_id, `order`) values($1, $2, $3) on duplicate key update `order`  = $3', array($template_id, $case_type_id, intval($order)) ) or die(mysqli_query_error());
		$res = mysqli_query_params('SELECT t.id, t.l'.UL_ID().' `text`, t.type, t.iconCls, tt.`order` FROM templates_per_tags tt JOIN templates t ON tt.template_id = t.id WHERE tt.template_id = $1 and tt.case_type_id = $2', array($template_id, $case_type_id) ) or die(mysqli_query_error());
		if($r = $res->fetch_assoc()){
			$r['id'] = 't-'.$case_type_id.'-'.$r['id'];
			$rez = array('success' => true, 'data' => $r);
		}
		$res->close();
		return $rez;
	}
	public function templateDeassociateFromTag($id){
		$t = explode('-', $id);
		$template_id = intval(array_pop($t));
		//$tag_id = intval(array_pop($t));
		$case_type_id = intval(array_pop($t));
		mysqli_query_params('delete from templates_per_tags where template_id = $1 and case_type_id = $2', array($template_id, $case_type_id) ) or die(mysqli_query_error());
		return array('success' => true);
	}
	private function getGroupTags($group_id, $for_case_type_id = false){
		$rez = array();
		$sql = 'select t.id, t.l'.UL_ID().' `name`, t.iconCls, t.order'.($for_case_type_id ? ', (SELECT COUNT(*) FROM templates_per_tags WHERE case_type_id = $2 AND tag_id = t.id) templates' : '').
			' from tag_groups__tags_result gtr join tags t on gtr.tag_id = t.id where gtr.tags_group_id = $1 order by t.order, 2';
		//$res = mysqli_query_params('CALL p_get_tags_group_tags($1, $2)', array($group_id, UL_ID()) ) or die(mysqli_query_error());
		$res = mysqli_query_params($sql, array($group_id, $for_case_type_id) ) or die(mysqli_query_error());
		while($r = $res->fetch_assoc()) $rez[] = $r;
		$res->close();
		mysqli_clean_connection();
		return $rez;
	}
	
	private function getTagTemplates($case_type_id, $tag_id){
		$rez = array();
		//die($case_type_id.', '.$tag_id);
		$res = mysqli_query_params('SELECT t.id, t.l'.UL_ID().' `text`, t.type, t.iconCls, tt.order FROM templates_per_tags tt JOIN templates t ON tt.template_id = t.id WHERE case_type_id = $1 and tt.tag_id = $2', array($case_type_id, $tag_id) ) or die(mysqli_query_error());
		while($r = $res->fetch_assoc()){
			$r['leaf'] = true;
			//$r['iconCls'] = empty($r['iconCls']) ? 'icon-document-medium' : $r['iconCls'];
			$rez[] = $r;
		}
		$res->close();
		return $rez;
	}

	public static function updateTagGroupsResultTable($tags){
		if(!is_array($tags)) $tags = array($tags);
		$res = mysqli_query_params('SELECT f_get_tag_pids(id) FROM tags where id in (0'.implode(',', $tags).')') or die(mysqli_query_error());
		$ids = array();
		while($r = $res->fetch_row()) $ids = array_merge($ids, explode('/', $r[0]));
		$res->close();
		if(!empty($ids)){
			$ids = array_unique($ids, SORT_NUMERIC);
			$res = mysqli_query_params('SELECT distinct tags_group_id from tag_groups__tags where tag_id in ('.implode(',', $ids).')') or die(mysqli_query_error());
			while($r = $res->fetch_row()){
				$rez = true;
				mysqli_query_params('call p_update_tag_group_tags($1)', $r[0]) or die(mysqli_query_error());
			}
			$res->close();
			mysqli_clean_connection();
		}
		$sql = 'SELECT DISTINCT t.id, gt.pid_value, gt.template_id, t.l'.UL_ID().', t.iconCls, g.id, g.system'.
			' FROM tag_groups g'.
			' JOIN tag_groups__tags_result gt ON g.id = gt.tags_group_id'.
			' JOIN tags t ON gt.tag_id = t.id AND t.hidden IS NULL'.
			' ORDER BY `system`, g.`order`, g.l'.UL_ID().', t.order, 2';
		$res = mysqli_query_params($sql) or die(mysqli_query_error());
		$rez = Array();
		while($r = $res->fetch_row()) $rez[] = $r;
		$res->close();
		return $rez;
	}
}