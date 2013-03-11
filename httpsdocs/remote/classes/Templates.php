<?php
require_once 'Security.php';
class Templates{

	public function getChildren($params){
		$rez = array();
		$t = explode('/', $params->path);
		$nodeId = intval(array_pop($t));
		switch($t){
			case 0: //user, contact and organization template + case templates folder
		}
		$res = mysqli_query_params('select id, l'.UL_ID().' `text`, `type`, `order`, `visible`, iconCls, (select count(*) '.
			'from templates where pid = t.id) `loaded` from templates t '.
			'where `type` > -100 and pid'.( ($nodeId > 0) ? '=$1' : ' is NULL and is_folder=1' ).' order by `order`, `type`, 2' , $nodeId) or die(mysqli_query_error());
		while($r = $res->fetch_assoc()){
			$r['loaded'] = empty($r['loaded']);
			if(empty($nodeId)) $r['expanded'] = true;
			if(empty($r['type'])) $r['cls'] = 'fwB';
			if($r['type'] < 0) $r['cls'] = 'fwB';
			array_push($rez, $r);
		}
		return $rez;
	}
	public static function getCaseTypeTempleId($case_type_id) {
		$case_type_id = explode('-', $case_type_id);
		$case_type_id = array_pop($case_type_id);
		$case_type_id = intval($case_type_id);
		$id = 0;
		$sql = 'SELECT t.id FROM `templates_per_tags` tpt JOIN templates t ON tpt.`template_id` = t.id AND t.type = 4 WHERE tpt.case_type_id = $1';
		$res = mysqli_query_params($sql, $case_type_id) or die(mysqli_query_error());
		if($r = $res->fetch_row()){
			$id = $r[0];

		}else{
			$name = 'Template for case type '.$case_type_id;
			mysqli_query_params('insert into templates (`type`, name, l1, l2, l3, visible) values (4, $1, $1, $1, $1, 0)', array($name) ) or die(mysqli_query_error());
			$id = last_insert_id();
			mysqli_query_params('insert into templates_per_tags (template_id, case_type_id) values($1, $2) ', array($id, $case_type_id)) or die(mysqli_query_error());
		}
		$res->close();
		return array('success' => true, 'id' => $id);
	}
	
	public function saveElement($params){//new folder or template
		if(!Security::canManage()) throw new Exception(L\Access_denied);
		
		$p = array(
			'id' => empty($params->id) ? null: $params->id
			,'type' => empty($params->type) ? 0: intval($params->type)
			//,'pid' => (empty($params->pid) || (!is_numeric($params->pid))) ? null: $params->pid
		);
		$values_string = '$1, $2';
		$res = mysqli_query_params('select id from templates where is_folder = 1 and `type` = $1 ',-$p['type']) or die(mysqli_query_error());
		if($r = $res->fetch_row()){
			$p['pid'] = $r[0];
			$values_string .= ',$3';
		}
		$res->close();
		$on_duplicate = '';
		getLanguagesParams($params, $p, $values_string, $on_duplicate, $params->text);
		
		mysqli_query_params('insert into templates ('.implode(',', array_keys($p)).') values ('.$values_string.') on duplicate key update '.$on_duplicate, array_values($p)) or die(mysqli_query_error());
		if(!is_numeric(@$params->id)) $p['id'] = last_insert_id();
		
		return array( 'success' => true, 'data' => array('id' => $p['id'], 'pid' => @$p['pid'], 'type' => $p['type'], 'text' => $params->text, 'loaded' => true));
	}
	public function deleteElement($id){
		if(!Security::canManage()) throw new Exception(L\Access_denied);
		mysqli_query_params('delete from templates where `type`> -100 and id = $1', $id) or die(mysqli_query_error());
		return Array('success' => true, 'id' => $id);
	}
	public function moveElement($params){
		mysqli_query_params('update templates set pid = $2 where id = $1', array($params->id, is_numeric($params->pid) ? $params->pid : null)) or die(mysqli_query_error());
		return array('success' => true);
	}
	public function readAll($p){// return templates list
		$sql = 'SELECT t.id, t.pid, t.type, t.l'.UL_ID().' `title`, t.iconCls, t.cfg, t.info_template FROM templates t  order by `order`, 5';
		$data = Array();
		$res = mysqli_query_params($sql) or die(mysqli_query_error());
		while($r = $res->fetch_assoc()){
			if(!empty($r['cfg'])) $r['cfg'] = (array)json_decode($r['cfg']);
			
			if(!isset($r['cfg']['system_tags']))
				$r['cfg']['system_tags'] =  $_SESSION['system_tags'];

			if(!isset($r['cfg']['personal_tags']))
				$r['cfg']['personal_tags'] =  $_SESSION['personal_tags'];
			// if(isset($_SESSION['personal_tags'])) $r['cfg']['personal_tags'] = $_SESSION['personal_tags'];
			// elseif(!isset($r['cfg']['personal_tags'])) $r['cfg']['personal_tags'] = CB_PERSONAL_TAGS;
			
			$data[] = $r;
		}
		$res->close();
		return Array('success' => true, 'data' => $data);
	}
	public function loadTemplate($params){
		if(!Security::canManage()) throw new Exception(L\Access_denied);
		
		/* get field names of template properties editing template */
		$template_fields = array();
		$res = mysqli_query_params('SELECT ts.id, ts.name FROM templates_structure ts JOIN templates t ON t.id = ts.template_id WHERE t.type = -100') or die(mysqli_query_error());
		while($r = $res->fetch_row())
			$template_fields[$r[1]] = $r[0];
		$res->close();
		/* end of get field names of template properties editing template */

		$data = array();
		$res = mysqli_query_params('select id, `type`, name, '.$_SESSION['languages']['string'].', visible, iconCls, default_field, cfg from templates where id = $1', $params->data->id) or die(mysqli_query_error());
		//, `order` - removed
		//,show_files, show_main_file, show_subjects, show_claimers, show_violations_edit, show_violations_association, show_decisions_association, show_complaints, show_appeals, gridJsClass
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
		$sql = 'SELECT ts.id, ts.pid, t.id template_id, ts.tag, ts.`level`, ts.`name`, ts.l'.UL_ID().' `title`, ts.`type`, ts.`order`, ts.cfg, (coalesce(t.title_template, \'\') <> \'\' ) `has_title_template`'.
				' FROM templates t left join templates_structure ts on t.id = ts.template_id ORDER BY template_id, `order`';
		$res = mysqli_query_params($sql) or die(mysqli_query_error());
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
		if(!Security::canManage()) throw new Exception(L\Access_denied);
		$d = json_decode($p['data']);

		/* get field names of template properties editing template */
		$template_fields = array();
		$res = mysqli_query_params('SELECT ts.id, ts.name FROM templates_structure ts JOIN templates t ON t.id = ts.template_id WHERE t.type = -100') or die(mysqli_query_error());
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
			//,'order' => empty($p['order']) ? 0 : intval($p['order'])
			//,'visible' => empty($p['visible']) ? 0 : 1
			//,'iconCls' => empty($p['iconCls']) ? null: $p['iconCls']
			//,'default_field' => empty($p['default_field']) ? null: $p['default_field']
			//,'cfg' => empty($cfg) ? null: json_encode($cfg)
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
		
		getLanguagesParams($p, $params, $values_string, $on_duplicate);
		
		mysqli_query_params('insert into templates (`'.implode('`,`', array_keys($params)).'`) values ('.$values_string.') on duplicate key update '.$on_duplicate, array_values($params)) or die(mysqli_query_error());
		if(!is_numeric($params['id'])) $params['id'] = last_insert_id();

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
		$res = mysqli_query_params('SELECT id, pid, tag, `level`, ts.name, ts.l'.UL_ID().' `title`, `type`, `order`, cfg, (select count(*) from templates_structure where pid = ts.id) children FROM templates_structure ts WHERE template_id = $1 order by `order`', $template_id) or die(mysqli_query_error());
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
		$res = mysqli_query_params($sql, $object_id) or die(mysqli_query_error());
		while($r = $res->fetch_assoc()){
			$field = $r['field'];
			unset($r['field']);
			if(empty($r['pfu']) || ($r['pfu'] == $_SESSION['user']['id']) || $is_admin) $rez['values'][$field] = $r; else $rez['hideFields'][] = $field;
		}
		$res->close();
		$res = mysqli_query_params($sql2, $object_id) or die(mysqli_query_error());
		while($r = $res->fetch_row()) $rez['duplicateFields'][$r[2]][$r[0]] = $r[1];
		$res->close();
		return $rez;
	}

	public static function getTemplateFieldValue(&$field){
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
				@$value = $_SESSION['languages']['per_id'][$value]['name'];
				break;
			case 'combo':
			case 'popuplist': 
				if(!empty($value)) $value = getThesauriTitles($value); break;
				break;
			case '_case': 
					if(empty($value)) { $value = ''; break; }
					$a = explode(',', $value);
					$a = array_filter($a, 'is_numeric');
					if(empty($a)) { $value = ''; break; }
					$res = mysqli_query_params('select name from cases where id in ('.implode(',', $a).') order by 1') or die(mysqli_query_error());
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
					$res = mysqli_query_params('select coalesce(custom_title, title) from objects where id in ('.implode(',', $a).') order by 1') or die(mysqli_query_error());
					$value = array();
					while($r = $res->fetch_row()) $value[] = $r[0];
					$res->close();
					if(sizeof($value) == 1) $value = $value[0];
					break;
			case 'date': $value = formatMysqlDate($value); break;
			case 'datetime': $value = formatMysqlTime($value); break;
			case 'html': 
				//$value = trim(strip_tags($value));
				//$value = nl2br($value);
				break;
			//case '_short_date_format':
		}
		return $value;
	}
}
?>