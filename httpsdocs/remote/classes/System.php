<?php

namespace CB;

if(!Security::canManage()) throw new \Exception(L\Access_denied);

class System {

	public function tagsGetChildren($params){
		$rez = array();
		$t = explode('/', $params->path);
		$nodeId = intval(array_pop($t));
		$res = DB\mysqli_query_params('select id, '.config\language_fields.', `type`, `order`, `hidden`, (select count(*) from tags where pid = t.id) `hasChildren`, iconCls from tags t where pid'.( ($nodeId > 0) ? '=$1' : ' is NULL' ).' and user_id is null  and group_id is null order by `type`, `order`, l'.USER_LANGUAGE_INDEX , $nodeId) or die(DB\mysqli_query_error());
		while($r = $res->fetch_assoc()){
			if($r['type'] == 1){
			}else{
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
		$res = DB\mysqli_query_params('SELECT f_get_tag_pids($1)' , $id) or die(DB\mysqli_query_error());
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
		);
		$values_string = '$1, $2, $3, $4, $5';
		$on_duplicate =  'hidden = $4, iconCls = $5';

		if(empty($params->id)){
			$p['order'] = 0;
			$res = DB\mysqli_query_params('SELECT max(`order`) from tags where pid'.( (empty($params->pid) || (!is_numeric($params->pid))) ? ' is null ' : ' = '.$params->pid) ) or die(DB\mysqli_query_error());
			if($r = $res->fetch_row()) $p['order'] = intval($r[0])+1;
			$res->close();
			$values_string .= ', $6';
			$on_duplicate .= ',`order` = $6';
		}

		Util\getLanguagesParams($params, $p, $values_string, $on_duplicate);
		DB\mysqli_query_params('insert into tags (`'.implode('`,`', array_keys($p)).'`) values ('.$values_string.') on duplicate key update '.$on_duplicate, array_values($p)) or die(DB\mysqli_query_error());
		if(!is_numeric(@$params->id)) $p['id'] = DB\last_insert_id();
		$p['loaded'] = true;
		return array( 'success' => true, 'data' => $p);
	}

	public function tagsMoveElement($params){
		/* get old pid */
		$res = DB\mysqli_query_params('select pid, `order` from tags where id = $1', $params->id) or die(DB\mysqli_query_error());
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
				$res = DB\mysqli_query_params('select pid, `order` from tags where id = $1', $params->toId) or die(DB\mysqli_query_error());
				if($r = $res->fetch_row()){
					$params->toId = $r[0];
					$order = $r[1];
				}
				$res->close();
				DB\mysqli_query_params('update tags set `order` = `order` + 1 where pid = $1 and `order` >= $2', array($params->toId, $order)) or die(DB\mysqli_query_error());
				break;
			case 'below':
				/* get relative node order and pid */
				$res = DB\mysqli_query_params('select pid, `order` from tags where id = $1', $params->toId) or die(DB\mysqli_query_error());
				if($r = $res->fetch_row()){
					$params->toId = $r[0];
					$order = $r[1]+1;
				}
				$res->close();
				DB\mysqli_query_params('update tags set `order` = `order` + 1 where pid = $1 and `order` >= $2', array($params->toId, $order)) or die(DB\mysqli_query_error());
				break;
			default:
				$res = DB\mysqli_query_params('select max(`order`) from tags where pid = $1', $params->toId) or die(DB\mysqli_query_error());
				if($r = $res->fetch_row()){
					$order = $r[0]+1;
				}
				$res->close();
		}
		DB\mysqli_query_params('update tags set pid = $2, `order` = $3 where id = $1', array($params->id, $params->toId, $order)) or die(DB\mysqli_query_error());
		DB\mysqli_query_params('update tags set `order` = `order` - 1 where pid = $1 and `order` > $2', array($old_pid, $old_order)) or die(DB\mysqli_query_error());
		return array('success' => true);
	}

	public function tagsSortChilds($params){
		$res = DB\mysqli_query_params('select id from tags where pid = $1 order by l'.USER_LANGUAGE_INDEX.(($params->direction == 'desc') ? ' desc' : '') , $params->id) or die(DB\mysqli_query_error());
		$i = 1;
		while($r = $res->fetch_row()){
			DB\mysqli_query_params('update tags set `order` = $1 where id = $2' , array($i, $r[0])) or die(DB\mysqli_query_error());
			$i++;
		}
		$res->close();	
		return array('success' => true);
	}
	
	public function tagsDeleteElement($id){ //tag or folder
		$res = DB\mysqli_query_params('select pid, `order` from tags where id = $1', $id) or die(DB\mysqli_query_error());
		$pid = 0;
		if($r = $res->fetch_row()){
			$pid = $r[0];
			try {
				DB\mysqli_query_params('delete from tags where id = $1', $id) or die(DB\mysqli_query_error());
				
			} catch (\Exception $e) {
				return array('success' => false, 'msg' => 'Cannot delete selected tag, it is used in the system.');
			}
			DB\mysqli_query_params('update tags set `order` = `order` - 1 where pid = $1 and `order` > $2', array($pid, $r[1])) or die(DB\mysqli_query_error());
		}
		$res->close();
		return array('success' => true);
	}

	public function getCountries(){
		$rez = array();
		$res = DB\mysqli_query_params('select id, name, phone_codes from casebox.country_phone_codes order by name') or die(DB\mysqli_query_error());
		while($r = $res->fetch_row())
			$rez[] = $r;
		return array('success' => true, 'data' => $rez);
	}
	public function getTimezones(){
		$rez = array();
		$res = DB\mysqli_query_params('SELECT caption, gmt_offset FROM casebox.zone ORDER BY gmt_offset, caption') or die(DB\mysqli_query_error());
		while($r = $res->fetch_row()){
			$offsetHours = floor(abs($r[1])/3600); 
			$offsetMinutes = round((abs($r[1]) - $offsetHours * 3600) / 60);
			if($offsetMinutes == 60){
				$offsetHours++;
				$offsetMinutes = 0;
			}
			$r[1] = ( ($r[1] < 0) ? '-': '+' )
				. ($offsetHours < 10 ? '0' : '') . $offsetHours 
				. ':' 
				. ($offsetMinutes < 10 ? '0' : '') . $offsetMinutes; 			
			$rez[] = $r;
		}
		return array('success' => true, 'data' => $rez);
	}
}