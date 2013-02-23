<?php
require_once 'Cases.php';
require_once 'Security.php';
require_once 'Log.php';
class Browser{
	
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
			$ids = toNumericArray($p->pidValue);
			if(empty($ids)) break;
				$sql = 'SELECT od.value FROM '.
					'objects o '.
					'JOIN templates t ON t.id = o.`template_id` '.
					'JOIN templates_structure ts ON t.id = ts.`template_id` AND ts.name = $1 '.
					'JOIN objects_data od ON o.id = od.`object_id` AND od.`field_id` = ts.id '.
					'WHERE o.`id` IN ('.implode(',',$ids).');';
				$res = mysqli_query_params($sql, $p->field) or die(mysqli_query_error());
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
		if(!empty($p->scope)){
			switch($p->scope){
				case 'project': /* limiting pid to project. If not in a project then to parent directory */
					if(!empty($p->objectId) && is_numeric($p->objectId)){
						$sql = 'select coalesce(f_get_objects_case_id($1), pid) from tree where id = $1 ';
						$res = mysqli_query_params($sql, $p->objectId) or die(mysqli_query_error());
						if($r = $res->fetch_row()) $p->pids = $r[0]; 
						$res->close();
					}elseif(!empty($p->path)){
						$v = explode('/',$p->path);
						$pids = 0;
						while(!empty($v) && empty($pids)) $pids = array_pop($v);
						if(!empty($pids)) $p->pids = $pids;
					}
					break;
				case 'parent':
					if(!empty($p->objectId) && is_numeric($p->objectId)){
						$sql = 'select pid from tree where id = $1 ';
						$res = mysqli_query_params($sql, $p->objectId) or die(mysqli_query_error());
						if($r = $res->fetch_row()) $p->pids = $r[0]; 
						$res->close();
					}elseif(!empty($p->path)){
						$v = explode('/',$p->path);
						$pids = 0;
						while(!empty($v) && empty($pids)) $pids = array_pop($v);
						if(!empty($pids)) $p->pids = $pids;
					}

					break;
				case 'self': 
					if(!empty($p->objectId) && is_numeric($p->objectId)){
						$sql = 'select id from tree where id = $1 ';
						$res = mysqli_query_params($sql, $p->objectId) or die(mysqli_query_error());
						if($r = $res->fetch_row()) $p->pids = $r[0]; 
						$res->close();
					}elseif(!empty($p->path)){
						$v = explode('/',$p->path);
						$pids = 0;
						while(!empty($v) && empty($pids)) $pids = array_pop($v);
						if(!empty($pids)) $p->pids = $pids;
					}
					break;
				default: 
					if(empty($p->descendants)) $p->pid = toNumericArray($p->scope);
					else $p->pids = toNumericArray($p->scope);
					break;
			}
		}

		$p->fl = 'id,name,type,subtype,template_id,status';
		$search = new Search();
		return $search->query($p);

		//return ;
	}

	function createFolder($path){
		$pid = explode('/', $path);
		$pid = array_pop($pid);
		if(!is_numeric($pid)) return array('success' => false);

		/* TODO: check security access */

		/* find default folder name */
		$newFolderName = L\NewFolder;
		$existing_names = array();
		$sql = 'select name from tree where pid = $1 and name like $2';
		$res = mysqli_query_params($sql, array($pid, $newFolderName.'%')) or die(mysqli_query_error());
		while($r = $res->fetch_row()) $existing_names[] = $r[0];
		$res->close();
		$i = 1;
		while(in_array($newFolderName, $existing_names)){
			$newFolderName = L\NewFolder.' ('.$i.')';
			$i++;
		}
		/* end of find default folder name */

		mysqli_query_params('insert into tree (pid, user_id, `type`, `name`, cid, uid) values ($1, $2, $3, $4, $2, $2)', array($pid, $_SESSION['user']['id'], 1, $newFolderName)) or die(mysqli_query_error());
		$id = last_insert_id();
		SolrClient::runCron();
		return array('success' => true, 'path' => $path, 'data' => array( 'nid' => $id, 'pid' => $pid, 'name' => $newFolderName, 'system' => 0, 'type' => 1, 'subtype' => 0, 'iconCls' => 'icon-folder', 'cid' => $_SESSION['user']['id']) );
	}

	public function delete($paths){
		if(!is_array($paths)) $paths = array($paths);
		$deleted_ids = array();
		foreach($paths as $path){
			$id = explode('/', $path);
			$id = array_pop($id);
			if(!is_numeric($id)) return array('success' => false);
			mysqli_query_params('call p_delete_tree_node($1)', $id) or die(mysqli_query_error());
			$deleted_ids[] = intval($id);
		}
		/* TODO: check security access */
		$solr = new SolrClient;
		foreach($deleted_ids as $id) $solr->deleteId($id);
		unset($solr);
		return array('success' => true, 'ids' => $deleted_ids);
	}

	public function rename($p){
		$id = explode('/', $p->path);
		$id = array_pop($id);
		$p->name = trim($p->name);
		if(!is_numeric($id) || empty($p->name)) return array('success' => false);

		/* TODO: check security access */

		mysqli_query_params('update tree set name = $1 where id = $2', array($p->name, $id)) or die(mysqli_query_error());
		mysqli_query_params('update cases set name = $1 where id = $2', array($p->name, $id)) or die(mysqli_query_error());
		mysqli_query_params('update objects set custom_title = $1 where id = $2', array($p->name, $id)) or die(mysqli_query_error());
		mysqli_query_params('update files set name = $1 where id = $2', array($p->name, $id)) or die(mysqli_query_error());
		mysqli_query_params('update tasks set title = $1 where id = $2', array($p->name, $id)) or die(mysqli_query_error());
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
			$res = mysqli_query_params($sql, $p->data[$i]->id) or die(mysqli_query_error());
			if($r = $res->fetch_assoc()){
				$process_ids[] = $r['id'];
				if(!$p->confirmed && ($p->action !== 'copy') ){
					$type = ($p->action == 'shortcut') ? 2 : $r['type'];
					$sql = 'select id from tree where pid = $1 and system = $2 and type = $3 and subtype = $4 and name = $5';
					$res2 = mysqli_query_params($sql, array($p->pid, $r['system'], $type, $r['subtype'], $r['name'])) or die(mysqli_query_error());
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
					$res = mysqli_query_params($sql, array($id, $p->pid)) or die(mysqli_query_error());
					if($r = $res->fetch_row()) $newName = empty($r[1]) ? $r[0] : $this->getNewCopyName($p->pid, $r[0]);
					$res->close();

					mysqli_query_params('insert into tree(pid, user_id, `system`, `type`, subtype, tag_id, name, `date`, size, is_main, cfg, cid, cdate, uid, udate)
						select $2, user_id, 0, `type`, subtype, tag_id, $4, `date`, size, is_main, cfg, $3, CURRENT_TIMESTAMP, $3, CURRENT_TIMESTAMP from tree where id =$1', array($id, $p->pid, $_SESSION['user']['id'], $newName) ) or die(mysqli_query_error());
					$obj_id = last_insert_id();
					$type = 0;
					$res = mysqli_query_params('select `type` from tree where id = $1', $id) or die(mysqli_query_error());
					if($r = $res->fetch_row()) $type = $r[0];
					$res->close();
					switch($type){
						case 3://case
							mysqli_query_params('INSERT INTO cases (id, nr, name, closed, close_date, cid, uid, cdate, udate)'.
								' select $2, nr, $4, closed, close_date, $3, $3, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP from cases where id =$1', array($id, $obj_id, $_SESSION['user']['id'], $newName) ) or die(mysqli_query_error());
							break;
						
						case 4://case object
							mysqli_query_params('INSERT INTO objects(id, type_id, title, custom_title, template_id, date_start, date_end, author, iconCls, details, private_for_user, cid, uid, cdate, udate)'.
								' select $2, type_id, title, $4, template_id, date_start, date_end, author, iconCls, details, private_for_user, $3, $3, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP from objects where id =$1', array($id, $obj_id, $_SESSION['user']['id'], $newName) ) or die(mysqli_query_error());
							
							$duplicates = array(0 => 0);
							$sql = 'select id, pid, object_id, field_id from objects_duplicates where object_id = $1 order by id';
							$res = mysqli_query_params($sql, $id) or die(mysqli_query_error());
							while($r = $res->fetch_assoc()){
								mysqli_query_params('INSERT INTO objects_duplicates(pid, object_id, field_id)'.
								' values($1, $2, $3)', array($duplicates[$r['pid']], $r['object_id'], $r['field_id']) ) or die(mysqli_query_error());
								$duplicates[$r['id']] = last_insert_id();
							}
							$res->close();

							$sql = 'select field_id, duplicate_id, `value`, info, files, private_for_user from objects_data where object_id =$1';
							$res = mysqli_query_params($sql, $id) or die(mysqli_query_error());
							while($r = $res->fetch_assoc())
								mysqli_query_params('INSERT INTO objects_data(object_id, field_id, duplicate_id, `value`, info, files, private_for_user)'.
								' values($1, $2, $3, $4, $5, $6, $7)', array($obj_id, $r['field_id'], $duplicates[$r['duplicate_id']], $r['value'], $r['info'], $r['files'], $r['private_for_user']) ) or die(mysqli_query_error());
							$res->close();
							break;
						
						case 5://file
							mysqli_query_params('insert INTO files(id, content_id, `date`, `name`, title, cid, uid, cdate, udate)'.
								' select $2, content_id, `date`, $4, `title`, $3, $3, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP from files where id =$1', array($id, $obj_id, $_SESSION['user']['id'], $newName) ) or die(mysqli_query_error());
							break;
						
						case 6://task
						case 7://event
							mysqli_query_params('INSERT INTO tasks(id, title, date_start, date_end, `type`, privacy, responsible_party_id, responsible_user_ids, autoclose, description, parent_ids, child_ids, `time`, reminds, `status`, missed, completed, cid, uid, cdate, udate)'.
								' select $2, $4, date_start, date_end, `type`, privacy, responsible_party_id, responsible_user_ids, autoclose, description, parent_ids, child_ids, `time`, reminds, `status`, missed, completed, $3, $3, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP from tasks where id =$1', array($id, $obj_id, $_SESSION['user']['id'], $newName) ) or die(mysqli_query_error());
							break;
						
						case 8://message
							break;
					}
					if(!empty($existent_name)) mysqli_query_params('update tree set name = $2 where id = $1', array($obj_id, $this->getNewCopyName($p->pid, $existent_name) ) ) or die(mysqli_query_error());
				}

				break;
			case 'move':
				$res = mysqli_query_params('select pid from tree where id in ('.implode(',', $process_ids).')') or die(mysqli_query_error());
				while($r = $res->fetch_row()) $modified_pids[] = intval($r[0]);
				$res->close();
				mysqli_query_params('update tree set pid = $1 where id in ('.implode(',', $process_ids).')', $p->pid) or die(mysqli_query_error());
				break;
			case 'shortcut':
				mysqli_query_params('insert into tree (pid, `system`, `type`, `subtype`, target_id, `name`, cid) SELECT $1, 0, 2, 0, id, `name`, $2 from tree where id in ('.implode(',', $process_ids).')', array($p->pid, $_SESSION['user']['id'])) or die(mysqli_query_error());
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
			$res = mysqli_query_params($sql, array($pid, $newName)) or die(mysqli_query_error());
			if($r = $res->fetch_assoc()) $id = $r['id'];
			else $id = null;
			$res->close();
			$i++;
		}while(!empty($id));
		return $newName;
	}

	function saveFile($p){
		$files = new Files();

		/* clean previous unhandled uploads if any */
		$a = $files->getUploadParams();
		if( ($a !== false) && !empty( $a['files'] ) ){
			@unlink(CB_FILES_INCOMMING_PATH.$_SESSION['key']);
			$files->removeIncomingFiles($a['files']);
		}
		/* end of clean previous unhandled uploads if any */

		$F = &$_FILES;
		if(empty($p['pid'])) return Array('success' => false, 'msg' => L\Error_uploading_file);
		//TODO: SECURITY: check if current user has write access to folder
		
		if(empty($F)){ //update only file properties (no files were uploaded)
			$files->updateFileProperties($p);
			return array( 'success' => true );
		}

		//if( !$files->fileExists($p['pid']) ) return array('success' => false, 'msg' => L\TargetFolderDoesNotExist);
		$res = mysqli_query_params('select id from tree where id = $1', $p['pid']) or die(mysqli_query_error()); 
		if($r = $res->fetch_assoc()){ }else return array('success' => false, 'msg' => L\TargetFolderDoesNotExist);
		$res->close();


		/*checking if there is no upload error (for any type of upload: single, multiple, archive) */
		foreach($F as $fn => $f)
			if (!in_array($f['error'], Array(UPLOAD_ERR_OK, UPLOAD_ERR_NO_FILE))) return Array('success' => false, 'msg' => L\Error_uploading_file .': '.$f['error']);

		/* retreiving files list  */
		switch($p['uploadType']){
			case 'archive':
				$archiveFiles = array(); 
				foreach($F as $fk => $f){
					$files->extractUploadedArchive($F[$fk]);
					$archiveFiles = array_merge($archiveFiles, $F[$fk]);
				}
				$F = $archiveFiles;
				break;
			default: 
				$files->moveUploadedFilesToIncomming($F) or die('cannot move file to incomming '.CB_FILES_INCOMMING_PATH);
				break;
		}

		$p['existentFilenames'] = $files->getExistentFilenames( $F, $p['pid'] );
		$p['files'] = &$F;

		if( !empty($p['existentFilenames']) ){
			// store current state serialized in a local file in incomming folder 
			$files->saveUploadParams( $p );
			$allow_new_version = false;
			foreach($p['existentFilenames'] as $f){
				$mfvc = getMFVC($f['name']);
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
			// if( sizeof($p['existentFilenames']) == 1 )
			// 	return array(
			// 		'success' => false
			// 		,'type' => 'fileexists'
			// 		,'filename' => $p['existentFilenames'][0]['name']
			// 		,'suggestedFilename' => $p['existentFilenames'][0]['suggestedFilename']
			// 		,'allow_new_version' => $allow_new_version
			// 	);
			// return array( 'success' => false
			// 	,'type' => 'multiplefileexists'
			// 	,'count' => sizeof($p['existentFilenames'])
			// );
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
		$a['responce'] = $p->responce;
		switch($p->responce){
			case 'rename':
				$a['newName'] = $p->newName;
				//check if the new name does not also exist
				if(empty($a['responce'])) return array('success' => false, 'msg' => L\FilenameCannotBeEmpty);
				reset($a['files']);
				$k = key($a['files']);
				$a['files'][$k]['name'] = $a['newName'];
				if($files->fileExists( $a['pid'], $a['newName'])){
					$files->saveUploadParams($a);
					return array(
						'success' => false
						,'type' => 'filesexist'
						//,'filename' => $a['newName']
						,'allow_new_version' => (getMFVC($a['newName']) > 0)
						,'suggestedFilename' => $files->getAutoRenameFilename($a['pid'], $a['newName'])
						,'msg' => str_replace( '{filename}', '"'.$a['newName'].'"', L\FilenameExistsInTarget )
					);
				}
				// $files->storeFiles($a);
				// break;
			//newversion, replace, rename, autorename, cancel
			// case 'overwrite':
			// case 'overwriteall':
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
		$res = mysqli_query_params($sql, $p['id']) or die(mysqli_query_error());
		if($r = $res->fetch_assoc()) $p['pid'] = $r['pid'];
		$res->close();

		$rez = Array('success' => true, 'data' => array('id' => $p['id'], 'pid' => $p['pid']));
		
		$f = $_FILES['file'];
		if($f['error'] == UPLOAD_ERR_NO_FILE){
			mysqli_query_params('update files set `title` = $2, `date` = $3 where id = $1', array($p['id'], $p['title'], clientToMysqlDate($p['date']) ) ) or die(mysqli_query_error());
			return $rez;
		}
		if($f['error'] != UPLOAD_ERR_OK) return Array('success' => false, 'msg' => L\Error_uploading_file .': '.$f['error']);

		$p['files'] = &$_FILES;
		$p['responce'] = 'overwrite';
		$files = new Files();
		$files->storeFiles($p);
		SolrClient::runCron();
		return $rez;
	}

	public function toggleFavorite($p){
		
		$favoriteFolderId = $this->getFavoriteFolderId();
		$p->pid = $favoriteFolderId;
		$sql = 'select id from tree where pid = $1 and `type` = 2 and target_id = $2';
		$res = mysqli_query_params($sql, array($favoriteFolderId, $p->id)) or die(mysqli_query_error());
		if($r = $res->fetch_row()){
			mysqli_query_params('delete from tree where id = $1', array($r[0]) ) or die(mysqli_query_error());
			$res->close();
			$p->favorite = 0;
		}else{
			$res->close();
			/* get objects name */
			$name = 'Llink';
			$sql = 'select name from tree where id = $1';
			$res = mysqli_query_params($sql, array($p->id)) or die(mysqli_query_error());
			if($r = $res->fetch_row()){
				$name = $r[0];
			}
			$res->close();
			/* end of get objects name */
			mysqli_query_params('insert into tree (pid, user_id, `type`, name, target_id) values($1, $2, 2, $3, $4)', array($favoriteFolderId, $_SESSION['user']['id'], $name, $p->id) ) or die(mysqli_query_error());
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
		mysqli_query_params('update tree set cid = $1, uid = $1 where id in ('.$ids.') and `system` = 0', $_SESSION['user']['id']) or die(mysqli_query_error());
		mysqli_query_params('update cases set cid = $1, uid = $1  where id in ('.$ids.')', $_SESSION['user']['id']) or die(mysqli_query_error());
		mysqli_query_params('update objects set cid = $1, uid = $1  where id in ('.$ids.')', $_SESSION['user']['id']) or die(mysqli_query_error());
		mysqli_query_params('update files set cid = $1, uid = $1 where id in ('.$ids.')', $_SESSION['user']['id']) or die(mysqli_query_error());
		mysqli_query_params('update tasks set cid = $1, uid = $1  where id in ('.$ids.')', $_SESSION['user']['id']) or die(mysqli_query_error());		
		SolrClient::runCron();
		return $rez;
	}
	public function getPath($id){
		$rez = array('success' => false);
		if(!is_numeric($id)) return $rez;
		$sql = 'select f_get_tree_ids_path(case when `type` = 2 then target_id else id end) from tree where id = $1';
		$res = mysqli_query_params($sql, $id) or die(mysqli_query_error());
		if($r = $res->fetch_row()){
			$rez = array('success' => true, 'path' => $r[0]);
		}
		$res->close();
		return $rez;
	}
	public function isChildOf($id, $pid){
		$rez = false;
		$sql = 'SELECT f_get_tree_ids_path($1)';
		$res = mysqli_query_params( $sql, $id ) or die(mysqli_query_error());
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
		$res = mysqli_query_params($sql, array()) or die(mysqli_query_error());
		if($r = $res->fetch_row()) $id = $r[0];
		$res->close();
		return $id;
	}
	public function getRootProperties($id){
		$rez = array('success' => true, 'data' => array());
		$sql = 'select id `nid`, `system`, `type`, `subtype`, `name` from tree where id = $1';
		$res = mysqli_query_params($sql, $id) or die(mysqli_query_error());
		if($r = $res->fetch_assoc()) $rez['data'] = $r;
		$res->close();
		return $rez;
	}
	static function getFavoriteFolderId(){
		$id = null;
		$sql = 'select id from tree where pid is null and `system` = 1 and `type` = 1 and subtype = 2 and user_id = $1';
		$res = mysqli_query_params($sql, array($_SESSION['user']['id'])) or die(mysqli_query_error());
		if($r = $res->fetch_row()) $id = $r[0];
		$res->close();
		return $id;
	}
}