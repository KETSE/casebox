<?php
//shell_exec(CB_PDF2SWF_PATH.'pdf2swf -B '.CB_PDF2SWF_PATH.'rfxview.swf "'.$fn.'" -o "'.$swf_fn.'"');
require_once 'preview_extractor.php';
class preview_extractor_pdf extends preview_extractor{

	function execute(){
		$sql = 'select count(*) from file_previews where `status` = 2 and `group` = \'pdf\'';
		$res = mysqli_query_params($sql) or die(mysqli_query_error());
		$processing = false;
		if($r = $res->fetch_row()) $processing = ($r[0] > 0);
		$res->close();
		if($processing) exit(0);

		$sql = 'select c.id `content_id`, c.path, p.status, (select name from files f where f.content_id = c.id limit 1) `name` '.
			'from file_previews p '.
			'left join files_content c on p.id = c.id '.
			//'left join files f on f.content_id = p.id '.
			'where p.`status` = 1 and `group` = \'pdf\' order by p.cdate';
		$res = mysqli_query_params($sql) or die(mysqli_query_error());
		while($r = $res->fetch_assoc()){
			// if(empty($r['id'])){
			// 	$this->removeFromQueue($r['content_id']);
			// 	continue;
			// }
			mysqli_query_params('update file_previews set `status` = 2 where id = $1', $r['content_id']) or die(mysqli_query_error());
			$fn = CB_FILES_PATH.$r['path'].DIRECTORY_SEPARATOR.$r['content_id'];
			$nfn = CB_FILES_PREVIEW_PATH.$r['content_id'].'_.swf';
			$preview_filename = CB_FILES_PREVIEW_PATH.$r['content_id'].'_.html';
			$log = CB_PDF2SWF_PATH.'pdf2swf -B '.CB_LIB_DIR.'rfxview.swf "'.$fn.'" -o "'.$nfn.'"'."\n";
			$log .= shell_exec(CB_PDF2SWF_PATH.'pdf2swf  "'.$fn.'" -o "'.$nfn.'"'); // > /tmp/pdf.log//-B '.CB_LIB_DIR.'rfxview.swf
			$log .= shell_exec(CB_PDF2SWF_PATH.'swfcombine -X 596 -Y 841 '.CB_LIB_DIR.'rfxview.swf viewport="'.$nfn.'" -o "'.$nfn.'"'); // > /tmp/pdf.log
			//sudo -u apache swfcombine  -X 596 -Y 841 "/var/www/vhosts/casebox.org/subdomains/stratelit2/httpsdocs/lib/rfxview.swf" viewport="/var/www/vhosts/casebox.org/subdomains/stratelit2/casebox/files/casebox2_osji/preview/86220_.swf" -o "/var/www/vhosts/casebox.org/subdomains/stratelit2/casebox/files/casebox2_osji/preview/86220_.swf"

			file_put_contents('/tmp/pdf.log', $log, FILE_APPEND);
			file_put_contents( $preview_filename, '<OBJECT CLASSID="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" WIDTH="100%" CODEBASE="http://active.macromedia.com/flash5/cabs/swflash.cab#version=8,0,0,0">'.
						'<PARAM NAME="MOVIE" VALUE="preview/'.$r['content_id'].'_.swf">'.
						'<PARAM NAME="PLAY" VALUE="false">'.
						'<PARAM NAME="LOOP" VALUE="false">'.
						'<PARAM NAME="QUALITY" VALUE="high">'.
						'<PARAM NAME="ALLOWSCRIPTACCESS" VALUE="always">'.
						'<EMBED SRC="preview/'.$r['content_id'].'_.swf" WIDTH="100%" HEIGHT="100%" QUALITY="high" TYPE="application/x-shockwave-flash" ALLOWSCRIPTACCESS="always" PLUGINSPAGE="http://get.adobe.com/flashplayer/">'.
						'</EMBED>'.
						'</OBJECT>' );
			mysqli_query_params('update file_previews set `status` = 0, filename = $2, `size` = $3 where id = $1', array($r['content_id'], $r['content_id'].'_.html', filesize($preview_filename) ) ) or die(mysqli_query_error());
			$res->close();
			$res = mysqli_query_params($sql) or die(mysqli_query_error());
		}
		$res->close();
	}
}

$extractor = new preview_extractor_pdf();
$extractor->execute();

?>