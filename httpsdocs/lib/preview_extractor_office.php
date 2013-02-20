<?php
include 'preview_extractor.php';
class preview_extractor_office extends preview_extractor{

	function execute(){
		$sql = 'select count(*) from file_previews where `status` = 2 and `group` = \'office\'';
		$res = mysqli_query_params($sql) or die(mysqli_query_error());
		$processing = false;
		if($r = $res->fetch_row()) $processing = ($r[0] > 0);
		$res->close();
		if($processing) exit(0);

		$sql = 'select c.id `content_id`, c.path, p.status, (select name from files f where f.content_id = c.id limit 1) `name` '.
			'from file_previews p '.
			'left join files_content c on p.id = c.id '.
			'where p.`status` = 1 and `group` = \'office\' order by p.cdate';
		$res = mysqli_query_params($sql) or die(mysqli_query_error());
		while($r = $res->fetch_assoc()){
			mysqli_query_params('update file_previews set `status` = 2 where id = $1', $r['content_id']) or die(mysqli_query_error());
			$ext = explode('.', $r['name']);
			$ext = array_pop($ext);
			$ext = strtolower($ext);
			$fn = CB_FILES_PATH.$r['path'].DIRECTORY_SEPARATOR.$r['content_id'];
			$nfn = CB_FILES_PREVIEW_PATH.$r['content_id'].'_.'.$ext;
			$pfn = CB_FILES_PREVIEW_PATH.$r['content_id'].'_.html';

			copy($fn, $nfn);
			file_put_contents($pfn, '');
			$cmd = '/usr/local/sbin/unoconv -v -f html -o '.$pfn.' '.$nfn;
			exec($cmd);
			unlink($nfn);
			file_put_contents( $pfn, '<div style="padding: 5px">'.$this->purify(file_get_contents($pfn), array('URI.Base' => '/preview/', 'URI.MakeAbsolute' => true)).'</div>' );
			
			mysqli_query_params('update file_previews set `status` = 0, `filename` = $2, `size` = $3 where id = $1', array($r['content_id'], $r['content_id'].'_.html', filesize($pfn) ) )  or die(mysqli_query_error());
			$res->close();
			$res = mysqli_query_params($sql) or die(mysqli_query_error());
		}
		$res->close();
	}
}

$extractor = new preview_extractor_office();
$extractor->execute();

?>