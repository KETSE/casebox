<?php
require_once 'Cases.php';
class Objects{
	function load($p){
		/* procedure for loading all necessary properties of a given case object */
		$rez = array();
		$d = $p->data; //shortcut
		// SECURITY: check if object id is numeric 
		if(!is_numeric($d->id)) throw new Exception(L\Wrong_input_data);
		// end of SECURITY: check if object id is numeric 
		
		$template = $this->getTemplateInfo(null, $d->id);
		$rez['template_id'] = $template['id'];
		$rez['template_pid'] = $template['pid'];
		$rez['iconCls'] = $template['iconCls'];
		$rez['type'] = $template['type'];
		$d->case_id = Cases::getId($d->id);
		//die('!'.$d->case_id);
		// SECURITY: check if object exists in DB
		//if(!$this->getUniqueObjectId($d->case_id, $rez['template_id'], $d->id)) throw new Exception(L\Object_not_found);
		// end of SECURITY: check if object exists in DB
		// SECURITY: check if this objects case is opened by current user 
		//if(!Security::checkIfCaseOpened($d->case_id)) throw new Exception(L\case_not_oppened);
		// end of SECURITY: check if this objects case is opened by current user 
		// SECURITY: check if current user has at least read access to this case
		//if(!Security::canReadCase($d->case_id)) throw new Exception(L\Access_denied);
		// end of SECURITY: check if current user has at least read access to this case
		
		$rez['id'] = $d->id;
		if(empty($d->case_id)) $d->case_id = null;
		$rez['case_id'] = $d->case_id;
		if($rez['template_pid'] == 5 || $rez['template_pid'] == 6){
			$rez['spentTime'] = array();
			$rez['tasks'] = array();
		}

		/* get object title */
		$res = mysqli_query_params('SELECT t.pid, o.title, o.custom_title, t.name, o.date_start, o.date_end, o.author, o.private_for_user `pfu`, (o.date_end < now()) is_active, files_count  '.
			',f_get_tree_ids_path(t.pid) `path` '.
			',f_get_tree_path(t.id) `pathtext` '.
			',t.cdate, t.udate, t.cid, t.uid '.
			'FROM objects o '.
			' join tree t on o.id = t.id '.
			'WHERE o.id = $1', Array($d->id, $_SESSION['user']['id'])) or die(mysqli_query_error());
		if($r = $res->fetch_assoc())
			// $rez['spentTime'] = array('hours' => $r['hours'], 'minutes' => $r['minutes']);
			// unset($r['hours']);
			// unset($r['minutes']);
			$rez = array_merge($rez, $r);
		$res->close();
		/* end of get object title */
		
		$rez['tags'] = $this->getObjectTagIds($rez['id']);

		$rez['gridData'] = Templates::getObjectsData($d->id);

		/* get Tasks */
		$sql = 'SELECT distinct t.id, t.title, t.description, t.`date_end`, t.cdate, t.responsible_user_ids, t.responsible_party_id, t.cid, t.completed  '.
			',(select l'.UL_ID().' from tags where id = t.responsible_party_id) responsible_party '.
			'FROM tasks t left join tasks_responsible_users ru on t.id = ru.task_id and ru.user_id = $2 where t.object_id = $1 and ((ru.user_id = $2) || (t.cid = $2) || (t.privacy = 0 ))  order by t.cdate';
		$res = mysqli_query_params($sql, Array($d->id, $_SESSION['user']['id'])) or die(mysqli_query_error());
		while($r = $res->fetch_assoc()){
			$res2 = mysqli_query_params('select l'.UL_ID().' FROM users_groups WHERE id =$1', $r['cid']) or die(mysqli_query_error());
			if($r2 = $res2->fetch_row()) $r['owner'] = $r2[0];
			$res2->close();

			if($r['cid'] != $r['responsible_user_ids']){
				$r['users'] = array();
				$res2 = mysqli_query_params('select id, l'.UL_ID().' FROM users_groups WHERE id in (0'.$r['responsible_user_ids'].') order by 2') or die(mysqli_query_error());
				while($r2 = $res2->fetch_row()) $r['users'][$r2[0]] = $r2[1];
				$res2->close();
			}
			$rez['tasks'][] = $r;
		}
		$res->close();
		/* end of get Tasks */

		global $data; // this method is used also internally (by getInfo method), so we skip logging for "load" method in this cases.
		if(is_object($data) && ($data->method == 'load') ) /**/
		Log::add(Array('action_type' => 11, 'case_id' => $d->case_id, 'object_id' => $d->id ));
		
		return Array('success' => true, 'data' => $rez);
	}
	function save($p){
		$log_action_type = 9; // update action
		$object_title = '';
		$object_custom_title = '';
		$object_date_start = null;
		$object_date_end = null;
		$object_violation = false;
		$object_author = null;
		//$object_iconCls = null;
		$fields = Array();
		$update_ids_icons = Array(); //$updated_decision_ids = Array(); //collecting ids to update their icon sets corresponding to the new values

		$d = json_decode($p['data']);
		fireEvent('beforeobjectsave', $d);
		//var_dump($d);
		$initial_object_id = $d->id;
		$d->case_id = null;
		//if(!is_numeric($d->case_id)) throw new Exception(L\Wrong_input_data);
		$pid = coalesce($d->pid, $d->case_id);
		// SECURITY: check if current user has at least read access to this case
		//if(!Security::canWriteCase($d->case_id)) throw new Exception(L\Access_denied);
		// end of SECURITY: check if current user has at least read access to this case
		// SECURITY: check if this objects case is opened by current user 
		//if(!Security::checkIfCaseOpened($d->case_id)) throw new Exception(L\case_not_oppened);
		// end of SECURITY: check if this objects case is opened by current user 

		$template = $this->getTemplateInfo($d->template_id, $d->id);
		
		/* analisys of object id (inserting if new) */
		//if($template['id'] == 1) $d->id = $this->getUniqueObjectId($d->case_id, $template['id'], $d->id); //this is for case card
		
		if(!is_numeric($d->id)){
			mysqli_query_params('insert into tree (pid, name, `type`, subtype, cid, uid) values ($1, $2, $3, $4, $5, $5)', array($pid, 'new case object', 4, $template['type'], $_SESSION['user']['id'])) or die(mysqli_query_error());
			$d->id = last_insert_id();
			$sql = 'INSERT INTO objects (id, case_id, `title`, template_id, cid) VALUES($1, $2, $3, $4, $5)';
			$params = Array($d->id, $d->case_id, '', $template['id'], $_SESSION['user']['id']);
			mysqli_query_params($sql, $params) or die(mysqli_query_error());
			
			$log_action_type = 8; //else throw new Eception(L\Error_creating_object); // create action
		}
		/* end of analizing object id */

		/* save objects tags */
		if(!empty($d->tags)){
			for($i = 3; $i< 5; $i++)
			if(isset($d->tags->{$i})){
				$tags = array_filter($d->tags->{$i}, 'is_numeric');
				mysqli_query_params('delete from objects_tags where object_id = $1 and level = $2 and tag_id not in(0'.implode(',', $tags).')', array($d->id, $i)) or die(mysqli_query_error());
				if(!empty($tags)) mysqli_query_params('insert into objects_tags (object_id, level, tag_id) values($1, $2,'.implode('),($1,$2,', $tags).') on duplicate key update tag_id = tag_id', array($d->id, $i)) or die(mysqli_query_error());
			}
		}
		/* end of save objects tags */
		
		/* save object duplicates from grid */
		$duplicate_ids = Array(0 => 0);
		if( isset($d->gridData->duplicateFields) ){
			$sql = 'INSERT INTO objects_duplicates (pid, object_id, field_id) VALUES ($1, $2, $3)';
			foreach($d->gridData->duplicateFields as $field_id => $fv){
				$i = 0;
				foreach($fv as $duplicate_id => $duplicate_pid){
					if(!is_numeric($duplicate_id)){
						mysqli_query_params($sql, Array($duplicate_ids[$duplicate_pid], $d->id, $field_id)) or die(mysqli_query_error());
						$duplicate_ids[$duplicate_id] = last_insert_id();
					}else $duplicate_ids[$duplicate_id] = $duplicate_id;
					$fields[$field_id]['duplicates'][$i]['id'] = $duplicate_id;
					$i++;
				}
			}
		}
		$filter_secure_fields = Security::isAdmin() ? '' : ' and id not in (select duplicate_id from objects_data where object_id = $1 and duplicate_id <> 0 and private_for_user <> '.$_SESSION['user']['id'].') ';
		mysqli_query_params('delete from objects_duplicates where object_id = $1 and (id not in ('.implode(',', array_values($duplicate_ids)).') )'.$filter_secure_fields, $d->id) or die(mysqli_query_error());
		/* end of save object duplicates from grid */

		$object_title = str_replace(array('{template_title}', '{phase_title}'), array($template['title'], ''/*$phase['name']/**/), $template['title_template']);
		
		/* save object values from grid */
		$sql = 'INSERT INTO objects_data (object_id, field_id, duplicate_id, `value`, info, files, private_for_user) VALUES ($1, $2, $3, $4, $5, $6, $7)
				ON DUPLICATE KEY UPDATE object_id = $1, field_id = $2, duplicate_id = $3, `value` = $4, info = $5, files = $6, private_for_user = $7';
		$ids = Array(0);
		$log = '';
		if(isset($d->gridData))
		foreach($d->gridData->values as $f => $fv){ //$c => $cv
			if(!isset($fv->value)) $fv->value = null;
			$f = explode('_', $f);
			$field_id = substr($f[0], 1);
			$field = Array();
			$res = mysqli_query_params('select name, type from templates_structure where id = $1', $field_id) or die(mysqli_query_error());
			if($r = $res->fetch_assoc()) $field = $r;
			$res->close();
			
			$duplicate_id = intval($duplicate_ids[$f[1]]);
			$duplicate_index = 0;
			if(isset($fields[$field_id]['duplicates']))
			foreach($fields[$field_id]['duplicates'] as $k => $v){
				if(is_array($v['id'])){
					if($v['id'] == $duplicate_id) $fields[$field_id]['duplicates'][$k]['index'] = $duplicate_index;
					else $duplicate_index++;
				}
			}
			/* for titles processing */
			$v = $fv->value;
			
			if($field['name'] == '_title') $object_custom_title = $v;
			switch($field['type']){
				//case 'object_title': $object_custom_title = $v; break;
				case 'date': 
				case 'datetime': 
					if($field['name'] == '_date_start') $object_date_start = $v;
					elseif($field['name'] == '_date_end') $object_date_end = $v;
					$v = empty($v) ? null : implode('.', array_reverse(explode('-', substr($v, 0, 10)))); 
					break;
				//case 'object_author': $object_author = $v; 
				case 'combo': 
					if( (strpos($object_title, '{f'.$field_id.'}') !== false) || (strpos($object_title, '{'.$field['name'].'}') !== false) || (strpos($object_title, '<?php ') >= 0)){
						$res = mysqli_query_params('SELECT l'.UL_ID().' `name` FROM tags WHERE id = $1', $v) or die(mysqli_query_error());
						if($r = $res->fetch_row()) $v = addslashes($r[0]);
						$res->close();
					}
					break;
				case 'popuplist': 
					if( (strpos($object_title, '{f'.$field_id.'}') !== false) || (strpos($object_title, '{'.$field['name'].'}') !== false) || (strpos($object_title, '<?php ') >= 0)){
						$a = explode(',', $v);
						$a = array_filter($a, 'is_numeric');
						if(!empty($a)){
							$v = array();
							$res = mysqli_query_params('SELECT l'.UL_ID().' `name` FROM tags WHERE id in ('.implode(',', $a).')') or die(mysqli_query_error());
							while($r = $res->fetch_row()) $v[] = addslashes($r[0]);
							$res->close();
							$v = implode(', ', $v);
						}
					}
					break;
				case 'case_title': mysqli_query_params('update cases set name = $1 where id = $2', Array($v, $d->case_id)) or die(mysqli_query_error()); break;
				case 'object_violation': $object_violation = $v; break;
			}

			$object_title = str_replace( '{f'.$field_id.'}', $v, $object_title);
			$object_title = str_replace( '{'.$field['name'].'}', $v, $object_title);
			$object_title = str_replace( '{'.$field['name'].'_info}', $fv->info, $object_title);
			if($duplicate_id > 0){
				$fields[$field_id]['duplicates'][$duplicate_index]['value_id'] = $fv->value;
				$fields[$field_id]['duplicates'][$duplicate_index]['value'] = $v;
				$fields[$field_id]['duplicates'][$duplicate_index]['details'] = $fv->info;
				//$fields[$field_id]['duplicates'][$duplicate_index]['title'] = $field['name'];
				$fields[$field['name']] = &$fields[$field_id];
			}else{
				$fields[$field_id]['value_id'] = $fv->value;
				$fields[$field_id]['value'] = $v;
				$fields[$field_id]['details'] = $fv->info;
				$fields[$field_id]['title'] = $field['name'];
				$fields[$field['name']] = &$fields[$field_id];
			}
			/* end of for titles processing */
			if(empty($fv->pfu)) $fv->pfu = null;
			@$params = Array($d->id, $field_id, $duplicate_id, $fv->value, $fv->info, $fv->files, $fv->pfu);
			mysqli_query_params($sql, $params) or die(mysqli_query_error());
			$res = mysqli_query_params('select id from objects_data where object_id = $1 and field_id = $2 and duplicate_id = $3', Array($d->id, $field_id, $duplicate_id) ) or die(mysqli_query_error());
			if($r = $res->fetch_row())
			array_push($ids, $r[0]);
			$res->close();
		}
		$filter_secure_fields = Security::isAdmin() ? '' : ' and ((private_for_user is null) or (private_for_user = '.$_SESSION['user']['id'].')) ';
		mysqli_query_params('delete from objects_data where object_id = $1 and (id not in ('.implode(',', $ids).') )'.$filter_secure_fields, $d->id) or die(mysqli_query_error());
		
		//replacing field titles into object title variable
		$sql = 'select id, name, l'.UL_ID().' from templates_structure where template_id = $1 and (($2 like concat(\'%{f\', id, \'t}%\') ) or ($2 like concat(\'%{\', name, \'_title}%\')))';
		$res = mysqli_query_params($sql, Array($template['id'], $object_title)) or die(mysqli_query_error());
		while($r = $res->fetch_row()) $object_title = str_replace('{f'.$r[0].'t}', $r[2], $object_title);
		$res->close();
		// evaluating the title if contains php code
		if(strpos($object_title, '<?php') !== false){
			@eval(' ?>'.$object_title.'<?php ');
			if(!empty($title)) $object_title = $title;
		}
		//replacing any remained field placeholder from the title
		$object_title = preg_replace('/\{[^\}]+\}/', '', $object_title);
		$object_title = stripslashes($object_title);

		// updating object properties into the db																/*(empty($object_iconCls) ? '' : ', iconCls = $7')/**/
		@mysqli_query_params('update objects set title = $1, custom_title = $2, date_start = $3, date_end = $4, author = $5'.
			', iconCls = $7, private_for_user = $8, uid = $9 where id = $6', 
			Array(ucfirst($object_title), $object_custom_title, $object_date_start, $object_date_end, $object_author, $d->id, $this->getObjectIcon($d->id), $d->pfu, $_SESSION['user']['id'])) or die(mysqli_query_error());
		//if(is_debug_host()) echo $this->getObjectIcon($d->id).$d->id.'!';
		/* end of updating object properties into the db */
		$s = '{"data":{"case_id": '.coalesce($d->case_id, 'null').', "id":'.$d->id.', "template_id": '.$template['id'].'}}';
		$p = json_decode($s);
		
		Log::add(Array('action_type' => $log_action_type, 'case_id' => $d->case_id, 'object_id' => $d->id));

		$update_ids_icons = array_keys($update_ids_icons);
		foreach($update_ids_icons as $id) mysqli_query_params('update objects set iconCls = $1 where id = $2', array($this->getObjectIcon($id), $id)) or die(mysqli_query_error());
		
		fireEvent('afterobjectsave', $d);
		
		SolrClient::runCron();
		
		return $this->load( $p );
	}
	
	function getPreview($id){ // This function is supposed to be used from communications glid list, to get the objects short info
		$rez = array();
		if(!is_numeric($id)) return;

		$top = '';
		$body = '';
		$bottom = '';
		$data = $this->load(json_decode('{"data":{"id":'.$id.'} }'));
		$data = $data['data'];
		
		// $files = $this->getFiles(json_decode('{"object_id":"'.$id.'"}'));
		// foreach($files['data'] as $f) $data['files'][$f['id']] = $f;
		
		$gf = Templates::getGroupedTemplateFieldsWithData($data['template_id'], $id);
		
		if(!empty($gf['top']))
			foreach($gf['top'] as $f){
				if($f['name'] == '_title') continue;
				if($f['name'] == '_date_start') continue;
				$v = Templates::getTemplateFieldValue($f);
				if(is_array($v)) $v = implode(', ', $v);
				if(!empty($v)){
					//$top .= (($i > 0) ? ', ': '').'<span class="cG">'.$f['title'].': </span>'.$v;
					$top .= '<tr><td class="prop-key">'.$f['title'].'</td><td class="prop-val">'.$v.'</td></tr>';
					$i++;
				}
			}
		if(!empty($gf['body']))
			foreach($gf['body'] as $f){
				$v = Templates::getTemplateFieldValue($f);
				if(is_array($v)) $v = implode('<br />', $v);
				
				if(empty($v) && empty($f['value']['info']) && empty($f['value']['files'])) continue;
				$body .= '<tr><td'.(empty($f['level']) ? '' : ' style="padding-left: '.($f['level'] * 10).'px"').' class="prop-key">'.$f['title'].'</td><td class="prop-val">'.$v.
					(empty($f['value']['info']) ? '' : '<p class="prop-info">'.$f['value']['info'].'</p>').'</td></tr>';
			}
		
		$tmp = Files::getFilesBlockForPreview($id);
		if(!empty($tmp)) $bottom .= '<div class="obj-preview-h pt10">'.L\Files.'</div>'.$tmp.'<br />';
		$tmp = Tasks::getAxtiveTasksBlockForPreview($id);
		if(!empty($tmp)) $bottom .= '<div class="obj-preview-h pt10">'.L\ActiveTasks.'</div>'.$tmp.'<br />';

		if(!empty($gf['bottom']))
			foreach($gf['bottom'] as $f){
				$v = Templates::getTemplateFieldValue($f);
				if(empty($v)) continue;
				$bottom .=  '<div class="obj-preview-h">'.$f['title'].'</div>'.$v.'<br />';
			}
		

		if(!empty($data['tasks'])){
			$d = Array();
			foreach($data['tasks'] as $t){
				$info = $t['owner'];
				if( $t['responsible_user_ids'] != $t['cid'] ){
					/* showing users list */
					$info .= ' &rarr; '.implode(', ',array_values($t['users']));
				}
				$small_fields = array();
				if( $t['responsible_party_id'] != $_SESSION['config']['responsible_party'][1] ){
					/* append responsible part */
					$small_fields[] = L\Party.': '.$t['responsible_party'];
				}
				if(!empty($t['completed'])) $small_fields[] = L\Accomplished_date.': '.formatMysqlDate($t['completed']);
				$info .= ((empty($info) || empty($small_fields)) ? '' : '<br />').implode(', ', $small_fields);
				
				if(!empty($info)) $info = '<br /><span class="fs11 cG">'.$info.'</span>';
				$d[] = '<tr><td><a class="task click" nid="'.$t['id'].'">'.$t['title'].'</a>'.$info.'</td><td>'.formatMysqlDate($t['cdate']).'</td><td>'.formatMysqlDate($t['date_end']);
			}
			if(!empty($d)) $bottom .= '<table border="0" cellpadding="2" width="100%" style="padding: 5px 0px; border-bottom: 1px solid lightgray">'.
				'<tr class="bgcLG cG"><th width="20%" class="icon-padding icon-calendar-task">'.L\Tasks.'</th><th width="25%">'.L\Created.'</th><th width="30%">'.L\Deadline.'</th></tr><tr>'.implode('</tr><tr>', $d).'</tr></table>';
		}
		/* end of tasks */		
		
		Log::add(Array('action_type' => 12, 'case_id' => $data['case_id'], 'object_id' => $data['id'] ));
		if(!empty($top)) $top = '<div class="obj-preview-h">'.L\Details.'</div>'.$top;
		$top .= $body;
		if(!empty($top)) $top = '<table class="obj-preview">'.$top.'</table><br />';

		return '<div style="padding:10px">'.$top.$bottom.'</div>';
	}
	function destroy($p){
		if(empty($p->ids)) return array('success' => false, msg => L\Wrong_id);
		if(!is_array($p->ids)) $p->ids = explode(',',$p->ids);
		$p->ids = array_filter($p->ids, 'is_numeric');
		$p->case_id = Cases::getId($p->ids[0]);
		// SECURITY: check if this objects case is opened by current user 
		if(!Security::checkIfCaseOpened($p->case_id)) throw new Exception(L\case_not_oppened);
		// end of SECURITY: check if this objects case is opened by current user 
		// SECURITY: check if current user has at least read access to this case
		if(!Security::canWriteCase($p->case_id)) throw new Exception(L\Access_denied);
		// end of SECURITY: check if current user has at least read access to this case
		/* selecting the object ids that have to update icon */
		/* SHOULD BE REVIEWED $update_ids_icons = array();
		/* selecting the object ids that have to update icon */
		$update_solr = false;
		if(file_exists('/var/lib/Apache/Solr/Service.php')){
			require_once('/var/lib/Apache/Solr/Service.php');
			$update_solr = true;
		}
		
		Log::add(Array('action_type' => 10, 'case_id' => $p->case_id, 'object_id' => $p->ids[0])); // SHOULD BE REVIEWED FOR MULTIPLE 
		mysqli_query_params('delete from objects where case_id = $1 and id in ('.implode(',', $p->ids).')', $p->case_id) or die(mysqli_query_error());
		mysqli_query_params('delete from tree where id in ('.implode(',', $p->ids).')') or die(mysqli_query_error());//TODO: to think if delete only from tree and with triggers to delete from other tables

		if($update_solr){
			$solr = new Apache_Solr_Service('127.0.0.1', 8983, '/solr/'.CB_PROJ.'_actions/');
			if (! $solr->ping()) { echo L('Solr_connection_error'); return; }
			$solr->deleteByQuery('id:('.implode(' OR ', $p->ids).')');
			$solr->commit();
			unset($solr);
		}
		/*$update_ids_icons = array_keys($update_ids_icons);
		foreach($update_ids_icons as $id) mysqli_query_params('update objects set iconCls = $1 where id = $2', array($this->getObjectIcon($id), $id)) or die(mysqli_query_error());
		/**/
		return Array('success' => true, 'data' => $p->ids);
	}

	function getViolations($object_id){
		if(!is_numeric($object_id)) return Array('success' => false, 'msg' => L\Wrong_id);
		$case_id = Cases::getId($object_id);
		Security::checkCaseReadAction($case_id);
		$data = Array();
		/* this select is for selecting all available violations */
		$sql = 'SELECT vo.id, COALESCE(pvo.custom_title, pvo.title) `decision_title`, th.l'.UL_ID().' `violation_title`, vo.`date_start` `date`
			,(select iconCls from templates where id = pvo.template_id) `decision_icon`
			FROM objects vo 
			LEFT JOIN templates t ON vo.template_id = t.id
			LEFT JOIN objects pvo ON vo.pid = pvo.id
			,tags th
			WHERE pvo.id = $1 and ( th.id = vo.type_id) 
			order by vo.date_start';
		$res = mysqli_query_params($sql, $object_id) or die(mysqli_query_error());
		while($r = $res->fetch_assoc()) $data[] = $r;
		$res->close();
		return Array( 'success' => true, 'data' => $data);
	}
	private function getObjectTags($object_id){
		$rez = array();
		$res = mysqli_query_params('select ot.level, t.id, t.l'.UL_ID().' from objects_tags ot join tags t on ot.tag_id = t.id '.
			'where ot.object_id = $1 order by ot.level, t.order, 3', array($object_id, $_SESSION['user']['language_id'])) or die(mysqli_query_error());
		while($r = $res->fetch_row()) $rez[$r[0]][] = array('id' => $r[1], 'name' => $r[2]);
		$res->close();
		return $rez;
	}
	static function getObjectTagIds($object_id){
		$rez = array();
		$res = mysqli_query_params('select level, tag_id from objects_tags where object_id = $1', $object_id) or die(mysqli_query_error());
		while($r = $res->fetch_row()) $rez[$r[0]][] = $r[1];
		$res->close();
		return $rez;
	}

	public static function getAssociatedObjects($p){
		$data = array();
		if(is_numeric($p)) $p = json_decode('{"id": '.$p.'}');
		if(empty($p->id)) return array('success' => true, 'data' => $data);
		
		// SECURITY: check if this objects case is opened by current user 
		//if(!Security::checkIfCaseOpened($p->case_id)) throw new Exception(L\case_not_oppened);
		// end of SECURITY: check if this objects case is opened by current user 
		// SECURITY: check if current user has at least read access to this case
		//if(!Security::canReadCase($p->case_id)) throw new Exception(L\Access_denied);
		// end of SECURITY: check if current user has at least read access to this case
		
		/* select distinct associated case ids from the case */
		$sql = 'SELECT DISTINCT d.value
		FROM objects co
		JOIN templates_structure s ON co.template_id = s.template_id AND s.type = \'_objects\'
		JOIN objects_data d on. d.field_id = s.id
		WHERE co.id = $1';
		$ids = array();
		$res = mysqli_query_params($sql, $p->id) or die(mysqli_query_error());
		while($r = $res->fetch_row()){
			$a = explode(',',$r[0]);
			foreach($a as $id) if(!empty($id)) $ids[$id] = 1;
		}
		$res->close();
		$ids = array_keys($ids);
		if(empty($ids)) return array('success' => true, 'data' => array());
		/* end of select distinct case ids from the case */
		$sql = 'SELECT DISTINCT t.id, t.`name`, t.date, t.`type`, t.subtype, o.template_id, t2.status FROM tree t '.
		'left join objects o on t.id = o.id '.
		'left join tasks t2 on t.id = t2.id '.
		'WHERE t.id IN ('.implode(',', $ids).') order by 2';
		$res = mysqli_query_params($sql) or die(mysqli_query_error());
		while($r = $res->fetch_assoc()){
			if(!empty($r['date'])){
				$r['date'][10] = 'T';
				$r['date'] .= 'Z';
			}
			$data[] = $r;
		}
		$res->close();
		return array('success' => true, 'data' => $data);
	}

	private function getUniqueObjectId($case_id, $template_id, $object_id = null){
		/* this function will get the id of the first object of a corresponding template for given case, actually it is designed to be used for singeton objects of specific template in the case .
			It can also be used as a check for existance of a object with specified id.
		*/
		$rez = false;
		$sql = 'select id from objects where case_id = $1 and template_id = $2';
		$params = Array($case_id, $template_id);
		if( !empty($object_id) ){
			$sql .= ' and id = $3';
			array_push($params, $object_id);
		}
		$res = mysqli_query_params($sql, $params) or die(mysqli_query_error());
		if($r = $res->fetch_row()) $rez = $r[0];
		$res->close();
		return $rez;
	}
	private function getTemplateInfo($template_id = false, $object_id = false){
		if(is_numeric($template_id)){
			$res = mysqli_query_params('SELECT id, pid, type, l'.UL_ID().' `title`, iconCls, default_field, title_template  FROM templates WHERE id = $1', $template_id) or die(mysqli_query_error());
			if($r = $res->fetch_assoc()){
				$res->close();
				return $r;
			}
			$res->close();		
		}
		if(is_numeric($object_id)){
			$res = mysqli_query_params('SELECT id, pid, type, t.l'.UL_ID().' `title`, iconCls, default_field, title_template from templates t  where id = (select template_id from objects where id = $1)', $object_id) or die(mysqli_query_error());
			if($r = $res->fetch_assoc()){
				$res->close();
				return $r;
			}
			$res->close();		
		}
		throw new Exception(L\Template_not_found);
	}
	private function getObjectIcon($object_id){
		$rez = null;

		/* -- default icon by template /**/
		$res = mysqli_query_params('SELECT t.iconCls  FROM objects o  JOIN templates t ON o.template_id = t.id  WHERE o.id = $1', $object_id) or die(mysqli_query_error());
		if($r = $res->fetch_row()) $rez = $r[0];
		$res->close();		
		return $rez;
	}

	static function getFieldValue($object_id, $field_id, $duplicate_id = 0){
		$rez = null;
		$sql = 'select value from objects_data where object_id = $1 and field_id = $2 and duplicate_id = $3';
		$res = mysqli_query_params($sql, array($object_id, $field_id, $duplicate_id)) or die(mysqli_query_error());
		if($r = $res->fetch_row()) $rez = $r[0];
		$res->close();
		return $rez;
	}
	
	static function setFieldValue($object_id, $field_id, $value, $duplicate_id = 0){
		$rez = null;
		$sql = 'insert into objects_data (object_id, field_id, duplicate_id, `value`) values($1, $2, $3, $4) on duplicate key update `value` = $4';
		mysqli_query_params($sql, array($object_id, $field_id, $duplicate_id, $value)) or die(mysqli_query_error());
	}
	
	static function getSorlData($id){
		$rez = array();
		$lang_field = 'l'.$_SESSION['languages']['per_abrev'][$GLOBALS['CB_LANGUAGE']]['id'];
		$sql = 'SELECT 
			co.id
			,co.template_id
			,co.cid
			,COALESCE(co.custom_title, co.title) `title`
			,t.iconCls
			,co.private_for_user
			FROM objects co left join templates t on co.template_id = t.id where co.id = $1';
		
		$res = mysqli_query_params($sql, $id) or die(mysqli_query_error()."\n".$sql);
		while($r = $res->fetch_assoc()){
			$rez['template_id'] = $r['template_id'];
			$rez['content'] = '';//$r['title']."\n";
			$rez['iconCls'] = $r['iconCls'];
			
			$sql = 'SELECT ts.name, ts.'.$lang_field.' `title`, ts.`type`, ts.solr_column_name, d.`value`, info, files '.
				'FROM objects o '.
				'JOIN objects_data d ON d.object_id = o.id '.
				'JOIN templates_structure ts ON ts.template_id = o.template_id AND ts.id = d.field_id '.
				'WHERE o.id = $1 and (d.private_for_user is null)';//and ts.solr_column_name IS NOT NULL
			$dres = mysqli_query_params($sql, $id) or die(mysqli_query_error()."\n".$sql);
			while($dr = $dres->fetch_assoc()){
				$processed_values = array();
				if(!empty($dr['value']))
				switch($dr['type']){
					case 'boolean':
					case 'checkbox':
					case 'object_violation':
						$dr['value'] = empty($dr['value']) ? false : true;
						break;
					case 'date': 
						$dr['value'] .= 'Z';
						if($dr['value'][10] == ' ') $dr['value'][10] = 'T';
						break;
					//case 'object_author': 
					case 'combo': 
					case 'popuplist': 
						$dr['value'] = implode(',', array_filter(explode(',', $dr['value']), 'is_numeric'));
						if(empty($dr['value'])) break;
						$sql = 'select '.$lang_field.' from tags where id in (0'.$dr['value'].')';
						$sres = mysqli_query_params($sql, array($r['id'])) or die(mysqli_query_error()."\n".$sql);
						$dr['value'] = explode(',', $dr['value']);
						while($sr = $sres->fetch_row()) $processed_values[] = $sr[0];
						$sres->close();
						break;
					case 'html': 
						$dr['value'] = strip_tags($dr['value']);
						//$processed_values[] = strip_tags($dr['value']);
						break;
					case '_auto_title':
					case 'memo': 
					case 'text': 
					case 'int': 
					case 'float':
					case 'time': 
					default: break;
				}
				
				if(!empty($dr['value']))

				if(!empty($processed_values)){
					foreach($processed_values as $v){
						$rez['content'] .= $dr['title'].' '.$v."\n";
					}
				}elseif(!empty($dr['value'])){
					if(!is_array($dr['value']))// $dr['value'] = implode(' ', $dr['value']);
					$rez['content'] .= $dr['title'].' '.
						(in_array($dr['solr_column_name'], array('date_start', 'date_end', 'dates')) ? substr($dr['value'], 0, 10): $dr['value'])."\n";
				}
			}
			$dres->close();
			
			/* selecting action tags */
			$sql = 'SELECT tag_id, level FROM objects_tags WHERE object_id = $1';
			$dres = mysqli_query_params($sql, $id) or die(mysqli_query_error()."\n".$sql);
			while($dr = $dres->fetch_row()) $rez[($dr[1] == 4) ? 'user_tags' : 'sys_tags'][] = intval($dr[0]);
			$dres->close();
			/* end of selecting action tags */
		}
		$res->close();
		return $rez;
	}
}
