<?php

namespace CB;

class Browser{
	/* getCustomControllerResults function used to check if node has a controller specified in its "cfg" field
		if node have custom controller then results from the controller are returned, otherwise false is returned
	 */
	public function getCustomControllerResults($path){
		$rez = false;
		$ids = explode('/', $path);
		$id = array_pop($ids);
		while( (!is_numeric($id) || ($id < 1)) && !empty($ids)) $id = array_pop($ids);

		if(empty($id) || !is_numeric($id) ) return false;

		$sql = 'select cfg from tree where id = $1';
		$res = DB\mysqli_query_params($sql, $id) or die(DB\mysqli_query_error());
		if($r = $res->fetch_assoc()) {
		 	if(!empty($r['cfg'])) $r['cfg'] = json_decode($r['cfg']);
		 	if(!empty($r['cfg']->controller)){
		 		$userMenu = new UserMenu();
		 		$rez = $userMenu->{$r['cfg']->controller}($path);
		 		unset($userMenu);
		 	}
		 }
		 $res->close();
		 return $rez;
	}

	function getObjectsForField($p){
		// ,"scope": 'tree' //project, parent, self, $node_id
		// ,"field": <field_name> //for field type
	
		// ,"descendants": true
		// /* filter used for objects */
		// ,+"tags": []
		// ,+"types": []
		// ,+"templates": []
		// ,"templateGroups": []

		//,+query - user query
		if(!empty($p->source))
		switch($p->source){
			case 'field':
			if( empty($p->pidValue) || empty($p->field) ) break;
			$ids = Util\toNumericArray($p->pidValue);
			if(empty($ids)) break;
				$sql = 'SELECT od.value FROM '.
					'objects o '.
					'JOIN templates t ON t.id = o.`template_id` '.
					'JOIN templates_structure ts ON t.id = ts.`template_id` AND ts.name = $1 '.
					'JOIN objects_data od ON o.id = od.`object_id` AND od.`field_id` = ts.id '.
					'WHERE o.`id` IN ('.implode(',',$ids).');';
				$res = DB\mysqli_query_params($sql, $p->field) or die(DB\mysqli_query_error());
				$ids = array();
				while($r = $res->fetch_row()){
					if(!empty($r[0])){
						$v = explode(',', $r[0]);
						for ($i=0; $i < sizeof($v); $i++) { 
							if(!empty($v[$i])) $ids[$v[$i]] = 1;
						}
					}
				}
				$res->close();
				$ids = array_keys($ids);
				if(empty($ids)) return array('success' => true, 'data' => array() );
				$p->ids = $ids;
				break;

		}
		$pids = false;
		if(!empty($p->scope)){
			switch($p->scope){
				case 'project': /* limiting pid to project. If not in a project then to parent directory */
					if(!empty($p->objectId) && is_numeric($p->objectId)){
						$sql = 'select coalesce(case_id, pid) from tree where id = $1 ';
						$res = DB\mysqli_query_params($sql, $p->objectId) or die(DB\mysqli_query_error());
						if($r = $res->fetch_row()) $p->pids = $r[0]; 
						$res->close();
					}elseif(!empty($p->path)){
						$v = explode('/',$p->path);
						$pids = 0;
						while(!empty($v) && empty($pids)) $pids = array_pop($v);
					}
					break;
				case 'parent':
					if(!empty($p->objectId) && is_numeric($p->objectId)){
						$sql = 'select pid from tree where id = $1 ';
						$res = DB\mysqli_query_params($sql, $p->objectId) or die(DB\mysqli_query_error());
						if($r = $res->fetch_row()) $p->pids = $r[0]; 
						$res->close();
					}elseif(!empty($p->path)){
						$v = explode('/',$p->path);
						$pids = 0;
						while(!empty($v) && empty($pids)) $pids = array_pop($v);
					}

					break;
				case 'self': 
					if(!empty($p->objectId) && is_numeric($p->objectId)){
						$sql = 'select id from tree where id = $1 ';
						$res = DB\mysqli_query_params($sql, $p->objectId) or die(DB\mysqli_query_error());
						if($r = $res->fetch_row()) $p->pids = $r[0]; 
						$res->close();
					}elseif(!empty($p->path)){
						$v = explode('/',$p->path);
						$pids = 0;
						while(!empty($v) && empty($pids)) $pids = array_pop($v);
					}
					break;
				case 'dependent': 
					if(!empty($p->pidValue)) $pids = Util\toNumericArray($p->pidValue);
					break;
				default: 
					$pids = Util\toNumericArray($p->scope);
					break;
			}
		}
		if(!empty($pids)){
			if(empty($p->descendants)) 
				$p->pid = $pids;
			else $p->pids = $pids;
		}

		$p->fl = 'id,name,type,subtype,template_id,status';
		if(!empty($p->fields)){
			if(!is_array($p->fields)) $p->fields = explode(',', $p->fields);
			for ($i=0; $i < sizeof($p->fields); $i++){
				$fieldName = trim($p->fields[$i]);
				if($fieldName == 'project') $fieldName = 'case';
				if(in_array($fieldName, array('date', 'path', 'case', 'size', 'cid', 'oid', 'cdate', 'udate') ) ) $p->fl .= ','.$fieldName;
			}
		}

		$search = new Search();
		return $search->query($p);

		//return ;
	}

	function createFolder($path){
		$pid = explode('/', $path);
		$pid = array_pop($pid);
		if(!is_numeric($pid)) return array('success' => false);

		/* check security access */
		if(!Security::canCreateFolders($pid)) throw new \Exception(L\Access_denied);
		
		/* find default folder name */
		$newFolderName = L\NewFolder;
		$existing_names = array();
		$sql = 'select name from tree where pid = $1 and name like $2';
		$res = DB\mysqli_query_params($sql, array($pid, $newFolderName.'%')) or die(DB\mysqli_query_error());
		while($r = $res->fetch_row()) $existing_names[] = $r[0];
		$res->close();
		$i = 1;
		while(in_array($newFolderName, $existing_names)){
			$newFolderName = L\NewFolder.' ('.$i.')';
			$i++;
		}
		/* end of find default folder name */

		DB\mysqli_query_params('insert into tree (pid, user_id, `type`, `name`, cid, uid, template_id) values ($1, $2, $3, $4, $2, $2, $3)', array($pid, $_SESSION['user']['id'], 1, $newFolderName, config\default_folder_template)) or die(DB\mysqli_query_error());
		$id = DB\last_insert_id();
		SolrClient::runCron();
		return array('success' => true, 'path' => $path, 'data' => array( 'nid' => $id, 'pid' => $pid, 'name' => $newFolderName, 'system' => 0, 'type' => 1, 'subtype' => 0, 'iconCls' => 'icon-folder', 'cid' => $_SESSION['user']['id']) );
	}

	public function delete($paths){
		if(!is_array($paths)) $paths = array($paths);
		/* collecting ids from paths */
		$ids = array();
		foreach($paths as $path){
			$id = explode('/', $path);
			$id = array_pop($id);
			if(!is_numeric($id)) return array('success' => false);
			if( !Security::canDelete($id) ) throw new \Exception( L\Access_denied );
			$ids[] = intval($id);
		}
		if(empty($ids)) return array('success' => false);

		/* before deleting we should check security for specified paths and all children */
		
		/* if access is granted then setting dstatus=1 for specified ids and dstatus = 2 for all their children /**/
		fireEvent('beforeNodeDbDelete', $ids);
		DB\mysqli_query_params('update tree set did = $1, dstatus = 1, ddate = CURRENT_TIMESTAMP, updated = (updated | 1) where id in ('.implode(',', $ids).') ', $_SESSION['user']['id']) or die(DB\mysqli_query_error());
		foreach($ids as $id) DB\mysqli_query_params('call p_mark_all_childs_as_deleted($1, $2)', array($id, $_SESSION['user']['id'])) or die(DB\mysqli_query_error());
		SolrClient::runCron();
		
		fireEvent('nodeDbDelete', $ids);
		return array('success' => true, 'ids' => $ids);
	}

	public function rename($p){
		$id = explode('/', $p->path);
		$id = array_pop($id);
		$p->name = trim($p->name);
		if(!is_numeric($id) || empty($p->name)) return array('success' => false);

		/* check security access */
		if(!Security::canWrite($id)) throw new \Exception(L\Access_denied);
		
		DB\mysqli_query_params('update tree set name = $1 where id = $2', array($p->name, $id)) or die(DB\mysqli_query_error());
		DB\mysqli_query_params('update objects set custom_title = $1 where id = $2', array($p->name, $id)) or die(DB\mysqli_query_error());
		DB\mysqli_query_params('update files set name = $1 where id = $2', array($p->name, $id)) or die(DB\mysqli_query_error());
		DB\mysqli_query_params('update tasks set title = $1 where id = $2', array($p->name, $id)) or die(DB\mysqli_query_error());
		
		$sql = 'INSERT INTO objects_data (object_id, field_id, value) '.
			'select $1, ts.id, $2 from tree t join templates_structure ts on t.template_id = ts.template_id where t.id = $1 and ts.name = \'_title\' '.
			' on duplicate key update `value` = $2';
		DB\mysqli_query_params($sql, Array($id, $p->name) ) or die(DB\mysqli_query_error());

		SolrClient::runCron();
		return array('success' => true, 'data' => array( 'id' => $id, 'newName' => $p->name) );
	}

	public function paste($p){
		
		if(!is_numeric($p->pid) || empty($p->data)) return array('success' => false, 'msg' => L\ErroneousInputData);
		
		if(empty($p->confirmed)) $p->confirmed = false;
		$process_ids = array();
		// check if not pasting object to itself
		for ($i=0; $i < sizeof($p->data); $i++) {
			if($p->pid == $p->data[$i]->id) return array('success' => false, 'msg' => L\CannotCopyObjectToItself);
			if( $this->isChildOf($p->pid, $p->data[$i]->id) ) return array('success' => false, 'msg' => L\CannotCopyObjectInsideItself);

			$sql = 'select id, pid, name, `system`, `type`, subtype from tree where id = $1';
			$res = DB\mysqli_query_params($sql, $p->data[$i]->id) or die(DB\mysqli_query_error());
			if($r = $res->fetch_assoc()){
				$process_ids[] = $r['id'];
				if(empty($p->action)) $p->action = 'copy';
				if(!$p->confirmed && ($p->action !== 'copy') ){
					$type = ($p->action == 'shortcut') ? 2 : $r['type'];
					$sql = 'select id from tree where pid = $1 and system = $2 and type = $3 and subtype = $4 and name = $5';
					$res2 = DB\mysqli_query_params($sql, array($p->pid, $r['system'], $type, $r['subtype'], $r['name'])) or die(DB\mysqli_query_error());
					if($r2 = $res2->fetch_assoc()){
						//if($r2['id'] == $r['id']) return array('success' => false, 'msg' => L\CannotCopyObjectOverItself);
						return array('success' => false, 'confirm' => true, 'msg' => L\ConfirmOverwriting);
					}
					$res2->close();
				}
			}
			$res->close();/**/
		}
		
		/* checking if processed ids names (of corresponding types) exists in target */
		
		if(empty($process_ids)) return array('success' => true, 'pids' => array());

		/* end of checking if processed ids names (of corresponding types) exists in target */

		$modified_pids = array($p->pid);
		switch($p->action){
			case 'copy':
				foreach($process_ids as $id){
					$newName = '';
					$sql = 'select t1.name, t2.name from tree t1 left join tree t2 on t2.pid = $2 and t2.name = t1.name where t1.id = $1';
					$res = DB\mysqli_query_params($sql, array($id, $p->pid)) or die(DB\mysqli_query_error());
					if($r = $res->fetch_row()) $newName = empty($r[1]) ? $r[0] : $this->getNewCopyName($p->pid, $r[0]);
					$res->close();

					DB\mysqli_query_params('insert into tree(pid, user_id, `system`, `type`, template_id, tag_id, name, `date`, size, is_main, cfg, cid, cdate, uid, udate)
						select $2, user_id, 0, `type`, template_id, tag_id, $4, `date`, size, is_main, cfg, $3, CURRENT_TIMESTAMP, $3, CURRENT_TIMESTAMP from tree where id =$1', array($id, $p->pid, $_SESSION['user']['id'], $newName) ) or die(DB\mysqli_query_error());
					$obj_id = DB\last_insert_id();
					$type = 0;
					$res = DB\mysqli_query_params('select `type` from tree where id = $1', $id) or die(DB\mysqli_query_error());
					if($r = $res->fetch_row()) $type = $r[0];
					$res->close();
					switch($type){
						case 3://case
						case 4://case object
							DB\mysqli_query_params('INSERT INTO objects(id, title, custom_title, template_id, date_start, date_end, author, iconCls, details, private_for_user, cid, uid, cdate, udate)'.
								' select $2, title, $4, template_id, date_start, date_end, author, iconCls, details, private_for_user, $3, $3, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP from objects where id =$1', array($id, $obj_id, $_SESSION['user']['id'], $newName) ) or die(DB\mysqli_query_error());
							
							$duplicates = array(0 => 0);
							$sql = 'select id, pid, object_id, field_id from objects_duplicates where object_id = $1 order by id';
							$res = DB\mysqli_query_params($sql, $id) or die(DB\mysqli_query_error());
							while($r = $res->fetch_assoc()){
								DB\mysqli_query_params('INSERT INTO objects_duplicates(pid, object_id, field_id)'.
								' values($1, $2, $3)', array($duplicates[$r['pid']], $r['object_id'], $r['field_id']) ) or die(DB\mysqli_query_error());
								$duplicates[$r['id']] = DB\last_insert_id();
							}
							$res->close();

							$sql = 'select field_id, duplicate_id, `value`, info, files, private_for_user from objects_data where object_id =$1';
							$res = DB\mysqli_query_params($sql, $id) or die(DB\mysqli_query_error());
							while($r = $res->fetch_assoc())
								DB\mysqli_query_params('INSERT INTO objects_data(object_id, field_id, duplicate_id, `value`, info, files, private_for_user)'.
								' values($1, $2, $3, $4, $5, $6, $7)', array($obj_id, $r['field_id'], $duplicates[$r['duplicate_id']], $r['value'], $r['info'], $r['files'], $r['private_for_user']) ) or die(DB\mysqli_query_error());
							$res->close();
							break;
						
						case 5://file
							DB\mysqli_query_params('insert INTO files(id, content_id, `date`, `name`, title, cid, uid, cdate, udate)'.
								' select $2, content_id, `date`, $4, `title`, $3, $3, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP from files where id =$1', array($id, $obj_id, $_SESSION['user']['id'], $newName) ) or die(DB\mysqli_query_error());
							break;
						
						case 6://task
						case 7://event
							DB\mysqli_query_params('INSERT INTO tasks(id, title, date_start, date_end, `type`, privacy, responsible_party_id, responsible_user_ids, autoclose, description, parent_ids, child_ids, `time`, reminds, `status`, missed, completed, cid, uid, cdate, udate)'.
								' select $2, $4, date_start, date_end, `type`, privacy, responsible_party_id, responsible_user_ids, autoclose, description, parent_ids, child_ids, `time`, reminds, `status`, missed, completed, $3, $3, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP from tasks where id =$1', array($id, $obj_id, $_SESSION['user']['id'], $newName) ) or die(DB\mysqli_query_error());
							break;
						
						case 8://message
							break;
					}
					if(!empty($existent_name)) DB\mysqli_query_params('update tree set name = $2 where id = $1', array($obj_id, $this->getNewCopyName($p->pid, $existent_name) ) ) or die(DB\mysqli_query_error());
					Objects::updateCaseUpdateInfo($obj_id);
				}

				break;
			case 'move':
				foreach($process_ids as $id) Objects::updateCaseUpdateInfo($id);
				$res = DB\mysqli_query_params('select pid from tree where id in ('.implode(',', $process_ids).')') or die(DB\mysqli_query_error());
				while($r = $res->fetch_row()) $modified_pids[] = intval($r[0]);
				$res->close();
				DB\mysqli_query_params('update tree set pid = $1, updated = (updated | 100) where id in ('.implode(',', $process_ids).')', $p->pid) or die(DB\mysqli_query_error());
				
				foreach($process_ids as $id) Objects::updateCaseUpdateInfo($id);
				
				$this->markAllChildsAsUpdated($process_ids, 100);
				break;
			case 'shortcut':
				DB\mysqli_query_params('insert into tree (pid, `system`, `type`, `subtype`, target_id, `name`, cid) SELECT $1, 0, 2, 0, id, `name`, $2 from tree where id in ('.implode(',', $process_ids).')', array($p->pid, $_SESSION['user']['id'])) or die(DB\mysqli_query_error());
				Objects::updateCaseUpdateInfo(DB\last_insert_id());
				break;
		}
		SolrClient::runCron();
		return array('success' => true, 'pids' => $modified_pids);
	}

	function getNewCopyName($pid, $name, $excludeExtension = false){
		$ext = '';
		if($excludeExtension){
			$a = explode('.', $name);
			if(sizeof($a) > 1) $ext = '.'.array_pop($a);
			$name = implode('.', $a);
		}

		$id = null;
		$i = 0;
		$newName = '';
		do{
			$newName = L\CopyOf.' '.$name.( ($i > 0) ? ' ('.$i.')' : '').$ext;
			$sql = 'select id from tree where pid = $1 and name = $2';
			$res = DB\mysqli_query_params($sql, array($pid, $newName)) or die(DB\mysqli_query_error());
			if($r = $res->fetch_assoc()) $id = $r['id'];
			else $id = null;
			$res->close();
			$i++;
		}while(!empty($id));
		return $newName;
	}

	function saveFile($p){
		if(!file_exists(FILES_INCOMMING_PATH)) @mkdir(FILES_INCOMMING_PATH, 0777, true);
		
		$files = new Files();
		
		/* clean previous unhandled uploads if any */
		$a = $files->getUploadParams();
		if( ($a !== false) && !empty( $a['files'] ) ){
			@unlink(FILES_INCOMMING_PATH.$_SESSION['key']);
			$files->removeIncomingFiles($a['files']);
		}
		/* end of clean previous unhandled uploads if any */

		$F = &$_FILES;
		if(empty($p['pid'])) return Array('success' => false, 'msg' => L\Error_uploading_file);
		//TODO: SECURITY: check if current user has write access to folder
		
		if(empty($F)){ //update only file properties (no files were uploaded)
			$files->updateFileProperties($p);
			return array( 'success' => true );
		}else foreach ($F as $k => $v) $F[$k]['name'] = strip_tags(@$F[$k]['name']);

		//if( !$files->fileExists($p['pid']) ) return array('success' => false, 'msg' => L\TargetFolderDoesNotExist);
		$res = DB\mysqli_query_params('select id from tree where id = $1', $p['pid']) or die(DB\mysqli_query_error()); 
		if($r = $res->fetch_assoc()){ }else return array('success' => false, 'msg' => L\TargetFolderDoesNotExist);
		$res->close();


		/*checking if there is no upload error (for any type of upload: single, multiple, archive) */
		foreach($F as $fn => $f)
			if (!in_array($f['error'], Array(UPLOAD_ERR_OK, UPLOAD_ERR_NO_FILE))) return Array('success' => false, 'msg' => L\Error_uploading_file .': '.$f['error']);

		/* retreiving files list  */
		switch(@$p['uploadType']){
			case 'archive':
				$archiveFiles = array(); 
				foreach($F as $fk => $f){
					$files->extractUploadedArchive($F[$fk]);
					$archiveFiles = array_merge($archiveFiles, $F[$fk]);
				}
				$F = $archiveFiles;
				break;
			default: 
				$files->moveUploadedFilesToIncomming($F) or die('cannot move file to incomming '.FILES_INCOMMING_PATH);
				break;
		}

		$p['existentFilenames'] = $files->getExistentFilenames( $F, $p['pid'] );
		$p['files'] = &$F;

		if( !empty($p['existentFilenames']) ){
			// store current state serialized in a local file in incomming folder 
			$files->saveUploadParams( $p );
			if(!empty($p['response'])) return $this->confirmUploadRequest((object)$p); //it is supposed to work only for single files upload

			$allow_new_version = false;
			foreach($p['existentFilenames'] as $f){
				$mfvc = Files::getMFVC($f['name']);
				if($mfvc > 0) $allow_new_version = true;
			}
			$rez = array(
				'success' => false
				,'type' => 'filesexist'
				,'allow_new_version' => $allow_new_version
				,'count' => sizeof($p['existentFilenames'])
			);
			if($rez['count'] == 1){
				$rez['msg'] = empty($p['existentFilenames'][0]['msg']) ?  
				str_replace( '{filename}', '"'.$p['existentFilenames'][0]['name'].'"', L\FilenameExistsInTarget ) : $p['existentFilenames'][0]['msg'];
				//$rez['filename'] = $p['existentFilenames'][0]['name'];
				$rez['suggestedFilename'] = $p['existentFilenames'][0]['suggestedFilename'];
			}else $rez['msg'] = L\SomeFilenamesExistsInTarget;
			
			return $rez;
		}
		$files->storeFiles($p); //if everithing is ok then store files
		SolrClient::runCron();
		$rez = array('success' => true, 'data' => array('pid' => $p['pid']));
		$files->attachPostUploadInfo($F, $rez);
		return $rez;
	}

	function confirmUploadRequest($p){ // called when user was asked about file(s) overwrite
		//if cancel then delete all uploaded files from incomming
		$files = new Files();
		$a = $files->getUploadParams();
		$a['response'] = $p->response;
		switch($p->response){
			case 'rename':
				$a['newName'] = $p->newName;
				//check if the new name does not also exist
				if(empty($a['response'])) return array('success' => false, 'msg' => L\FilenameCannotBeEmpty);
				reset($a['files']);
				$k = key($a['files']);
				$a['files'][$k]['name'] = $a['newName'];
				if($files->fileExists( $a['pid'], $a['newName'])){
					$files->saveUploadParams($a);
					return array(
						'success' => false
						,'type' => 'filesexist'
						//,'filename' => $a['newName']
						,'allow_new_version' => (Files::getMFVC($a['newName']) > 0)
						,'suggestedFilename' => $files->getAutoRenameFilename($a['pid'], $a['newName'])
						,'msg' => str_replace( '{filename}', '"'.$a['newName'].'"', L\FilenameExistsInTarget )
					);
				}
				// $files->storeFiles($a);
				// break;
			case 'newversion':
			case 'replace':
			case 'autorename': $files->storeFiles($a); break;
			default: //cancel
				$files->removeIncomingFiles($a['files']);
				return array('success' => true, 'data' => array() );
				break;
		}
		SolrClient::runCron();
		$rez = array('success' => true, 'data' => array('pid' => $a['pid']));
		$files->attachPostUploadInfo($a['files'], $rez);
		return $rez;
	}

	function uploadNewVersion($p){

		$sql = 'select pid from tree where id = $1';
		$res = DB\mysqli_query_params($sql, $p['id']) or die(DB\mysqli_query_error());
		if($r = $res->fetch_assoc()) $p['pid'] = $r['pid'];
		$res->close();

		$rez = Array('success' => true, 'data' => array('id' => $p['id'], 'pid' => $p['pid']));
		
		$f = $_FILES['file'];
		if($f['error'] == UPLOAD_ERR_NO_FILE){
			DB\mysqli_query_params('update files set `title` = $2, `date` = $3 where id = $1', array($p['id'], $p['title'], Util\clientToMysqlDate($p['date']) ) ) or die(DB\mysqli_query_error());
			return $rez;
		}
		if($f['error'] != UPLOAD_ERR_OK) return Array('success' => false, 'msg' => L\Error_uploading_file .': '.$f['error']);

		$p['files'] = &$_FILES;
		$p['response'] = 'overwrite';
		$files = new Files();
		$files->storeFiles($p);
		SolrClient::runCron();
		return $rez;
	}

	public function toggleFavorite($p){
		
		$favoriteFolderId = $this->getFavoriteFolderId();
		$p->pid = $favoriteFolderId;
		$sql = 'select id from tree where pid = $1 and `type` = 2 and target_id = $2';
		$res = DB\mysqli_query_params($sql, array($favoriteFolderId, $p->id)) or die(DB\mysqli_query_error());
		if($r = $res->fetch_row()){
			DB\mysqli_query_params('delete from tree where id = $1', array($r[0]) ) or die(DB\mysqli_query_error());
			$res->close();
			$p->favorite = 0;
		}else{
			$res->close();
			/* get objects name */
			$name = 'Llink';
			$sql = 'select name from tree where id = $1';
			$res = DB\mysqli_query_params($sql, array($p->id)) or die(DB\mysqli_query_error());
			if($r = $res->fetch_row()){
				$name = $r[0];
			}
			$res->close();
			/* end of get objects name */
			DB\mysqli_query_params('insert into tree (pid, user_id, `type`, name, target_id) values($1, $2, 2, $3, $4)', array($favoriteFolderId, $_SESSION['user']['id'], $name, $p->id) ) or die(DB\mysqli_query_error());
			$p->favorite = 1;
		}
		return array('success' => true, 'data' => $p,);
	}
	public function takeOwnership($ids){
		if(!is_array($ids)) $ids = explode(',', $ids);
		$ids = array_filter($ids, 'is_numeric');
		$rez = array('success' => true, 'data' => $ids); 
		if(empty($ids)) return $rez;
		$ids = implode(',', $ids);
		DB\mysqli_query_params('update tree set oid = $1, uid = $1 where id in ('.$ids.') and `system` = 0', $_SESSION['user']['id']) or die(DB\mysqli_query_error());
		//TODO: view if needed to mark all childs as updated, for security to be changed ....
		SolrClient::runCron();
		return $rez;
	}
	public function isChildOf($id, $pid){
		$rez = false;
		$sql = 'SELECT f_get_tree_ids_path($1)';
		$res = DB\mysqli_query_params( $sql, $id ) or die(DB\mysqli_query_error());
		if($r = $res->fetch_row()){
			$r = '/'.$r[0].'/r';
			$rez = ( strpos($r, "/$pid/") !== false ); 
		}
		$res->close();
		return $rez;
	}
	static function getRootFolderId(){
		$id = null;
		$sql = 'select id from tree where pid is null and `system` = 1 and `type` = 1 and subtype = 0';
		$res = DB\mysqli_query_params($sql, array()) or die(DB\mysqli_query_error());
		if($r = $res->fetch_row()) $id = $r[0];
		$res->close();
		return $id;
	}
	public function getRootProperties($id){
		$rez = array('success' => true, 'data' => array());
		$sql = 'select id `nid`, `system`, `type`, `subtype`, `name`, `cfg` from tree where id = $1';
		$res = DB\mysqli_query_params($sql, $id) or die(DB\mysqli_query_error());
		if($r = $res->fetch_assoc()){
			if(empty($r['cfg'])) unset($r['cfg']);
			else $r['cfg'] = json_decode($r['cfg']);

			$rez['data'] = array($r);
			$this->updateLabels($rez['data']);
			$rez['data'] = $rez['data'][0];
		}
		$res->close();
		return $rez;
	}
	static function getFavoriteFolderId(){
		$id = null;
		$sql = 'select id from tree where pid is null and `system` = 1 and `type` = 1 and subtype = 2 and user_id = $1';
		$res = DB\mysqli_query_params($sql, array($_SESSION['user']['id'])) or die(DB\mysqli_query_error());
		if($r = $res->fetch_row()) $id = $r[0];
		$res->close();
		return $id;
	}

	public static function markAllChildsAsUpdated($ids, $bits = 11){
		if(!is_array($ids)) $ids = explode(',', $ids);
		$ids = array_filter($ids, 'is_numeric');
		if(empty($ids)) return;
		foreach($ids as $id)
			DB\mysqli_query_params('call p_mark_all_childs_as_updated($1, $2)', array( $id, $bits ) ) or die(DB\mysqli_query_error());
		return true;
	}

	public function prepareResults(&$data){
		if(empty($data) || !is_array($data)) return;
		for ($i=0; $i < sizeof($data); $i++) { 
			$d = &$data[$i];
			if(isset($d['id']) && empty($d['nid'])){
				$d['nid'] = $d['id'];
				unset($d['id']);
			}
			if(!isset($d['loaded'])){
				$sql = 'select count(*) from tree where pid = $1 and dstatus = 0'.( empty($this->showFoldersContent) ? ' and `template_id` in (0'.implode(',', $GLOBALS['folder_templates']).')' : '' );
				$res = DB\mysqli_query_params($sql, $d['nid']) or die(DB\mysqli_query_error());
				if($r = $res->fetch_row()) $d['loaded'] = empty($r[0]);
				$res->close();
			}
		}

	}
	public function updateLabels(&$data){
		for ($i=0; $i < sizeof($data); $i++) {
			$d = &$data[$i];
			unset($d['iconCls']);
			//@$d['nid'] = intval($d['nid']);
			@$d['system'] = intval($d['system']);
			@$d['type'] = intval($d['type']);
			@$d['subtype'] = intval($d['subtype']);
			switch($d['type']){
				case 0: break; 
				case 1: switch ($d['subtype']) {
						case 1:	if( (substr($d['name'], 0, 1) == '[') && (substr($d['name'], -1, 1) == ']') )
								$d['name'] = L\get(substr($d['name'], 1, strlen($d['name']) -2));
							break;
						case 2:	$d['name'] = L\MyCaseBox; break;
						case 3:	$d['name'] = L\MyDocuments; break;
						case 4:	$d['name'] = L\Cases; break;
						case 5:	$d['name'] = L\Tasks; break;
						case 6:	$d['name'] = L\Messages; break;
						//case 7:	$d['name'] = L\RecycleBin; break;
						case 8:	break;
						case 9: break;
						case 10: $d['name'] = L\PublicFolder; break;
						default: break;
					}
					break;
				case 2: break;
				case 3: break;
				case 4: break;
				case 5: break;
				case 6: break;
				case 7: break;
			}
		}
		return $data;
	}

	public static function getIcon(&$data){
		if(!isset($data['type'])) return '';
		
		switch(intval($data['type'])){
			case 0: return Util\coalesce($data['iconCls'], 'icon-folder');
				break; 
			case 1: 
				switch(intval(@$data['subtype'])){
					case 1:	break;
					case 2:	return 'icon-home'; break;
					case 3:	return 'icon-blue-folder'; break;
					case 4:	return 'icon-briefcase'; break;
					case 5:	return 'icon-calendar-small'; break;
					case 6:	return 'icon-mail-medium'; break;
					case 7:	return 'icon-blue-folder-stamp'; break;
					case 8:	return 'icon-folder'; break;
					case 9: return 'icon-blue-folder'; break;
					case 10: return 'icon-blue-folder-share'; break;
					default: return @Util\coalesce($data['iconCls'], 'icon-folder'); break;
				}
				break;
			case 2: return 'icon-shortcut';//case
				break;
			case 3: return 'icon-briefcase';//case
				break;
			case 4: //case object
				if(!empty($data['cfg']) && !empty($data['cfg']->iconCls)) return $data['cfg']->iconCls;
				if(!empty($data['template_id'])) return Templates::getIcon($data['template_id']);
				return 'icon-none';
				break;
			case 5: //file
				return Files::getIcon($data['name']);
				break;
			case 6:
				if(@$d['status'] == 3) return 'icon-task-completed';
				return 'icon-task';//task
				break;
			case 7: return 'icon-event';//Event
			case 8: return 'icon-mail';//Message (email)
				break;

		}
	}
}