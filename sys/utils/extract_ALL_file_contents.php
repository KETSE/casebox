#!/usr/bin/php
<?php
include '../crons/crons_init.php';
foreach($CB_cores as $core){
	mysqli_query_params('use `'.$core['db_name'].'`') or die(mysqli_query_error());
	echo "\n\r Processing core \"".$core['db_name']."\"\n\r";

	$rez = Array('Total' => 0, 'Processed' =>0, 'Not found'=> 0/*, 'Processed List' => Array(), 'Not found List' => Array()/**/);
	$processed_list = Array();
	$not_fount_list = Array();
	
	$where = 'where skip_parsing = 0';
	
	$res = mysqli_query_params('select id, path, `type`, pages from files_content '.$where) or die(mysqli_query_error());//where name like \'%.pdf\
	while($r = $res->fetch_assoc()){
		$filename = PROJ_FILES_PATH.$core['name'].DIRECTORY_SEPARATOR.$r['path'].DIRECTORY_SEPARATOR.$r['id'];
		echo "\nFile: $filename (".$r['type'].") ";
		if(file_exists($filename)){
			$skip_parsing = 0;
			$pages = $r['pages'];
			if( substr($r['type'], 0, 5) != 'image' ){
				if( !file_exists($filename.'.gz') ){
					echo ' processed';
					$fc = shell_exec('java -Dfile.encoding=UTF8 -jar '.TIKA_APP.' -t "'.$filename.'"');
					$fc = mb_convert_encoding($fc, mb_detect_encoding($fc), 'UTF-8');
					$fc = str_replace(array("\n", "\r", "\t"), ' ', $fc);
					$fc = trim($fc);
					if(!empty($fc)){
						$fc = gzcompress($fc, 9);
						file_put_contents($filename.'.gz', $fc);
					}else $skip_parsing = 1;
					$sr = shell_exec('java -Dfile.encoding=UTF8 -jar '.TIKA_APP.' -m "'.$filename.'"');
					$sr = mb_convert_encoding($sr, mb_detect_encoding($sr), 'UTF-8');
					preg_match('/Pages:\s+([0-9]+)/i', $sr, $matches);
					$pages = @$matches[1];
				}
			}else{
				$skip_parsing = 1;
				echo ' skipped';
			}
			mysqli_query_params('update files_content set parse_status = 1, pages = $2, skip_parsing = $3 where id = $1', Array($r['id'], $pages, $skip_parsing)) or die('error: '.mysqli_query_error());
			$rez['Processed'] = $rez['Processed'] +1;
		}else{
			$rez['Not found'] = $rez['Not found']+1;
		}
		echo '.';
	}
	$res->close();
	$rez['Total'] = $rez['Processed'] + $rez['Not found'];
	print_r($rez);
	echo "Ok\n";
}
?>