<?php
	/*
	selecting node properties from the tree
	comparing last preview access time with node update time. Generate preview if needed and store it in cache
	checking if preview is available and return it
	 */
	if(empty($_GET['f'])) exit(0);
	require_once 'init.php';
	if(!is_loged()){
		echo 'Session expired. Please login.';
		exit(0);
	}
	$f = $_GET['f'];
	$f = explode('.', $f);
	$a = array_shift($f);
	@list($id, $version_id) = explode('_', $a);
	$ext = array_pop($f);

	//TODO: check access with security model
	if($ext !== 'html'){//this will provide other files (images, swfs)
		$f = realpath(CB_FILES_PREVIEW_PATH.$_GET['f']);
		if(file_exists($f)){
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			header('Content-type: '.finfo_file($finfo, $f));
			echo file_get_contents($f);
		}
		exit(0);
	}	
	if(!is_numeric($id)) exit(0);

	$sql = 'SELECT t.id, t.pid, t.type, t.subtype, t.name, t.updated FROM tree t WHERE t.id = $1';
	$res = mysqli_query_params($sql, $id) or die(mysqli_query_error());
	if($r = $res->fetch_assoc()) $f = $r;
	$res->close();
	if(!is_array($f) || empty($f)) exit(0); //tree element does not exist
	$preview = array();
	switch($f['type']){
		case 3:
		case 4:
		case 8:
			$o = new Objects();
			echo $o->getPreview($id);
			break;
		case 5:
			$sql = 'SELECT p.filename FROM files f join file_previews p on f.content_id = p.id WHERE f.id = $1';
			if(!empty($version_id)) $sql = 'SELECT p.filename FROM files_versions f join file_previews p on f.content_id = p.id WHERE f.file_id = $1 and f.id = $2';
			$res = mysqli_query_params($sql, array($id, $version_id)) or die(mysqli_query_error());
			if($r = $res->fetch_assoc())
				if(!empty($r['filename']) && file_exists(CB_FILES_PREVIEW_PATH.$r['filename']) ) $preview = $r;
			$res->close();
			if(empty($preview)) $preview = Files::generatePreview($id, $version_id);
			
			if(!empty($preview['processing'])) echo '&#160';
			else{
				$top = '';
				$tmp = Tasks::getAxtiveTasksBlockForPreview($id);
				if(!empty($tmp)) $top = '<div class="obj-preview-h pt10">'.L\ActiveTasks.'</div>'.$tmp;
				if(!empty($top)) echo '<div class="p10">'.$top.'</div><hr />';
				
				if(!empty($preview['filename'])){
					$fn = CB_FILES_PREVIEW_PATH.$preview['filename'];
					if(file_exists($fn)){
						echo file_get_contents($fn);
						$res = mysqli_query_params('update file_previews set ladate = CURRENT_TIMESTAMP where id = $1', $id) or die(mysqli_query_error());
					}
				}elseif(!empty($preview['html'])) echo $preview['html'];
			}
			break;
		case 6:
		case 7:
			$o = new Tasks();
			echo $o->getPreview($id);
			break;
	}
?>