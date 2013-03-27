<?php

class Tasks{

	function load($id){ //loading task data
		$rez = array('success' => false);
		$sql = 'select id, case_id, object_id, `title`, date_start, `date_end`, missed, `type`, privacy, responsible_party_id, responsible_user_ids, autoclose, description, parent_ids, child_ids'
			.',DATEDIFF(`date_end`, UTC_DATE()) `days`'
			.',(select pid from tree where id = $1) pid'
			.',(select reminds from tasks_reminders where task_id = $1 and user_id = $2) reminds'
			.',(select name from cases where id = case_id) `case`'
			.',(select concat(coalesce(concat(date_format(date_start, \''.$_SESSION['user']['short_date_format'].'\'), \' - \'), \'\'), coalesce(custom_title, title)) from objects where id = object_id) object'
			.',status, cid, completed, cdate '
			.',has_deadline, importance, category_id, allday '
			.',(select f_get_tree_ids_path(pid) from tree where id = t.id) `path` '
			.',f_get_tree_path(id) `pathtext` '
			.'from tasks t where id = $1';
		$res = mysqli_query_params($sql, array($id, $_SESSION['user']['id'])) or die(mysqli_query_error());
		if($r = $res->fetch_assoc()){
			$this->getTaskStyles($r);
			$r['days'] = formatLeftDays($r['days']);
			$r['date_start'] = date_mysql_to_iso($r['date_start']);
			$r['date_end'] = date_mysql_to_iso($r['date_end']);
			$r['cdate'] = date_mysql_to_iso($r['cdate']);
			$r['completed'] = date_mysql_to_iso($r['completed']);
			$c = explode('/', $r['path']);
			$r['create_in'] = array_pop($c);
			$rez = array('success' => true, 'data' => $r);
		}else throw new Exception(L\Object_not_found);
		$res->close();
		
		$res = mysqli_query_params('select id, name from tree where pid = $1 and `type` = 5 order by `name`', $id) or die(mysqli_query_error());
		while($r = $res->fetch_assoc()) $rez['data']['files'][] = $r;
		$res->close();

		$res = mysqli_query_params('select u.id, ru.status, ru.thesauri_response_id, ru.`time` from tasks_responsible_users ru join users_groups u on ru.user_id = u.id where ru.task_id = $1 order by u.l'.UL_ID(), $id) or die(mysqli_query_error());
		while($r = $res->fetch_assoc()){
			$rez['data']['users'][] = $r;
			if($r['id'] == $_SESSION['user']['id']) $rez['data']['user'] = $r;
		}
		$res->close();
		
		$rez['data']['admin'] = (Security::isAdmin() || ($rez['data']['cid'] == $_SESSION['user']['id']));
		$rez['data']['type'] = intval($rez['data']['type']);
		$rez['data']['privacy'] = intval($rez['data']['privacy']);
		return $rez;
	}
	
	function getCaseTasks($p){ // TO REVIEW
		if(empty($p->case_id) || !is_numeric($p->case_id)) throw new Exception(L\Wrong_id);
		$rez = array('success' => true, 'data' => array());
		$sql = 'select id, object_id, `title`, `date_end`, `type`, privacy, responsible_party_id, responsible_user_ids, autoclose, description, parent_ids, child_ids'.
			',(select reminds from tasks_reminders where task_id = t.id and user_id = $2) reminds'.
			',status, cid, completed, cdate from tasks t where case_id = $1';
		$res = mysqli_query_params($sql, array($p->case_id, $_SESSION['user']['id'])) or die(mysqli_query_error());
		while($r = $res->fetch_assoc()){
			$this->getTaskStyles($r);
			$rez['data'][] = $r;
		}
		$res->close();
		return $rez;
	}
	
	function save($p){
		require_once 'Cases.php';
		if(!isset($p['id'])) $p['id'] = null;
		if(!isset($p['pid'])) $p['pid'] = null;
		$p['type'] = intval($p['type']);
		try{
			$p['case_id'] = Cases::getId($p['pid']);
		} catch (Exception $e) {
		}
		if(empty($p['case_id'])) $p['case_id'] = null;
		
		$log_action_type = 25; //suppose that only notifications are changed
		if(!isset($p['id'])) $p['id'] = null;
		if( !validId($p['id']) || Security::canManageTask($p['id']) ){
			/* update the task details only if is admin or owner of the task /**/

			/* getting available user ids to assign task to for verification /**/
			/*$u = Security::getCaseLowerLevelUsers($p); //TO REVIEW
			$user_ids = Array();
			foreach($u['data'] as $user) array_push($user_ids, $user['id']);
			if(!in_array($p['user_id'], $user_ids)) throw new Exception(L\Wrong_user_assigned);
			/* end of getting available user ids to assign task to for verification /**/
			
			$log_action_type = 21;// suppose adding new task
			if(is_numeric($p['create_in'])) $p['pid'] = $p['create_in'];
			if(!is_numeric($p['pid'])) $p['pid'] = null;
			
			$p['date_start'] = empty($p['date_start']) ? null : date_iso_to_mysql($p['date_start']); 
			if(!isset($p['has_deadline'])) $p['has_deadline'] = 0;
			if(!isset($p['allday'])) $p['allday'] = 0;

			if( ($p['has_deadline'] == 1) || ($p['type'] == 7) ){
				$p['date_end'] = empty($p['date_end']) ? null : date_iso_to_mysql($p['date_end']); 
			}else $p['date_end'] = null;

			if(empty($p['time'])) $p['time'] = null;//'00:00';
			
			/* estimating deadline status in dependance with parent tasks statuses */
			if( ($p['type'] == 6) && !empty($p['parent_ids'])){
				$p['parent_ids'] = explode(',', $p['parent_ids']);
				$p['parent_ids'] = array_filter($p['parent_ids'], 'is_numeric');
				$p['parent_ids'] = implode(',', $p['parent_ids']);
			}else $p['parent_ids'] = null;
			$status = 4;//pending
			if(empty($p['parent_ids'])){
				$status = 2;//active
				/* if it's overdue - mysql trigger will change the status */
			}else{
				$res = mysqli_query_params('SELECT COUNT(id), sum(status) FROM tasks where id in ('.$p['parent_ids'].')') or die(mysqli_query_error());
				if(($r = $res->fetch_row()) && ($r[0]*2 == $r[1])) $status = 2; //all parent tasks are completed
				$res->close();
			}
			/* end of estimating deadline status in dependance with parent tasks statuses */
			if(empty($p['id'])){
				$res = mysqli_query_params('insert into tree (pid, name, `type`, cid, uid) values ($1, $2, $3, $4, $4)', array($p['pid'], $p['title'], $p['type'], $_SESSION['user']['id'])) or die(mysqli_query_error());
				$p['id'] = last_insert_id();
			}else{
				//mysqli_query_params('delete from tasks_dependance where task_id = $1', $p['id']) or die(mysqli_query_error());
				$log_action_type = 22; // updating task
			}
			
			if(!isset($p['autoclose'])) $p['autoclose'] = 1;

			$sql = 'INSERT INTO tasks (id, case_id, object_id, `title`, `date_start`, `date_end`, `time`, `type`, `privacy`, responsible_party_id, responsible_user_ids, description, '.
				'parent_ids, reminds, cid, status, autoclose, has_deadline, importance, category_id, allday, uid, udate)
				VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17, $18, $19, $20, $21, null, null) '.
				'ON DUPLICATE KEY UPDATE `object_id`=$3, `title` = $4, `date_start` = $5, `date_end` = $6, `time` = $7, `type` = $8, `privacy` = $9'.
				', responsible_party_id = $10, responsible_user_ids = $11, description = $12, parent_ids = $13, reminds = $14, uid = $15, udate = CURRENT_TIMESTAMP, status = case status when 2 then 2 else $16 end, autoclose = $17'.
				', has_deadline = $18, importance = $19, category_id = $20, allday = $21 ';
			mysqli_query_params($sql, @Array(
				$p['id']
				,$p['case_id']
				,null
				,$p['title']
				,$p['date_start']
				,$p['date_end']
				,$p['time']
				,$p['type']
				,intval($p['privacy'])
				,$p['responsible_party_id']
				,$p['responsible_user_ids']
				,$p['description']
				,$p['parent_ids']
				,$p['reminds']
				,$_SESSION['user']['id']
				,$status
				,$p['autoclose']
				,$p['has_deadline']
				,$p['importance']
				,$p['category_id']
				,$p['allday']
				)) or die(mysqli_query_error());

			/* saving parent task ids into the additional table (tasks_dependance)*/
			// $pt = explode(',', $p['parent_ids']);
			// $pt = array_filter($pt, 'is_numeric');
			// if(!empty($pt))
			// mysqli_query_params('insert into tasks_dependance (task_id, parent_task_id) select $1, id from tasks where id in (0'.implode(',', $pt).')', $p['id']) or die(mysqli_query_error());
			/* end of saving parent task ids into the additional table (tasks_dependance)*/
			
			/*storing specified files*/
			require_once 'Files.php';
			$files = new Files();
			$params = array(
				'pid' => $p['id']
				,'date' => $p['date_start']
				,'responce' => 'newversion'
				,'files' => &$_FILES
			);
			$files->storeFiles($params);
			unset($files);
			/*end of specified files*/
			Cases::updateCaseUpdateInfo($p['id']);

		}
		$remind_users = null;
		if(($log_action_type == 21) || ($log_action_type == 22)) $remind_users = $p['responsible_user_ids'];
		$logParams = Array('action_type' => $log_action_type, 'case_id' => $p['case_id'], /*'object_id' => $p['object_id'], /**/'task_id' => $p['id'], 'to_user_ids' => $p['responsible_user_ids'], 'remind_users' => $remind_users, 'info' => 'title: '.$p['title']);
		Log::add($logParams);

		$this->saveReminds($p, $log_action_type = 25);
		SolrClient::runCron();
		//exec('php ../../casebox/crons/cron_solr_update_objects.php');
		$rez = $this->load($p['id']);
		$rez['logParams'] = &$logParams;
		return $rez;
	}
	
	function saveReminds($p, $log_action_type = 25){
		$p = (array)$p;
		require_once 'Cases.php';
		$case_name = '';
		try {
			$p['case_id'] = @Cases::getId($p['pid']);
			/* check if current user can read task */
			if( !validId($p['case_id']) || !Security::canReadCase($p['case_id']) ) throw new Exception(L\Access_denied);
			$case_name = Cases::getName($p['case_id']);
			/* save reminds for currents user /**/
		} catch (Exception $e) {
		}
		mysqli_query_params('insert into tasks_reminders (task_id, user_id, reminds) values ($1, $2, $3) on duplicate key update reminds = $3', array($p['id'], $_SESSION['user']['id'], $p['reminds'])) or die(mysqli_query_error());
		/* end of save reminds for currents user /**/

		/* create notifications for specified reminders */
		/* if no deadline is set for the task then no notifications will be set */
		$deadline = false;
		if(!empty($p['date_end'])) $deadline = $p['date_end'];
		else{
			$res = mysqli_query_params('select date_end from tasks where id = $1', $p['id']) or die(mysqli_query_error());
			if($r = $res->fetch_row()) $deadline = $r[0];
			$res->close();
		}
		if(!empty($deadline)){
			//selecting currently used notification ids to be updated with new data
			$ids = array();
			$res = mysqli_query_params('select id from notifications where task_id = $1 and user_id = $2 and subtype = 1', array($p['id'], $_SESSION['user']['id'])) or die(mysqli_query_error());
			while($r = $res->fetch_row()) $ids[] = $r[0];
			$res->close();
			//end of selecting currently used notification ids to be updated with new data
			
			$a = explode('-', $p['reminds']);
			$subject = L\Notification_for_task.' "'.$p['title'].'"';
			$message = str_replace(array('{task_title}', '{case_name}'), array($p['title'], $case_name), L\Notification_for_task);
			foreach($a as $r){
				$rem = explode('|', $r);	// user|remindType|remind delay|remindUnits
				if($rem[0] != 1) continue; // not by mail
				$id = empty($ids) ? null : array_shift($ids);
				$unit = 'HOUR';
				switch($rem[2]){
					case 1: $unit = 'MINUTE'; break;
					case 2: $unit = 'HOUR'; break;
					case 3: $unit = 'DAY'; break;
					case 4: $unit = 'WEEK'; break;
				}
				mysqli_query_params('INSERT INTO notifications (id, action_type, case_id, object_id, task_id, subtype, subject, message, time, user_id) VALUES ($1, $2, $3, $4, $5, 1, $6, $7, DATE_ADD($8, INTERVAL $9 '.$unit.'), $10)'.
								' on duplicate key update action_type = $2, case_id = $3, object_id = $4, task_id = $5, subtype = 1, subject = $6, message = $7, time = DATE_ADD($8, INTERVAL $9 '.$unit.'), user_id = $10', 
								array($id, $log_action_type, $p['case_id'], null/*$p['object_id']/**/, $p['id'], $subject, $message, $deadline, -$rem[1], $_SESSION['user']['id'])) or die(mysqli_query_error());
			}
			if(!empty($ids)) mysqli_query_params('delete from notifications where task_id = $1 and id in (0'.implode(',', $ids).')', $p['id']) or die(mysqli_query_error());
		}else{
			mysqli_query_params('delete from notifications where task_id = $1 and user_id = $2 and subtype = 1', array($p['id'], $_SESSION['user']['id']) ) or die(mysqli_query_error());
			//$p['reminds'] = '';
		}
		return array('success' => true, 'reminds' => $p['reminds']);
	}
	
	function setUserStatus($p){
		$rez = array('success' => true, 'id' => $p->id);
		$task = array();
		$res = mysqli_query_params('select responsible_user_ids, autoclose, title, status, autoclose, cid from tasks where id = $1', $p->id) or die(mysqli_query_error());
		if($r = $res->fetch_assoc()) $task  = $r;
		$res->close();
		//if($task[status] != 1) throw new exception(L\TaskNotActive);
		$responsible_users = explode(',', $task['responsible_user_ids']);
		if( ($_SESSION['user']['id'] != $task['cid']) && !Security::isAdmin() ) throw new Exception(L\Access_denied);
		if( !in_array($p->user_id, $responsible_users) ) throw new Exception(L\Wrong_id);
		if(empty($p->status)) $p->status = 0;
		@mysqli_query_params('insert into tasks_responsible_users (task_id, user_id, status, thesauri_response_id, `time`) values($1, $2, $3, $4, current_timestamp) on duplicate key update status = $3, thesauri_response_id = $4, `time` = current_timestamp', 
			array($p->id, $p->user_id, $p->status, $p->thesauri_response_id)
		) or die(mysqli_query_error());
		$autoclosed = false;
		$action_type = 29; //aboutTaskCompletionDecline
		if( $p->status == 1){
			$action_type = 30; //aboutTaskCompletionOnBehalt
			$autoclosed = $this->checkAutocloseTask($p->id);
		}
		Log::add(Array('action_type' => $action_type, 'task_id' => $p->id, 'to_user_ids' => $p->user_id, 'remind_users' => $task['cid'].','.$p->user_id, 'autoclosed' => $autoclosed, 'info' => 'title: '.$task['title'])); // TO REVIEW

		Cases::updateCaseUpdateInfo($p->id);
		
		SolrClient::runCron();
		//exec('php ../../casebox/crons/cron_solr_update_objects.php'); //??
		return $rez;
	}/**/
	
	function checkAutocloseTask($id){
		$rez = false;
		/* suppose that task is autoclose = 1 and dont check this for now*/
		$res = mysqli_query_params('select user_id from tasks_responsible_users where task_id = $1 and status = 0', $id) or die(mysqli_query_error());
		if($r = $res->fetch_row()){
			//there are unset user statuses, so nothing to change for this task
		}else{
			mysqli_query_params('update tasks set completed = CURRENT_TIMESTAMP where id = $1', $id) or die(mysqli_query_error());
			$res = mysqli_query_params('select title, autoclose, cid, responsible_user_ids from tasks where id = $1', $id) or die(mysqli_query_error());
			if(($r = $res->fetch_assoc()) && ($r['autoclose'] == 1)){
				$res->close();
				mysqli_query_params('update tasks set status = 3 where id = $1 and status <> 3 and autoclose = 1', $id) or die(mysqli_query_error());
				$this->updateChildTasks($id);
				$rez = true;
			}else $res->close();
		}
		return $rez;
	}
	
	function complete($p){
		/*$case_id = null;
		$object_id = null;
		$status = null;/**/
		$task = array();
		$res = mysqli_query_params('select t.case_id, t.object_id, t.status, ru.status `user_status`, t.cid, t.responsible_user_ids, t.title from tasks t join tasks_responsible_users ru on t.id = ru.task_id where t.id = $1 and ru.user_id = $2', array($p->id, $_SESSION['user']['id'])) or die(mysqli_query_error());
		if($r = $res->fetch_assoc()){
			if($r['user_status'] == 1) throw new Exception(L\Task_already_completed);
			$task = $r;
			/*$case_id = $r['case_id'];
			$object_id = $r['object_id'];
			$status = $r['status'];/**/
		}else if(!Security::isAdmin()) throw new Exception(L\Access_denied);
		$res->close();
		mysqli_query_params('update tasks_responsible_users set status = 1, `time` = current_timestamp where task_id = $1 and user_id = $2', array($p->id, $_SESSION['user']['id'])) or die(mysqli_query_error());
		mysqli_query_params('insert into messages (cid, nid, message) values($1, $2, $3)', 
			array($_SESSION['user']['id'], coalesce($task['case_id'], $task['object_id'], $p->id), $p->message)) or die(mysqli_query_error());
		
		Log::add(Array('action_type' => 23, 'task_id' => $p->id, /*'to_user_ids' => $task['responsible_user_ids'], /**/'remind_users' => $task['cid']/*.','.$task['responsible_user_ids']/**/, 'autoclosed' => $this->checkAutocloseTask($p->id), 'info' => 'title: '.$task['title']));

		Cases::updateCaseUpdateInfo($p->id);

		SolrClient::runCron();
		//exec('php ../../casebox/crons/cron_solr_update_objects.php'); 
		return array('success' => true);
	}
	
	function close($id){
		$task = array();
		$res = mysqli_query_params('select cid, title, responsible_user_ids from tasks where id = $1', $id) or die(mysqli_query_error());
		if($r = $res->fetch_assoc()) $task  = $r;
		$res->close();
		if( ($_SESSION['user']['id'] !== $task['cid']) && !Security::isAdmin() ) return  array('success' => false, 'msg' => L\No_access_to_close_task);
		mysqli_query_params('update tasks set status = 3 where id = $1', $id) or die(mysqli_query_error());
		/* log and notify all users about task closing */
		Log::add(Array('action_type' => 27, 'task_id' => $id, /*'to_user_ids' => $task['responsible_user_ids'],/**/ 'remind_users' => $task['cid'].','.$task['responsible_user_ids'], 'info' => 'title: '.$task['title']));
		$this->updateChildTasks($id);

		Cases::updateCaseUpdateInfo($id);

		SolrClient::runCron();
		//exec('php ../../casebox/crons/cron_solr_update_objects.php'); 
		return array('success' => true, 'id' => $id);
	}

	function reopen($id){
		$task = array();
		$res = mysqli_query_params('select cid, title, responsible_user_ids from tasks where id = $1', $id) or die(mysqli_query_error());
		if($r = $res->fetch_assoc()) $task  = $r;
		$res->close();
		if( ($_SESSION['user']['id'] !== $task['cid']) && !Security::isAdmin() ) return  array('success' => false, 'msg' => L\No_access_for_this_action);
		mysqli_query_params('update tasks set status = CASE WHEN ( (has_deadline = 0) OR ( (has_deadline = 1) AND (date_end > CURRENT_TIMESTAMP) ) ) THEN 2 ELSE 1 END where id = $1', $id) or die(mysqli_query_error());
		/* update responsible user statuses to incomplete*/
		mysqli_query_params('update tasks_responsible_users set status = 0 where task_id = $1', $id) or die(mysqli_query_error());
		/* end of update responsible user statuses to incomplete*/
		/* log and notify all users about task closing */
		Log::add(Array('action_type' => 31, 'task_id' => $id, 'remind_users' => $task['cid'].','.$task['responsible_user_ids'], 'info' => 'title: '.$task['title']));
		$this->updateChildTasks($id);

		Cases::updateCaseUpdateInfo($id);

		SolrClient::runCron();
		return array('success' => true, 'id' => $id);
	}	

	function updateChildTasks($task_id){
		// selecting child tasks (that depend on this task completition) 
		$updatingChildTasks = array();
		// $res = mysqli_query_params('SELECT ct.id, COUNT(pt.id), SUM(pt.status)
		// 	FROM tasks_dependance td 
		// 	JOIN tasks ct ON td.task_id = ct.id AND ct.status = 0 -- child tasks of currently updated task
		// 	JOIN tasks_dependance ctd ON ct.id = ctd.task_id
		// 	JOIN tasks pt ON ctd.parent_task_id = pt.id AND ct.status = 0 -- al parent tasks of the child tasks
		// 	WHERE td.parent_task_id = $1
		// 	GROUP BY ct.id', $task_id) or die(mysqli_query_error());
		// while($r = $res->fetch_row()) if($r[1]*2 == $r[2]) $updatingChildTasks[] = $r[0];
		// $res->close();
		// if(!empty($updatingChildTasks)) mysqli_query_params('update tasks set status = 2 where id in ('.implode(',', $updatingChildTasks).')') or die(mysqli_query_error());
	}

	function getUserTasks($p){
		$case_id = false;
		if(!empty($p->case_id)){
			if(!Security::canReadCase($p->case_id)) throw new Exception(L\Access_denied);
			$case_id = $p->case_id;
		}

		// get tasks that are owned by this user or is responsible
		$rez = array('success' => true, 'data' => array());
		$from = 'FROM tasks t LEFT JOIN tasks_responsible_users ru ON t.id = ru.task_id AND ru.user_id = $1 ';
		/* analize filter for tasks */
		//$where = '';
		$criterias = $case_id ? array('t.case_id = $2 and ( (t.privacy = 0) or (t.cid = $1) OR (ru.user_id IS NOT NULL) ) ') : array();
		if(!empty($p->showTasks)){
			$tasksCriteria = 0;
			$internalCriteria = 0;
			$deadlinesCriteria = 0;
			$ownerCriteria = 0;
			for($i = 0; $i < sizeof($p->showTasks); $i++)
				if($p->showTasks[$i] < 4)  $tasksCriteria = intval($p->showTasks[$i]);
				elseif ($p->showTasks[$i] < 6) $internalCriteria = intval($p->showTasks[$i]);
				elseif ($p->showTasks[$i] == 6) $deadlinesCriteria = intval($p->showTasks[$i]);
				else $ownerCriteria = intval($p->showTasks[$i]);
			//$where = ($tasksCriteria == 1) ? '' : ' where ';
			switch($tasksCriteria){
				case 0: if(!$case_id) $criterias[] = ' (t.status <> 3)'; break;
				case 1: break; // All
				case 2: $criterias[] = ' (t.status <> 3)'; break; //Active
				case 3: $criterias[] = ' (t.completed is not null)'; break; //Completed
			}
			//$where .= empty($internalCriteria) ? '' : (empty($where) ? '' : ' and ');
			switch($internalCriteria){
				case 0: break;
				case 4: $criterias[] = ' (t.`type` = 0)'; break;
				case 5: $criterias[] = ' (t.`type` = 1)'; break;
			}
			//$where .= empty($deadlinesCriteria) ? '' : (empty($where) ? '' : ' and ');
			if($deadlinesCriteria == 6) $criterias[] = ' (t.`date_end` is not null)';
			//$where .= empty($where) ? '' : ' and ';
			switch($ownerCriteria){
				case 0: if(!$case_id) $criterias[] = ' ((t.cid = $1) OR (ru.user_id IS NOT NULL))'; break;
				case 7: $criterias[] = ' (t.cid = $1)'; break;
				case 8: $criterias[] = ' (ru.user_id is not null)'; break;
			}
		}
		$from .= empty($criterias) ? 'where ((t.cid = $1) OR (ru.user_id IS NOT NULL)) and (t.status <> 3)' : ' where '.implode(' and ', $criterias);
		/* end of analize filter for tasks */
		/* get tasks count */			
		$res = mysqli_query_params('select count(t.id) '.$from, array($_SESSION['user']['id'], $case_id)) or die(mysqli_query_error());
		while($r = $res->fetch_row()) $rez['total'] = $r[0];
		$res->close();
		/* end of get tasks count */			
		/* selecting tasks /**/
		$sql = 'SELECT t.*'.
			',DATEDIFF(t.`date_end`, UTC_DATE()) `days`'.
			',(select name from cases where id = t.case_id) `case`'.
			',(select concat(coalesce(concat(date_format(date_start, \''.$_SESSION['user']['short_date_format'].'\'), \' - \'), \'\'), coalesce(custom_title, title)) from objects where id = t.object_id) `object`'.
			',(select l'.UL_ID().' from tags where id = t.responsible_party_id) `responsible_party` '.
			$from.' order by t.cdate'.(empty($p->limit) ? '' : ' LIMIT '.intval($p->limit)).(empty($p->start) ? '' : ' OFFSET '.intval($p->start));
		$res = mysqli_query_params($sql, array($_SESSION['user']['id'], $case_id)) or die(mysqli_query_error());
		while($r = $res->fetch_assoc()){
			if(!empty($r['responsible_user_ids']) && ($r['responsible_user_ids'] != $r['cid'])){
				$res2 = mysqli_query_params('select id, l'.UL_ID().' from users_groups where id in (0'.$r['responsible_user_ids'].')') or die(mysqli_query_error());
				while($r2 = $res2->fetch_row()) $r['users'][$r2[0]] = $r2[1];
				$res2->close();
			}
			if(!empty($r['date_end'])){
				$r['days'] = formatLeftDays($r['days']);
				if(strpos($r['days'], '"cM') !== false) $r['hot'] = 1;
			}
			if(!empty($r['completed'])) $r['completed_text'] = formatTaskTime($r['completed']);
			$this->getTaskStyles($r);
			$rez['data'][] = $r;
		}
		$res->close();
		return $rez;
		/* end of selecting tasks /**/
	}
	
	function getAssociableTasks($p){
		$case_id = false;
		if(isset($p->task_id) && is_numeric($p->task_id)) 
			try{
				require_once 'Cases.php';
				$case_id = Cases::getId($p->task_id);
			}catch(Exception $e){
			
			}
		$rez = ($case_id ? $this->getCaseTasks(json_decode('{"case_id": '.$case_id.'}')) : $this->getUserTasks(json_decode('{}')));
		/* remove current task id from results */
		for($i = 0; $i < sizeof($rez['data']); $i++)
			if($rez['data'][$i]['id'] == $p->task_id) array_splice($rez['data'], $i, 1);
		/* end of remove current task id from results */
		return $rez;
	}
	
	function getTasksByLawyer(){
		if(!Security::canManage()) throw new Exception(L\Access_denied);
		$rez = array('success' => true, 'data' => array());
		/* get visible lawyer ids */
		$lawyer_ids = array();
		$sql = 'SELECT DISTINCT ura2.user_id '.
			'FROM users_groups_association ura1 '.
			'JOIN users_groups_association ura2 ON ((ura1.office_id = 0) OR (ura2.office_id = 0) OR (ura1.office_id = ura2.office_id)) AND ura2.active = 1 '.
			//'JOIN cases_rights_effective e ON e.user_id = ura2.user_id AND e.access = 3 '.
			'WHERE ura1.active = 1 AND ura1.user_id = $1';
		$res = mysqli_query_params($sql, $_SESSION['user']['id']) or die(mysqli_query_error());
		while($r = $res->fetch_row()) $lawyer_ids[] = $r[0];
		$res->close();

		/* end of get visible lawyer ids */
		
		/* retreiving lawyer tasks /**/
		$sql = 'SELECT u.id, u.l'.UL_ID().' `name`, COUNT(t.id) `count`'.
			'FROM tasks t '.
			'JOIN tasks_responsible_users ru ON ru.task_id = t.id  '.
			',users_groups u '.
			'WHERE t.status <> 3 '.
			'GROUP BY u.id, 2  ORDER BY 2';
		$res = mysqli_query_params($sql, $_SESSION['user']['id']) or die(mysqli_query_error());
		while($r = $res->fetch_assoc()) $rez['data'][] = $r;
		$res->close();
		/* end of retreiving lawyer tasks /**/
		return $rez;
	}
	
	private function getTaskStyles(&$task){
		$cls = '';
		//$iconCls = 'icon-calendar-small';
		$iconCls = 'icon-calendar-medium-clean';
		if($task['status'] == 4){
			$cls = 'cO';
			//$iconCls = ' icon-bullet_gray';
		}
		if(!empty($task['missed'])){
			//$cls = 'cM';
			//$iconCls = 'icon-cross-script';
		}
		if(!empty($task['completed'])){
			$cls = ($task['status'] != 3) ? 'cGR' : 'cG';
			//$iconCls = 'icon-tick';
		}
		if(!empty($cls)) $task['cls'] = $cls;
		if(!empty($iconCls)) $task['iconCls'] = $iconCls;
	}
	
	function browse($p){
		$rez = array('success' => true, 'data' => array(), 'total' => 0);
		if(!empty($p->facets) && !empty($p->facets->date_end))
			for($i = 0; $i < sizeof($p->facets->date_end); $i++)
				for($j=0; $j < sizeof($p->facets->date_end[$i]->values); $j++)
					switch($p->facets->date_end[$i]->values[$j]){
						case '0today': 		$p->facets->date_end[$i]->values[$j] = '[NOW/DAY TO NOW/DAY]'; break;
						case '1tomorrow': 	$p->facets->date_end[$i]->values[$j] = '[NOW/DAY+1DAY TO NOW/DAY+1DAY]'; break;
						case '2next7days': 	$p->facets->date_end[$i]->values[$j] = '[NOW/DAY TO NOW/DAY+6DAY ]'; break;
						case '3currentMonth': 	$p->facets->date_end[$i]->values[$j] = '[NOW/MONTH TO NOW/MONTH+1MONTH]'; break;
						case '4nextMonth': 	$p->facets->date_end[$i]->values[$j] = '[NOW/MONTH+1MONTH TO NOW/MONTH+2MONTH]'; break;
						case '5noDeadline': 	$p->facets->date_end[$i]->values[$j] = '[* TO *]'; break;
					}
		require_once '../engine.php';
		$sr = search_tasks($p);
		if($sr){
			$rez['total'] = $sr->response->numFound;
			foreach($sr->response->docs as $d){
				$rd = array();
				foreach($d as $fn => $fv) $rd[$fn] = is_array($fv) ? implode(',', $fv) : $fv;
				$rd['missed'] = empty($rd['missed']) ? 0 : 1;
				if(!empty($rd['date_end'])) $rd['date_end'] = substr($rd['date_end'], 0, 10);
				if(!empty($rd['cdate'])) $rd['cdate'] = substr($rd['cdate'], 0, 10).' '.substr($rd['cdate'], 11, 8);
				if(!empty($rd['case_id'])){
					$res = mysqli_query_params('select name from cases where id = $1', $rd['case_id']) or die(mysqli_query_error());
					while($r = $res->fetch_row()) $rd['case'] = $r[0];
					$res->close();
				}
				if(!empty($rd['action_id'])){
					$res = mysqli_query_params('select coalesce(custom_title, title) from objects where id = $1', $rd['action_id']) or die(mysqli_query_error());
					while($r = $res->fetch_row()) $rd['object'] = $r[0];
					$res->close();
					$rd['object_id'] = $rd['action_id'];
					unset($rd['action_id']);
					
				}
				$this->getTaskStyles($rd);
				$rez['data'][] = $rd;
			}

			if(!empty($sr->facet_counts)){
				$rez['facets'] = array('date_end' => array());
				foreach($sr->facet_counts->facet_fields as $k => $f) $rez['facets'][$k] = $f;
				/* process cases facet */
				if(!empty($rez['facets']['case_id'])){
					$case_ids = array();
					foreach($rez['facets']['case_id'] as $k => $v) $case_ids[$k] = array('id' => $k, 'items' => $v);
					if(!empty($case_ids)){
						$res = mysqli_query_params('select id, name from cases where id in ('.implode(',', array_keys($case_ids)).')') or die(mysqli_query_error());
						while($r = $res->fetch_assoc()) $case_ids[$r['id']]['title'] = $r['name'];
						$res->close();
						$rez['facets']['case_id'] = array_values($case_ids);
					}
				}
				/* end of process cases facet */
				if(!empty($sr->facet_counts->facet_queries)){
					$deadlineFacet = array();
					foreach($sr->facet_counts->facet_queries as $f => $fv) if($fv > 0) $deadlineFacet[$f] = $fv;
					if(!empty($deadlineFacet)) $rez['facets']['date_end'] = $deadlineFacet;
				}
			}
		}

		return $rez;
	}

	static function getTaskInfoForEmail($id, $user_id = false, $message = ''){
		$rez = '';
		$user = array();
		if($user_id == false) $user = &$_SESSION['user'];
		else{
			$user = UsersGroups::getUserPreferences($user_id);
			if(empty($user['language_id'])) $user['language_id'] = 1;
		}
		$sql = 'select `title`, date_start, date_end, description, status, category_id, importance, type, allday, has_deadline, cid'.
			',f_get_tree_path(id) `path_text` '.
			',(select l'.$user['language_id'].' from users_groups where id = t.cid) owner_text'.
			',cdate'.
			',responsible_user_ids'.
			',(select reminds from tasks_reminders where task_id = $1 and user_id = $2) reminders'.
			',DATABASE() `db` '.
			' from tasks t where id = $1';
		$res = mysqli_query_params($sql, array($id, $user['id']) ) or die(mysqli_query_error());
		if($r = $res->fetch_assoc()){
			$format = 'Y, F j';
			if($r['allday'] != 1) $format .= ' H:i';
			$datetime_period = formatMysqlDate($r['date_start'], $format);
			if($r['has_deadline'] == 1){
				$i = strtotime($r['date_end']);
				$datetime_period .= ' - '.formatMysqlDate($r['date_end'], $format);
			}
			$created_date_text = formatMysqlDate($r['cdate'], 'Y, F j H:i');
			$importance_text = '';
			switch($r['importance']){
				case 1: $importance_text = L('Low', $user['language_id']); break;
				case 2: $importance_text = L('Medium', $user['language_id']); break;
				case 3: $importance_text = L('High', $user['language_id']); break;
			}
			//$left = formatLeftDays($r['days']);
			$users = array();
			$ures = mysqli_query_params('select u.id, u.l'.$user['language_id'].' `name`, ru.status, ru.time from users_groups u '.
				'left join tasks_responsible_users ru on u.id = ru.user_id and ru.task_id = $1 where u.id in (0'.$r['responsible_user_ids'].') order by 1', $id) or die(mysqli_query_error());
			while($ur = $ures->fetch_assoc()){
				$users[] = "\n\r".'<tr><td style="width: 1% !important; padding:5px 5px 5px 0px; vertical-align:top; white-space: nowrap">'.
				"\n\r".'<img src="'.getCoreHost($r['db']).'photo/'.$ur['id'].'.jpg" style="width:32px; height: 32px" alt="'.$ur['name'].'" title="'.$ur['name'].'"/>'.
				"\n\r".( ($ur['status'] == 1) ? '<img src="'.getCoreHost($r['db']).'css/i/ico/tick-circle.png" style="width:16px;height:16px; margin-left: -16px"/>': '').
				"\n\r".'</td><td style="padding: 5px 5px 5px 0; vertical-align:top"><b>'.$ur['name'].'</b>'.
				"\n\r".'<p style="color:#777;margin:0;padding:0">'.
				"\n\r".( ($ur['status'] == 1) ? L('Completed', $user['language_id']).': <span style="color: #777" title="'.$ur['time'].'">'.formatMysqlDate($ur['time'], 'Y, F j H:i').'</span>' : L('waitingForAction', $user['language_id']) ).
				"\n\r".'</p></td></tr>';

			}
			$ures->close();
			$users =  empty($users) ? '' : '<tr><td style="width: 1%; padding: 5px 15px 5px 0; color: #777; vertical-align:top">'.L('TaskAssigned', $user['language_id']).':</td><td style="vertical-align:top">'.
				'<table style="font-family: \'lucida grande\',tahoma,verdana,arial,sans-serif; font-size: 11px; color: #333; width: 100%; display: table; border-collapse: separate; border-spacing: 0;"><tbody>'.
				implode('', $users).'</tbody></table></td></tr>';
			
			$files = array();
			$files_text = '';
			$fres = mysqli_query_params('select id, name from tree where pid = $1 and `type` = 5 order by `name`', $id) or die(mysqli_query_error());
			while($fr = $fres->fetch_assoc()) $files[] = $fr;
			$fres->close();

			if(!empty($files)){
				$files_text .= '<tr><td style="width: 1%; padding: 5px 15px 5px 0; color: #777; vertical-align:top">'.L('Files', $user['language_id']).':</td><td style="vertical-align:top"><ul style="list-style: none; padding:0;margin:0">';
				foreach($files as $f){
					$files_text .= '<li style="margin:0;padding: 3px 0"><a href="#" name="file" fid="'.$f['id'].'" style="text-decoration: underline; color: #15C"><img style="float:left;margin-right:5px" src="'.getCoreHost($r['db']).'css/i/ext/'.Files::getIconFileName($f['name']).'"> '.$f['name'].'</a></li>';
				}
				$files_text .= '</ul></td></tr>';
			}

			$reminders_text = '';
			if(!empty($r['reminders'])){
				$reminders_text .= '<tr><td  style="width: 1%; padding: 5px 15px 5px 0; color: #777; vertical-align:top">'.L('Reminders', $user['language_id']).':</td><td style="vertical-align:top"><ul style="list-style: none; text-decoration: none; color: #333;margin:0;padding: 0">';
				$ra = explode('-', $r['reminders']);
				foreach($ra as $rem){
					$rem = explode('|', $rem);
					$units = '';
					switch($rem[2]){
						case 1: $units = L('ofMinutes', $user['language_id']); break;
						case 2: $units = L('ofHours', $user['language_id']); break;
						case 3: $units = L('ofDays', $user['language_id']); break;
						case 4: $units = L('ofWeeks', $user['language_id']); break;
					}
					$reminders_text .= '<li>'.$rem[1].' '.$units.'</li>';
				}
				$reminders_text .= '</ul></td></tr>';
			}

			$message = str_replace( array('<i', '</i>'), array('<strong', '</strong>'), $message);
			$rez = file_get_contents(CB_TEMPLATES_PATH.'task_notification_email.html');
			
			$rez = str_replace(
				array(
					'{top}'
					,'{name_style}' 
					,'{name}'
					,'{datetime_period}'
					,'{description}'
					,'{Status}'
					,'{status_style}'
					,'{status_text}'
					,'{Created}'
					,'{created_date_text}'
					,'{Importance}'
					,'{importance_text}'
					,'{Category}'
					,'{category_style}'
					,'{category_text}'
					,'{Path}'
					,'{path_text}'
					,'{Owner}'
					,'{owner_image}'
					,'{owner_text}'
					,'{assigned_text}'
					,'{Files}'
					,'{files_text}'
					,'{Reminders}'
					,'{reminders_text}'
					,'{bottom}'
				)
				,array(
					$message
					,'font-size: 1.5em; display: block;'.( ($r['status'] == 3 ) ? 'color: #555; text-decoration: line-through' : '')
					,$r['title']
					,$datetime_period
					,$r['description']
					,L('Status', $user['language_id'])
					,'status-style'
					,L('taskStatus'.$r['status'], $user['language_id'])
					,L('Created', $user['language_id'])
					,$created_date_text
					,L('Importance', $user['language_id'])
					,$importance_text
					,L('Category', $user['language_id'])
					,'category_style'
					,getThesauriTitles($r['category_id'], $user['language_id'])
					,L('Path', $user['language_id'])
					,$r['path_text']
					,L('Owner', $user['language_id'])
					,getCoreHost($r['db']).'photo/'.$r['cid'].'.jpg'
					,$r['owner_text']
					,$users //{assigned_text}
					,L('Files', $user['language_id'])
					,$files_text
					,L('Reminders', $user['language_id'])
					,$reminders_text
					,''
				)
				,$rez
			);
		}
		$res->close();
		return $rez;
	}
	public function getPreview($id){
		if(!is_numeric($id)) return '';
		$d = $this->load($id);
		if($d['success'] != true)  return '';
		$d = $d['data'];

		$rez = '<div class="taskview">
			<h2 '.( ($d['status'] == 3) ? 'class=\'completed\'"' : '' ).'>{name}</h2>
			<div class="datetime">{datetime_period}</div>
			<div class="info">{description}</div>
			<table class="props"><tbody>
			<tr><td class="k">'.L\Status.':</td><td><span class="status{status}">{status_text}</span></td></tr>
			<tr><td class="k">'.L\Importance.':</td><td>{importance_text}</td></tr>
			<tr><td class="k">'.L\Category.':</td><td><img src="/css/i/s.gif" class="icon {category_icon}"> {category_text}</td></tr>
			<tr><td class="k">'.L\Path.':</td><td><a class="path" path="{path}" href="#">{path_text}</a></td></tr>
			<tr><td class="k">'.L\Owner.':</td><td><table class="people"><tbody>
				<tr><td class="user"><img class="photo32" src="photo/{cid}.jpg"></td><td><b>{creator_name}</b><p class="gr">'.L\Created.': '.
				'<span class="dttm" title="{full_create_date}">{create_date}</span></p></td></tr></tbody></table></td></tr>'
			;
			
			$date_format = str_replace('%', '', $_SESSION['user']['short_date_format']);
			$format = 'Y, F j';//$date_format;
			if($d['allday'] != 1) $format .= ' H:i';
			$i = strtotime($d['date_start']);
			$d['datetime_period'] = date($format, $i);
			
			if($d['has_deadline'] == 1){
				$i = strtotime($d['date_end']);
				$d['datetime_period'] .= ' - '.date($format, $i);
			}

			$d['importance_text'] = '';
			switch($d['importance']){
				case 1: $d['importance_text'] = L\Low; break;
				case 2: $d['importance_text'] = L\Medium; break;
				case 3: $d['importance_text'] = L\High; break;
			}

			$params = array( '{name}' => adjustTextForDisplay($d['title'])
				,'{datetime_period}' => $d['datetime_period']
				,'{description}' => nl2br(adjustTextForDisplay($d['description']))
				,'{status}' => $d['status']
				,'{status_text}' => L('taskStatus'.$d['status'])
				,'{importance_text}' => $d['importance_text']
				,'{category_icon}' => getThesauryIcon($d['category_id'])
				,'{category_text}' => getThesauriTitles($d['category_id'])
				,'{path}' => $d['path']
				,'{path_text}' => $d['pathtext']
				,'{cid}' => $d['cid']
				,'{creator_name}' => getUsername($d['cid'])
				,'{full_create_date}' => date($date_format.' H:i', strtotime($d['cdate']))
				,'{create_date}' => date($date_format.' H:i', strtotime($d['cdate']))
				);
			$rez = str_replace(array_keys($params), array_values($params), $rez);

			if(!empty($d['users'])){
				$rez .= '<tr><td class="k">'.L\TaskAssigned.':</td><td><table class="people"><tbody>';
				foreach($d['users'] as $u){
					$un = getUsername($u['id']);
					$rez .= '<tr><td class="user"><div style="position: relative"><img class="photo32" src="photo/'.$u['id'].'.jpg" alt="'.$un.'" title="'.$un.'">'.
					( ($u['status'] == 1 ) ? '<img class="done icon icon-tick-circle" src="css/i/s.gif" />': "").
					'</div></td><td><b>'.$un.'</b>'.
					'<p class="gr">'.( ($u['status'] == 1) ? L\Completed.': '.date($date_format.' H:i', strtotime($u['time']) ) : L\waitingForAction ).'</p></td></tr>'; //<a class="bt" name="complete" uid="1" href="#">завершить</a>

				}
				$rez .= '</tbody></table></td></tr>';
			}
			
			if(!empty($d['files'])){
				$rez .= '<tr><td class="k">'.L\Files.':</td><td><ul class="task_files">';
				foreach($d['files'] as $f)
					$rez .= '<li><a href="#" name="file" fid="'.$f['id'].'" onclick="App.mainViewPort.fireEvent(\'fileopen\', {id:'.$f['id'].'})" class="dib lh16 icon-padding file-unknown file-'.getFileExtension($f['name']).'">'.$f['name'].'</a></li>';
				$rez .= '</ul></td></tr>';
			}

			// 	//<!--tr><td class="k">Файлы:</td><td><!--ul class="obj-files"><li class="doc"><a href="#file1">Conference agenda with track.docx</a></li><li class="pdf"><a href="#file2">FOE-0506-Romanenko-1-web summary-DP-10 27 05.doc</a></li></ul-->
			
			if(!empty($d['reminds'])){
				$rez .= '<tr><td class="k">'.L\Reminders.':</td><td><ul class="reminders">';
				$r = explode('-', $d['reminds']);
				foreach($r as $rem){
					$rem = explode('|', $rem);
					$units = '';
					switch($rem[2]){
						case 1: $units = L\ofMinutes; break;
						case 2: $units = L\ofHours; break;
						case 3: $units = L\ofDays; break;
						case 4: L\ofWeeks; break;
					}
					$rez .= '<li><a name="rem_edit" rid="1" href="#">'.$rem[1].' '.$units.'</a></li>';
				}
				$rez .= '</ul></td></tr>'; //<a class="click nlhl" name="rem_add">Добавить напоминание</a>
			}
			$rez .= '</tbody></table></div>';

		return $rez;
	}
	static public function getAxtiveTasksBlockForPreview($pid){
		$rez = array();
		$sql = 'select id, name, date_end, DATEDIFF(`date_end`, UTC_DATE()) `days` from tree where pid = $1 and `type` = 6 order by date_end';
		$res = mysqli_query_params($sql, $pid) or die(mysqli_query_error());
		while($r = $res->fetch_assoc()){
			$rez[] = '<li class="icon-padding icon-task"><a class="task" href="#" nid="'.$r['id'].'">'.$r['name'].'</a>'.(empty($r['date_end']) ? '' : '<p class="cG">'.formatLeftDays($r['days']).'</p>');
		}
		$res->close();
		$rez = empty($rez) ? '' : '<ul class="obj-files">'.implode('', $rez).'</ul>';
		return $rez;
	}

	static function getSorlData($id){
		$rez = array();
		$sql = 'SELECT 
			title
			,status
			,category_id
			,importance
			,privacy
			,responsible_party_id
			,responsible_user_ids
			,autoclose
			,description
			,parent_ids
			,child_ids
			,missed
			,DATE_FORMAT(completed, \'%Y-%m-%dT%H:%i:%sZ\') `completed`
			,cid
			FROM tasks where id = $1';
		
		$res = mysqli_query_params($sql, $id) or die(mysqli_query_error()."\n".$sql);
		if($r = $res->fetch_assoc()){
			$rez['status'] = $r['status'];
			$rez['importance'] = $r['importance'];
			$rez['category_id'] = $r['category_id'];
			$rez['parent_ids'] = empty($r['parent_ids']) ? null : explode(',', $r['parent_ids']);
			if(!empty($r['responsible_user_ids'])) $rez['user_ids'] = explode(',', $r['responsible_user_ids']);
			$rez['content'] = $r['description'];
		}
		$res->close();/**/
		return $rez;
	}
}
?>