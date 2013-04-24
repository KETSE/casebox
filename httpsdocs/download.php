<?php
	namespace CB;
	header('Content-Type: text/plain; charset=UTF-8');
	if(empty($_GET['id'])) die();

	require_once('init.php');
	DB\connect();

	$ids = explode(',', $_GET['id']);
	$ids = array_filter($ids, 'is_numeric');
	if(empty($ids)) exit(0);

	$sql = 'select f.id, f.content_id, c.path, f.name, c.`type`, c.size from files f left join files_content c on f.content_id = c.id where f.id in ('.implode(',', $ids).')';
	if(!empty($_GET['v']) && is_numeric($_GET['v']))
		$sql = 'select f.id, f.content_id, c.path, f.name, c.`type`, c.size from files_versions f left join files_content c on f.content_id = c.id where f.id = '.intval($_GET['v']);
	if(empty($_GET['z']) || ($_GET['z'] != 1)){
		$res = DB\mysqli_query_params($sql) or die( DB\mysqli_query_error() );
		if($r = $res->fetch_assoc()){
  			header('Content-Description: File Transfer');
  			header('Content-Type: '.$r['type'].'; charset=UTF-8');
   			if(!isset($_GET['pw'])) header('Content-Disposition: attachment; filename="'.$r['name'].'"');
   			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
    			header('Content-Length: '.$r['size']);
			readfile(FILES_PATH.$r['path'].DIRECTORY_SEPARATOR.$r['content_id']);
			Log::add(Array('action_type' => 14, 'file_id' => $r['id']));
			
		}
		$res->close();
		exit(0);
	}else{
		$archive_name = $_SERVER['SERVER_NAME'].'_'.date('Y-m-d_Hi').'.zip';
		$files = array();
		if(!empty($ids)){
			$res = DB\mysqli_query_params($sql) or die( DB\mysqli_query_error() );
			while($r = $res->fetch_assoc()) $files[] = $r;
			$res->close();
			if(sizeof($files) == 1) $archive_name = $files[0]['name'].'_'.date('Y-m-d_Hi').'.zip';
			
			$zip = new ZipArchive();
			$tmp_name = tempnam(sys_get_temp_dir(), 'cb_arch');
			if ($zip->open($tmp_name, ZIPARCHIVE::CREATE)!==TRUE) exit("cannot create archive\n");
			foreach($files as $f) $zip->addFile(FILES_PATH.$f['path'].DIRECTORY_SEPARATOR.$f['content_id'], $f['name']);
			$zip->close();
			header('Content-Type: application/zip; charset=UTF-8');
			header('Content-Disposition: attachment; filename="'.$archive_name.'"');
			header('Content-Length: '.filesize($tmp_name));
			readfile($tmp_name);
			exit(0);
		}
	}
	header('Location: /');
?>