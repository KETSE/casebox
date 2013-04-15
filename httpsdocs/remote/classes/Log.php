<?php
class Log{

	public function getLastLog(){
		$data = array();
		$res = mysqli_query_params('select l'.UL_ID().' html, date from actions_log where pid is null order by `date` desc, id desc limit 50') or die(mysqli_query_error());
		while($r = $res->fetch_row()) $data[] = array($r[0].' '.formatPastTime($r[1]));
		return Array( 'success' => true, 'data' => $data );
	}
	
	public static function add($p){
		// Available table fields: id, user_id, to_user_ids, case_id, object_id, file_id, office_index, action_type, result, info
		// id can be used to update an existing row
		$id = null;
		$case = array();
		@$obj = Array('id' => $p['object_id'], 'title' => '', 'iconCls' => '');
		$task = array();
		$to_user_ids = array();
		if(!is_array($p)) return false;// if no params specified then exit
		if(isset($_SESSION['user']['id'])) $p['user_id'] = $_SESSION['user']['id'];
		
		//setting case_id if not specified and we have object_id or file_id specified
		if(empty($p['case_id']) && (!empty($p['object_id']) || !empty($p['file_id']) || !empty($p['task_id']))){
			require_once 'Cases.php';
			try{
				@$p['case_id'] = Cases::getId( coalesce($p['object_id'], $p['file_id'], $p['task_id']) );
			}catch(Exception $e){
				//Task is independent, not associated 
			}
		}
		// get case data
		if(!empty($p['case_id'])){
			$res = mysqli_query_params('select id, name, nr from cases where id = $1', $p['case_id']) or die(mysqli_query_error());
			if($r = $res->fetch_assoc()) $case_data = $r;
			$res->close();
		}else $p['case_id'] = null;
		// get object data
		if(!empty($p['object_id'])){
			$sql = 'select o.id,  o.type_id, coalesce(o.custom_title, o.title) `title`, t.iconCls from objects o join templates t on o.template_id = t.id where o.id = $1';
			$res = mysqli_query_params($sql, $p['object_id']) or die(mysqli_query_error());
			if($r = $res->fetch_assoc()) $obj = $r;
			$res->close();
		}else $p['object_id'] = null;
		// get task data
		if(!empty($p['task_id'])){
			$res = mysqli_query_params('select title from tasks where id = $1', $p['task_id']) or die(mysqli_query_error());
			if($r = $res->fetch_assoc()) $task = $r;
			$res->close();
		}else $p['task_id'] = null;
		// get file data
		$file = array();
		if(!empty($p['file_id'])){
			$res = mysqli_query_params('select id, name from files where id = $1', $p['file_id']) or die(mysqli_query_error());
			if($r = $res->fetch_assoc()){
				$file = $r;
				$t = explode('.', $file['name']);
				$file['a'] = ' <i class="file-unknown file-'.array_pop($t).'" id="'.$file['id'].'" id2 ="'.$p['case_id'].'">'.$file['name'].'</i>';
			}
			$res->close();
		}else $p['file_id'] = null;
		// get destination usernames if "to_user_ids" is present in params
		$to_user_names_data = array();
		if(!empty($p['to_user_ids'])){
			if(!is_array($p['to_user_ids'])) $to_user_ids = explode(',', $p['to_user_ids']);
			$to_user_ids = array_filter($to_user_ids, 'is_numeric');
		}
		if(!empty($to_user_ids)){
			$p['to_user_ids'] = implode(',',$to_user_ids);
			$res = mysqli_query_params('select name, '.$_SESSION['languages']['string'].', sex from users_groups where id in ('.$p['to_user_ids'].')') or die(mysqli_query_error());
			while($r = $res->fetch_assoc()) $to_user_names_data[] = $r;
			$res->close();
		}
		
		// get office name if "office_id" is present in params
		$office_name = '';
		if(!empty($p['office_id'])){
			$res = mysqli_query_params('select name from tags where id = $1', $p['office_id']) or die(mysqli_query_error());
			if($r = $res->fetch_row()) $office_name = $r[0];
			$res->close();
		}
		
		
		$u = &$_SESSION['user'];
		//create the htmls for each language
		$template_types_translation_names = array('', 'Object', 'IncomingAction', 'OutgoingAction', 'User', 'Contact', 'Organization' );

		initTranslations();
		$fields = array('id', 'pid', 'user_id', 'to_user_ids', 'office_id', 'case_id', 'object_id', 'file_id', 'task_id', 'date', 'action_type', 'remind_users', 'result', 'info');
		if(!empty($_SESSION['languages']['per_id']))
		foreach($_SESSION['languages']['per_id'] as $lk => $lv){
			$l = 'l'.$lk;
			$fields[] = $l;
			
			@$case['a'] = ' <i class="case" id="'.$p['case_id'].'">'.(!empty($case_data['name']) ? $case_data['name'] : ((!empty($case_data['nr'])) ? L('Nr', $l).' '.$case_data['nr'] : 'id: '.$case_data['id']) ).'</i>';
			
			@$obj['a'] = ' <i class="obj'.(empty($obj['iconCls']) ? '' : ' '.$obj['iconCls']).'" id="'.$obj['id'].'">'.$obj['title'].'</i>';
			@$obj['type'] = L($template_types_translation_names[$obj['type_id']], $l); 
			
			@$task['a'] = ' "<i class="task">'.$task['title'].'</i>"';
			
			$username = empty($u[$l]) ? $u['name'] : $u[$l];
			$to_user_names = array();
			if(!empty($to_user_names_data)){
				foreach($to_user_names_data as $tu) $to_user_names[] = '<i class="icon-user-'.$tu['sex'].'">'.(empty($tu[$l]) ? $tu['name'] : $tu[$l]).'</i>';
				$to_user_names = implode(', ', $to_user_names);
			}/**/
			
			@$sex = $u['sex'];
			$p[$l] = '<i class="icon-user-'.$sex.'">'.$username.'</i> ';
			switch($p['action_type']){ //- log actions
				case 1: //Login
					$p[$l] .= Log::getGenderString($sex, 'LoggedOn', $l);
					break;
				case 2: // Logout
					$p[$l] .= Log::getGenderString($sex, 'LoggedOut', $l).' '.L('fromTheSystem', $l);
					break;
				case 3: // Add case
					$p[$l] .= Log::getGenderString($sex, 'Added', $l).' '.L('theCase', $l).$case['a']; //(($sex == 'f') ? 'добавила' : 'добавил').' дело '.$case['a'];
					break;
				case 4: // update case
					$p[$l] .= Log::getGenderString($sex, 'Updated', $l).' '.L('theCase', $l).$case['a'];
					break;
				case 5: // delete case
					$p[$l] .= Log::getGenderString($sex, 'Deleted', $l).' '.L('case', $l).$case['a'];
					break;
				case 6: // open case
					$p[$l] .= Log::getGenderString($sex, 'Opened', $l).' '.L('theCase', $l).$case['a'];
					break;
				case 7: // close case
					$p[$l] .= Log::getGenderString($sex, 'Closed', $l).' '.L('theCase', $l).$case['a'];
					break;
				case 8: // add case object
					$p[$l] .= Log::getGenderString($sex, 'Added', $l).' '.$obj['type'].$obj['a'].' '.L('toCase', $l).$case['a'];
					break;
				case 9: // update case object
					$p[$l] .= Log::getGenderString($sex, 'Updated', $l).' '.$obj['type'].$obj['a'].' '.L('inCase', $l).$case['a'];
					break;
				case 10: // delete case object
					$p[$l] .= Log::getGenderString($sex, 'Deleted', $l).' '.$obj['type'].$obj['a'].' '.L('fromCase', $l).$case['a'];
					break;
				case 11: // open case object
					$p[$l] .= Log::getGenderString($sex, 'Opened', $l).' '.$obj['type'].$obj['a'].' '.L('ofCase', $l).$case['a'];
					break;
				case 12: // get case objects info
					$p[$l] .= Log::getGenderString($sex, 'Viewed', $l).' '.$obj['type'].$obj['a'].' '.L('ofCase', $l).$case['a'];
					break;
				case 13: // add case file
					$p[$l] .= Log::getGenderString($sex, 'Added', $l).' '.L('file', $l).$file['a'].' ';
					if(empty($p['object_id'])) $p[$l] .= L('toCase', $l).$case['a']; else $p[$l] .= L('ofCase', $l).$obj['a'].' '.L('ofCase', $l).$case['a'];
					break;
				case 14: // download case file
					$p[$l] .= Log::getGenderString($sex, 'Downloaded', $l).' '.L('theFile', $l).$file['a'].' '.L('ofCase', $l).$case['a'];
					break;
				case 15: // delete case file	
					$p[$l] .= Log::getGenderString($sex, 'Deleted', $l).' '.L('file', $l).$file['a'].' '.L('fromCase', $l).$case['a'];
					break;
				case 16: // update case access
					$p[$l] .= Log::getGenderString($sex, 'Updated', $l).' '.L('securityRights', $l).' '.L('forCase', $l).$case['a'];
						//(empty($p['to_user_id']) ? '' : ', адвокату <a class="icon-user-lawyer" href="">'.$to_user_name.'</a>');/**/
					break;
				/*case 17: // add access to case
					/*$p[$l] .= Log::getGenderString($sex, 'Added', $l).(($sex == 'f') ? 'передала' : 'передал').' дело "'.$case['a'].'" в оффис "'.$office_name.'"'.
						(empty($p['to_user_id']) ? '' : ', адвокату <a class="icon-user-lawyer" href="">'.$to_user_name.'</a>');/**/
				/*	break;
				case 18: // remove access from case
					/*$p[$l] .= Log::getGenderString($sex, 'Added', $l).(($sex == 'f') ? 'передала' : 'передал').' дело "'.$case['a'].'" в оффис "'.$office_name.'"'.
						(empty($p['to_user_id']) ? '' : ', адвокату <a class="icon-user-lawyer" href="">'.$to_user_name.'</a>');/**/
				/*	break;
				case 19: // grant access to case
					$p[$l] .= Log::getGenderString($sex, 'Added', $l).(($sex == 'f') ? 'открыла' : 'открыл').' доступ пользователю <a class="icon-user" href="">'.$to_user_name.'</a> к делу '.$case['a'];
					break;
				case 20: // close access to case
					$p[$l] .= Log::getGenderString($sex, 'Added', $l).(($sex == 'f') ? 'закрыла' : 'закрыл').' доступ пользователю <a class="icon-user" href="">'.$to_user_name.'</a> к делу '.$case['a'];
					break;/* */
				case 21: // add task
					$p[$l] .= Log::getGenderString($sex, 'Added', $l).' '.L('task', $l).$task['a'].' '.( $obj['id'] ? L('for', $l).' '.$obj['type'].$obj['a'] : '').( empty($p['case_id']) ? '' :' '.L('ofCase', $l).$case['a']);
					if(!empty($to_user_ids) && ($p['to_user_ids'] != $p['user_id'])) $p[$l] .= ' '.( (sizeof($to_user_ids) > 1) ? L('forUsers', $l) : L('forUser', $l) ).' '.$to_user_names;
					break;
				case 22: // update task
					$p[$l] .= Log::getGenderString($sex, 'Updated', $l).' '.L('theTask', $l).' '.$task['a'].' '.( $obj['id'] ? L('for', $l).' '.$obj['type'].$obj['a'] : '').( empty($p['case_id']) ? '' : ' '.L('ofCase', $l).$case['a']);
					if(!empty($to_user_ids) && ($p['to_user_ids'] != $p['user_id'])) $p[$l] .= ' '.( (sizeof($to_user_ids) > 1) ? L('forUsers', $l) : L('forUser', $l) ).' '.$to_user_names;
					break;
				case 23: // complete task by a user
					$p[$l] .= Log::getGenderString($sex, 'Completed', $l).' '.L('theTask', $l).$task['a'].' '.( $obj['id'] ? L('for', $l).' '.$obj['type'].$obj['a'] : '').( empty($p['case_id']) ? '' : ' '.L('ofCase', $l).$case['a']).
					($p['autoclosed'] ? ' '.L('and',$l).' '.L('theTask',$l).' '.L('hasBeenAutoclosed',$l): '');
					//if(!empty($p['to_user_id']) && ($p['to_user_id'] != $p['user_id'])) $p[$l] .= ' пользователя <a class="icon-user" href="">'.$to_user_name.'</a>';
					break;
				case 24: // remove task
					$p[$l] .= Log::getGenderString($sex, 'Deleted', $l).' '.L('theTask', $l).$task['a'].' '.( $obj['id'] ? L('from', $l).' '.$obj['type'].$obj['a'] : '').( empty($p['case_id']) ? '' : ' '.L('ofCase', $l).$case['a']);
					//if(!empty($p['to_user_id']) && ($p['to_user_id'] != $p['user_id'])) $p[$l] .= ' пользователя <a class="icon-user" href="">'.$to_user_name.'</a>';
					break;
				case 25: // update notifications
					$p[$l] .= Log::getGenderString($sex, 'Updated', $l).' '.L('remindersForTask', $l).$task['a'].' '.( $obj['id'] ? L('from', $l).' '.$obj['type'].$obj['a'] : '').( empty($p['case_id']) ? '' : ' '.L('ofCase', $l).$case['a']);
					//if(!empty($p['to_user_id']) && ($p['to_user_id'] != $p['user_id'])) $p[$l] .= ' пользователя <a class="icon-user" href="">'.$to_user_name.'</a>';
					break;
				case 26: // change user status for task
					$p[$l] .= Log::getGenderString($sex, 'Changed', $l).' '.L('theStatus', $l).' '.L('forUser', $l).' '.$to_user_names.' '.L('in', $l).' '.L('theTask', $l).$task['a'].' '.( $obj['id'] ? L('for', $l).' '.$obj['type'].$obj['a'] : '').( empty($p['case_id']) ? '' : ' '.L('ofCase', $l).$case['a']).
					($p['autoclosed'] ? ' '.L('and',$l).' '.L('theTask',$l).' '.L('hasBeenAutoclosed',$l): '');
					//if(!empty($p['to_user_id']) && ($p['to_user_id'] != $p['user_id'])) $p[$l] .= ' пользователя <a class="icon-user" href="">'.$to_user_name.'</a>';
					break;
				case 27: // close task
					$p[$l] .= Log::getGenderString($sex, 'Closed', $l).' '.L('theTask', $l).$task['a'].' '.( $obj['id'] ? L('for', $l).' '.$obj['type'].$obj['a'] : '').( empty($p['case_id']) ? '' : ' '.L('ofCase', $l).$case['a']);
					//if(!empty($p['to_user_id']) && ($p['to_user_id'] != $p['user_id'])) $p[$l] .= ' пользователя <a class="icon-user" href="">'.$to_user_name.'</a>';
					break;
				/*case 28: // autoclose task
					$p[$l] .= Log::getGenderString($sex, 'Closed', $l).' '.L('task', $l).$task['a'].' '.( $obj['id'] ? L('for', $l).' '.$obj['type'].$obj['a'] : '').( empty($p['case_id']) ? '' : ' '.L('ofCase', $l).$case['a']);
					//if(!empty($p['to_user_id']) && ($p['to_user_id'] != $p['user_id'])) $p[$l] .= ' пользователя <a class="icon-user" href="">'.$to_user_name.'</a>';
					break;/**/
			}
		}
		/* setting remind_users field /**/
		if(isset($p['remind_users'])){
			$p['remind_users'] = toNumericArray($p['remind_users']);
			$p['remind_users'] = array_diff($p['remind_users'], array($_SESSION['user']['id'])); //do not remind the user that have made changes
			if(empty($p['remind_users'])) unset($p['remind_users']); 
			else $p['remind_users'] = implode(',', $p['remind_users']);
		}/**/
		$i = 1;
		$fn = array();
		$fv = Array();
		$ufv = Array();
		$values = array();
		foreach($p as $k => $v)
			if(in_array($k, $fields)){
				$fn[] = $k;
				$fv[] = '$'.$i;
				$ufv[] = $k.' = $'.$i;
				$values[] = $p[$k];
				$i++;
			}
	
		$sql = 'INSERT INTO actions_log ('.implode(',', $fn).') VALUES ('.implode(',', $fv).') on duplicate key UPDATE '.implode(',', $ufv);
		mysqli_query_params($sql, $values) or die(mysqli_query_error());
		if(!empty($p['remind_users'])) Log::addNotifications($p);

		return last_insert_id();
	}	
	
	private static function getGenderString($sex, $property, $language = false){
		/* this function return translation for specified property with prefixes "he" or "she" from global translation variable L */
		$prefix = ($sex == 'f') ? 'she' : 'he';
		$property = $prefix.ucfirst($property);
		return L($property, $language);
	}
	
	private static function addNotifications(&$p){
		/*$p:;
		array(12) {
		["action_type"]=> 21
		["case_id"]=>2
		["object_id"]=>NULL
		["task_id"]=>52
		["to_user_ids"]=>'1,4'
		["remind_users"]=>'4'
		["removed_users"]=>'4'
		["info"]=>'title: test3'
		["user_id"]=>'1'
		["file_id"]=>NULL
		["l1"]=>'<i class=\"icon-user-m\">Vitalie Ţurcanu</i> added task \"<i class=\"task\">test3</i>\"  to case <i class=\"case\" id=\"2\">A test case</i> for users <i class=\"icon-user-m\">Vitalie Ţurcanu</i>, <i class=\"icon-user-m\">Dmitry Kazakov</i>'
		["l2"]=>'<i class=\"icon-user-m\">Vitalie Ţurcanu</i> a ajouté tâche \"<i class=\"task\">test3</i>\"  en cas <i class=\"case\" id=\"2\">A test case</i> pour les utilisateurs <i class=\"icon-user-m\">Vitalie Ţurcanu</i>, <i class=\"icon-user-m\">Dmitry Kazakov</i>'
		["l3"]=>'<i class=\"icon-user-m\">Виталий Цуркану</i> добавил задание \"<i class=\"task\">test3</i>\"  к делу <i class=\"case\" id=\"2\">A test case</i> для пользователей <i class=\"icon-user-m\">Виталий Цуркану</i>, <i class=\"icon-user-m\">Дмитрий Казаков</i>'
		}
	/**/	
		$to_user_ids = array();
		if(!empty($p['remind_users'])) $to_user_ids = toNumericArray($p['remind_users']); 
		if(empty($to_user_ids)) return ;
		
		$users_data = array();
		$res = mysqli_query_params('select id, language_id from users_groups where id in ('.implode(',',$to_user_ids).')') or die(mysqli_query_error());
		while($r = $res->fetch_assoc()) $users_data[] = $r;
		$res->close();
		foreach($users_data as $u){
			$l = 'l'.$u['language_id'];//$_SESSION['languages']['per_id'][$u['language_id']]['abreviation'];
			if(!$l) $l = $GLOBALS['CB_LANGUAGE'];
			$subject = L('CaseBoxNotification', $l).' ';
			$message = (empty($p[$l]) ? '' : '<p>'.$p[$l].'.</p>'); //log message
			switch($p['action_type']){
				case 3: // Add case (current info)
					$subject .= L('aboutNewCase', $l);
					break;
				case 4: // update case (current info)
					$subject .= L('aboutCaseUpdate', $l);
					break;
				case 7: // close case (current info)
					$subject .= L('aboutCaseClose', $l);
					break;
				case 21: // add task (current info)+
				case 22: // update task (current info)+
				case 23: // complete task (current info)+
				case 24: // remove task (current info)+
				case 26: // change user status for task 
				case 27: // close task (current info)
				case 28: // task overdue
				case 29: // aboutTaskCompletionDecline
				case 30: // aboutTaskCompletionOnBehalt
				case 31: // aboutTaskReopened
					 switch($p['action_type']){
					 	case 21: $subject = L('aboutTaskCreated', $l); break; //CHECKED
					 	case 22: $subject = L('aboutTaskUpdated', $l); break; //CHECKED
					 	case 23: $subject = L('aboutTaskComplete', $l); break; //CHECKED
					 	case 24: $subject = L('aboutTaskDelete', $l); break; // TO BE REWIEWED
					 	case 26: $subject = L('aboutUserTaskStatusChange', $l); break; // depricated
					 	case 27: $subject = L('aboutTaskComplete', $l); break;//aboutTaskClose //CHECKED
					 	case 28: $subject = L('aboutTaskOverdue', $l); break;
					 	case 29: $subject = L('aboutTaskCompletionDecline', $l); break; //CHECKED
					 	case 30: $subject = L('aboutTaskCompletionOnBehalt', $l); break; //CHECKED
					 	case 31: $subject = L('aboutTaskReopened', $l); break; //CHECKED
					 }
					$sql = 'select t.name, f_get_tree_path(t.id) `path`, u.'.$l.' `owner`, u.name `username` from tree t join users_groups u on t.cid = u.id where t.id = $1';
					$res = mysqli_query_params($sql, $p['task_id']) or die(mysqli_query_error());
					if($r = $res->fetch_assoc())
						$subject = str_replace( array('{owner}', '{name}', '{path}'), array(coalesce($r['owner'], $r['username']), $r['name'], $r['path'] ), $subject );
					$res->close();
					$message = Tasks::getTaskInfoForEmail($p['task_id'], $u['id'], @$p["removed_users"]/*, $message/**/);
					
					/*aboutTaskCreated		- New task / {owner}: {title} ({$path})
					aboutTaskUpdated		- Task update / {title} ({$path})
					aboutTaskComplete 		- Task completed / {title} ({$path})
					
					aboutTaskOverdue		- Task overdue / {title} ({$path})
					aboutTaskCompletionDecline	- Task completion declined / {owner}: {title} ({$path})
					aboutTaskCompletionOnBehalt	- On behalf task completion / {owner}: {title} ({$path})
					aboutTaskReopened		- Task reopened / {owner}: {title} ({$path})/**/
					break;
				}
			$p['case_id'] = is_numeric($p['case_id']) ? $p['case_id'] : null;
			$p['object_id'] = is_numeric($p['object_id']) ? $p['object_id'] : null;
			$p['task_id'] = is_numeric($p['task_id']) ? $p['task_id'] : null;
			mysqli_query_params('INSERT INTO notifications (action_type, case_id, object_id, task_id, subtype, subject, message, time, user_id) VALUES ($1, $2, $3, $4, 0, $5, $6, CURRENT_TIMESTAMP, $7)', 
							array($p['action_type'], $p['case_id'], $p['object_id'], $p['task_id'], $subject, $message, $u['id'])) or die(mysqli_query_error());
		}
	}
}
?>