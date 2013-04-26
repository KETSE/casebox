<?php

namespace CB;

class Templates{

	public function getChildren($params){
		$rez = array();
		$t = explode('/', $params->path);
		$nodeId = intval(array_pop($t));

		$res = DB\mysqli_query_params('select id nid, l'.USER_LANGUAGE_INDEX.' `text`, `type`, is_folder, `order`, `visible`, iconCls, (select count(*) '.
			'from templates where pid = t.id) `loaded` from templates t '.
			'where `type` <> \'template\' and pid'.( ($nodeId > 0) ? '=$1' : ' is NULL' ).' order by `order`, `type`, 2' , $nodeId) or die(DB\mysqli_query_error());
		while($r = $res->fetch_assoc()){
			$r['loaded'] = empty($r['loaded']);
			if(empty($nodeId)) $r['expanded'] = true;
			if(empty($r['type'])) $r['cls'] = 'fwB';
			if($r['type'] < 0) $r['cls'] = 'fwB';
			array_push($rez, $r);
		}
		return $rez;
	}
	
	public function createTemplate($params){
		if(!Security::canManage()) throw new \Exception(L\Access_denied);
		
		$p = array(
			'pid' => is_numeric(@$params->pid) ? $params->pid : null
			,'type' => ''
			,'iconCls' => 'icon-none'
		);
		$values_string = '$1, $2, $3';
		$on_duplicate = '';
		Util\getLanguagesParams($params, $p, $values_string, $on_duplicate, $params->text);
		
		DB\mysqli_query_params('insert into templates ('.implode(',', array_keys($p)).') values ('.$values_string.') on duplicate key update '.$on_duplicate, array_values($p)) or die(DB\mysqli_query_error());
		$p['id'] = DB\last_insert_id();
		
		return array( 'success' => true, 'data' => array('nid' => $p['id'], 'pid' => @$p['pid'], 'type' => $p['type'], 'iconCls' => 'icon-none', 'text' => $params->text, 'loaded' => true));
	}
	
	public function createFolder($params){
		if(!Security::canManage()) throw new \Exception(L\Access_denied);
		$sql = 'select id from templates where name = $1 and pid '.(empty($params->pid) ? ' is null' : '=$2');
		 $res = DB\mysqli_query_params($sql, array($params->text, @$params->pid) ) or die( DB\mysqli_query_error() );
		 if($r = $res->fetch_assoc()) return array( 'success' => false, 'msg' => L\FolderExists );
		 $res->close(); 
		
		$p = array(
			'pid' => is_numeric(@$params->pid) ? $params->pid : null
			,'name' => $params->text
			,'type' => ''
			,'is_folder' => 1
			,'iconCls' => 'icon-folder'
		);
		$values_string = '$1, $2, $3, $4, $5';
		$on_duplicate = '';
		Util\getLanguagesParams($params, $p, $values_string, $on_duplicate, $params->text);

		DB\mysqli_query_params('insert into templates ('.implode(',', array_keys($p)).') values ('.$values_string.') on duplicate key update '.$on_duplicate, array_values($p)) or die(DB\mysqli_query_error());
		$p['id'] = DB\last_insert_id();
		
		return array( 'success' => true, 'data' => array('nid' => $p['id'], 'pid' => @$p['pid'], 'is_folder' => 1, 'iconCls' => 'icon-folder', 'text' => $params->text, 'loaded' => true));
	}

	public function deleteElement($id){
		if(!Security::canManage()) throw new \Exception(L\Access_denied);
		DB\mysqli_query_params('delete from templates where `type` <> \'template\' and id = $1', $id) or die(DB\mysqli_query_error());
		return Array('success' => true, 'id' => $id);
	}
	public function moveElement($p){
		if(!Security::canManage()) throw new \Exception(L\Access_denied);
		// DB\mysqli_query_p('update templates set pid = $2 where id = $1', array($p->id, is_numeric($p->pid) ? $p->pid : null)) or die(DB\mysqli_query_error());
		// return array('success' => true);
		/* get old pid */
		$res = DB\mysqli_query_params('select pid, `order` from templates where id = $1', $p->id) or die(DB\mysqli_query_error());
		$old_pid = 0;
		$old_order = 0;
		if($r = $res->fetch_row()){
			$old_pid = $r[0];
			$old_order = $r[1];
		}
		$res->close();
		/* end of get old pid */
		$p->target_id = is_numeric($p->target_id) ? $p->target_id : null;
		$order = 1;
		switch($p->point){
			case 'above':
				/* get relative node order and pid */
				$res = DB\mysqli_query_params('select pid, `order` from templates where id = $1', $p->target_id) or die(DB\mysqli_query_error());
				if($r = $res->fetch_row()){
					$p->target_id = $r[0];
					$order = $r[1];
				}
				$res->close();
				DB\mysqli_query_params('update templates set `order` = `order` + 1 where pid = $1 and `order` >= $2', array($p->target_id, $order)) or die(DB\mysqli_query_error());
				break;
			case 'below':
				/* get relative node order and pid */
				$res = DB\mysqli_query_params('select pid, `order` from templates where id = $1', $p->target_id) or die(DB\mysqli_query_error());
				if($r = $res->fetch_row()){
					$p->target_id = $r[0];
					$order = $r[1]+1;
				}
				$res->close();
				DB\mysqli_query_params('update templates set `order` = `order` + 1 where pid = $1 and `order` >= $2', array($p->target_id, $order)) or die(DB\mysqli_query_error());
				break;
			default:
				$res = DB\mysqli_query_params('select max(`order`) from templates where pid = $1', $p->target_id) or die(DB\mysqli_query_error());
				if($r = $res->fetch_row()){
					$order = $r[0]+1;
				}
				$res->close();
		}
		DB\mysqli_query_params('update templates set pid = $2, `order` = $3 where id = $1', array($p->id, $p->target_id, $order)) or die(DB\mysqli_query_error());
		DB\mysqli_query_params('update templates set `order` = `order` - 1 where pid = $1 and `order` > $2', array($old_pid, $old_order)) or die(DB\mysqli_query_error());
		return array('success' => true);
	}
	public function readAll($p){// return templates list
		$sql = 'SELECT t.id, t.pid, t.type, t.l'.USER_LANGUAGE_INDEX.' `title`, t.iconCls, t.cfg, t.info_template, `visible` FROM templates t where is_folder = 0 order by 3, `order`, 4';
		$data = Array();
		$res = DB\mysqli_query_params($sql) or die(DB\mysqli_query_error());
		while($r = $res->fetch_assoc()){
			if(!empty($r['cfg'])) $r['cfg'] = (array)json_decode($r['cfg']);
			$data[] = $r;
		}
		$res->close();
		return Array('success' => true, 'data' => $data);
	}
	public function loadTemplate($params){
		if(!Security::canManage()) throw new \Exception(L\Access_denied);
		
		/* get field names of template properties editing template */
		$template_fields = array();
		$res = DB\mysqli_query_params('SELECT ts.id, ts.name FROM templates_structure ts JOIN templates t ON t.id = ts.template_id WHERE t.type = \'template\'') or die(DB\mysqli_query_error());
		while($r = $res->fetch_row())
			$template_fields[$r[1]] = $r[0];
		$res->close();
		/* end of get field names of template properties editing template */

		$data = array();
		$res = DB\mysqli_query_params('select id, `type`, name, '.config\language_fields.', visible, iconCls, default_field, cfg from templates where id = $1', $params->data->id) or die(DB\mysqli_query_error());

		if($r = $res->fetch_assoc()){
			if(!empty($r['cfg'])){
				$cfg = json_decode($r['cfg']);
				foreach($cfg as $k => $v) $r[$k] = $v;
			}
			unset($r['cfg']);
			$data = $r;
		}
		$res->close();
		foreach($data as $k => $v)
			if(isset($template_fields[$k])){
				$data['properties']['values']['f'.$template_fields[$k].'_0'] = array('value' => $v);
				if($k !== 'iconCls') unset($data[$k]);
			}
		return array('success' => true, 'data'  => $data);
	}
	public function getTemplatesStructure(){
		$rez = array('success' => true, 'data' => array());
		$sql = 'SELECT ts.id, ts.pid, t.id template_id, ts.tag, ts.`level`, ts.`name`, ts.l'.USER_LANGUAGE_INDEX.' `title`, ts.`type`, ts.`order`, ts.cfg, (coalesce(t.title_template, \'\') <> \'\' ) `has_title_template`'.
				' FROM templates t left join templates_structure ts on t.id = ts.template_id ORDER BY template_id, `order`';
		$res = DB\mysqli_query_params($sql) or die(DB\mysqli_query_error());
		while($r = $res->fetch_assoc()){
			$t = $r['template_id'];
			unset($r['template_id']);
			if( ($r['type'] == '_auto_title') && ($r['has_title_template'] == 0) ) $r['type'] = 'varchar';
			unset($r['has_title_template']);
			$data[$t][] = $r;
		}
		$res->close();
		return array('success' => true, 'data'  => $data);
	}
	public function saveTemplate($p){
		if(!Security::canManage()) throw new \Exception(L\Access_denied);
		$d = json_decode($p['data']);

		/* get field names of template properties editing template */
		$template_fields = array();
		$res = DB\mysqli_query_params('SELECT ts.id, ts.name FROM templates_structure ts JOIN templates t ON t.id = ts.template_id WHERE t.type = \'template\'') or die(DB\mysqli_query_error());
		while($r = $res->fetch_row())
			$template_fields[$r[0]] = $r[1];
		$res->close();
		/* end of get field names of template properties editing template */

		/*{"id":"11"
		,"type":"2"
		,"name":null
		,"l1":"reply"
		,"l2":null
		,"l3":"ответ"
		,"visible":"1"
		,"iconCls":null
		,"default_field":null
		,"files":"1"
		,"main_file":"1"
		,"subjects":"1"
		,"claimers":"1"
		,"properties":{
			"values":{
				"f270_0":{"value":"1","info":""}
				,"f267_0":{"value":"icon-arrow-left","info":""}
				,"f271_0":{"value":"1","info":""}
				,"f272_0":{"value":"1","info":""}
			}
		}
		,"fields":{"values":{}}}/**/
		$cfgProperties = array('gridJsClass', 'files', 'main_file');//, 'subjects', 'claimers', 'violations_edit', 'violations_association', 'decisions_association', 'complaints', 'appeals'
		$cfg = array();
		$params = array(
			'id' => empty($d->id) ? null: $d->id
		);
		$values_string = array('$1');
		$on_duplicate = array();//'`order` = $2, visible = $3, iconCls = $4, default_field = $5, cfg = $6';
		$i = 1;
		
		if(!empty($d->properties->values))
		foreach($d->properties->values as $f => $fv){
			$id = explode('_', $f); 
			$id = array_shift($id);
			$id = substr($id, 1);
			if(isset($template_fields[$id]))
			if(in_array($template_fields[$id], $cfgProperties)) $cfg[$template_fields[$id]] = $fv->value;
			else{ 
				$i++;
				$params[$template_fields[$id]] = $fv->value;
				$values_string[] = '$'.$i;
				$on_duplicate[] = '`'.$template_fields[$id].'` = $'.$i;
				if($template_fields[$id] == 'iconCls') $d->iconCls = $fv->value;
				if($template_fields[$id] == 'visible') $d->visible = $fv->value;
			}
		}
		$i++;
		$cfg = json_encode($cfg);
		$params['cfg'] = $cfg;
		$values_string[] = '$'.$i;
		$on_duplicate[] = '`cfg` = $'.$i;
		$values_string = implode(', ', $values_string);
		$on_duplicate = implode(', ', $on_duplicate);
		
		Util\getLanguagesParams($p, $params, $values_string, $on_duplicate);
		
		DB\mysqli_query_params('insert into templates (`'.implode('`,`', array_keys($params)).'`) values ('.$values_string.') on duplicate key update '.$on_duplicate, array_values($params)) or die(DB\mysqli_query_error());
		if(!is_numeric($params['id'])) $params['id'] = DB\last_insert_id();

		return array('success' => true, 'data' => $d);
	}
	
	
	private static function sort_template_rows(&$array, $pid = null, &$result){
		if(!empty($array[$pid])){
			foreach($array[$pid] as $r){
				array_push($result, $r);
				Templates::sort_template_rows($array, $r['id'], $result);
			}
		}
	}	
	
	public static function getTemplateStructure($template_id, $sorted = true){
		/* get template structure */
		$unsortedStructure = array();
		$res = DB\mysqli_query_params('SELECT id, pid, tag, `level`, ts.name, ts.l'.USER_LANGUAGE_INDEX.' `title`, `type`, `order`, cfg, (select count(*) from templates_structure where pid = ts.id) children FROM templates_structure ts WHERE template_id = $1 order by `order`', $template_id) or die(DB\mysqli_query_error());
		while($r = $res->fetch_assoc()){
			if(!empty($r['cfg'])) $r['cfg'] = json_decode($r['cfg']);
			$unsortedStructure[$r['pid']][$r['id']] = $r;
		}
		$res->close();
		
		if(!$sorted) return $unsortedStructure;
	
		$sortedStructure = array();
		Templates::sort_template_rows($unsortedStructure, null, $sortedStructure);
		/* end of get template structure */
		return $sortedStructure;
	}
	
	private static function iterateFieldsWithData(&$template_structure, &$gridData, $pid = null, $duplicate_id = 0){
		$rez = array();
		if(empty($template_structure[$pid])) return false;
		foreach($template_structure[$pid] as $field){
			$field['value'] = @$gridData['values']['f'.$field['id'].'_'.$duplicate_id];
			$subfields = Templates::iterateFieldsWithData($template_structure, $gridData, $field['id']);
			if(!empty($field['value']) || !empty($subfields)){
				if($field['tag'] == 'f') $rez[] = $field;
				if(!empty($subfields)) $rez = array_merge($rez, $subfields);
			}
			if(isset($gridData['duplicateFields'][$field['id']]))
			foreach($gridData['duplicateFields'][$field['id']] as $child_duplicate_id => $child_duplicate_pid)
				if($child_duplicate_pid == $duplicate_id){
					$field['value'] = $gridData['values']['f'.$field['id'].'_'.$child_duplicate_id];
					$subfields = Templates::iterateFieldsWithData($template_structure, $gridData, $field['id'], $child_duplicate_id);
					if(!empty($field['value']) || !empty($subfields)){
						if($field['tag'] == 'f') $rez[] = $field;
						if(!empty($subfields)) $rez = array_merge($rez, $subfields);
					}
				}
		}
		return $rez;
	}
	
	public static function getGroupedTemplateFieldsWithData($template_id, $object_id){
		$rez = array();
		$tf = Templates::getTemplateFieldsWithData($template_id, $object_id);
		if(!empty($tf))
		foreach($tf as $f){
			if(empty($f['cfg'])) $rez['body'][] = $f;
			elseif(@$f['cfg']->showIn == 'top') $rez['top'][] = $f;
			elseif(@$f['cfg']->showIn == 'tabsheet') $rez['bottom'][] = $f;
			else $rez['body'][] = $f;
		}
		return $rez;
	}
	
	public static function getTemplateFieldsWithData($template_id, $object_id){
		//helper function for get template non empty fields for a object. Used for info/preview purposes
		$ts = Templates::getTemplateStructure($template_id, false);
		$data = Templates::getObjectsData($object_id);
		return Templates::iterateFieldsWithData($ts, $data);
	}

	public static function getObjectsData($object_id){ //object, contact
		if(empty($object_id) || !is_numeric($object_id)) return;
		$sql = 'SELECT concat(\'f\', field_id, \'_\', duplicate_id) field, id, `value`, info, files, private_for_user `pfu` FROM objects_data WHERE object_id = $1';
		$sql2 = 'select id, pid, field_id from objects_duplicates where object_id = $1 order by id';

		require_once 'Security.php';
		$rez = Array();
		$is_admin = Security::canManage();
		$res = DB\mysqli_query_params($sql, $object_id) or die(DB\mysqli_query_error());
		while($r = $res->fetch_assoc()){
			$field = $r['field'];
			unset($r['field']);
			if(empty($r['pfu']) || ($r['pfu'] == $_SESSION['user']['id']) || $is_admin) $rez['values'][$field] = $r; else $rez['hideFields'][] = $field;
		}
		$res->close();
		$res = DB\mysqli_query_params($sql2, $object_id) or die(DB\mysqli_query_error());
		while($r = $res->fetch_row()) $rez['duplicateFields'][$r[2]][$r[0]] = $r[1];
		$res->close();
		return $rez;
	}
	public static function getIcon($template_id){
	
	}
	public static function getTemplateFieldValue(&$field, $format = 'html'){
		@$value = $field['value']['value'];
		switch($field['type']){
			case 'boolean':
			case 'checkbox':
			case 'object_violation':
				$value = empty($value) ? L\no : L\yes;
				break;
			case '_sex':
				switch($value){
					case 'm': $value = L\male; break;
					case 'f': $value = L\female; break;
					default: $value = '';
				}
				break;
			case '_language':
				@$value = $GLOBALS['language_settings'][$GLOBALS['languages'][$value -1]][0];
				break;
			case 'combo':
			case 'popuplist': 
				if(!empty($value)) $value = Util\getThesauriTitles($value); break;
				break;
			case '_case': 
					if(empty($value)) { $value = ''; break; }
					$a = explode(',', $value);
					$a = array_filter($a, 'is_numeric');
					if(empty($a)) { $value = ''; break; }
					$res = DB\mysqli_query_params('select name from cases where id in ('.implode(',', $a).') order by 1') or die(DB\mysqli_query_error());
					$value = array();
					while($r = $res->fetch_row()) $value[] = $r[0];
					$res->close();
					if(sizeof($value) == 1) $value = $value[0];
					break;
			case '_case_object': 
					if(empty($value)) { $value = ''; break; }
					$a = explode(',', $value);
					$a = array_filter($a, 'is_numeric');
					if(empty($a)) { $value = ''; break; }
					$res = DB\mysqli_query_params('select coalesce(custom_title, title) from objects where id in ('.implode(',', $a).') order by 1') or die(DB\mysqli_query_error());
					$value = array();
					while($r = $res->fetch_row()) $value[] = $r[0];
					$res->close();
					if(sizeof($value) == 1) $value = $value[0];
					break;
			case '_objects': 
					if(empty($value)) { $value = ''; break; }
					$a = explode(',', $value);
					$a = array_filter($a, 'is_numeric');
					if(empty($a)){ $value = ''; break; }
					$ids = implode(',', $a);
					
					switch(@$field['cfg']->source){
					case 'tree': 
					case 'related': 
					case 'field':
						$value = 'tree';
						$sql = 'select id, name, `type`, `subtype`, cfg, f_get_tree_ids_path(pid) `path` from tree where id in ('.$ids.')';
						break;
					case 'users': 
					case 'groups': 
					case 'usersgroups': 
						$value = 'users_groups';
						$sql = 'select id, name, l'.USER_LANGUAGE_INDEX.' `title`, CASE WHEN (`type` = 1) THEN \'icon-users\' ELSE CONCAT(\'icon-user-\', coalesce(sex, \'\') ) END `iconCls` from users_groups where id in ('.$ids.')';
						break;
					default: 
						$value = 'thesauri';
						$sql = 'select id, l'.USER_LANGUAGE_INDEX.' `title`, iconCls from tags where id in ('.$ids.') order by `order`';
						break;
					}
					
					$res = DB\mysqli_query_params($sql) or die(DB\mysqli_query_error());
					$value = array();
					while($r = $res->fetch_assoc()){
						@$label = Util\coalesce($r['title'], $r['name']);
						if(!empty($r['path'])) $label = ($format == 'html') ? '<a class="locate click" path="'.$r['path'].'" nid="'.$r['id'].'">'.$label.'</a>' : $label;

						
						switch(@$field['cfg']->renderer){
							case 'listGreenIcons': 
								$value[] =  ($format == 'html') ? '<li class="icon-padding icon-element">'.$label.'</li>' : $label;
								break;
							case 'listObjIcons': 
								if(!empty($r['cfg'])) $r['cfg'] = json_decode($r['cfg']);
								
								$icon = '';
								switch(@$field['cfg']->source){
								case 'tree': 
								case 'related': 
								case 'field':
									$icon = Browser::getIcon($r);
									break;
								default: 
									$icon = Util\coalesce($r['iconCls'], 'icon-none');
									break;
								}

								$value[] = ($format == 'html') ? '<li class="icon-padding '.$icon.'">'.$label.'</li>': $label;
								break;
							default:
								$value[] = ($format == 'html') ? '<li>'.$label.'</li>': $label;
						}
					}
					$res->close();
					$value = ($format == 'html') ? '<ul class="clean">'.implode('', $value).'</ul>': implode(', ', $value);
					break;

			case 'date': $value = Util\formatMysqlDate($value); break;
			case 'datetime': $value = Util\formatMysqlTime($value); break;
			case 'html': 
				//$value = trim(strip_tags($value));
				//$value = nl2br($value);
				break;
		}
		return $value;
	}
}
?>