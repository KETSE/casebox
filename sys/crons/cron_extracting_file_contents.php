#!/usr/bin/php
<?php
$cron_id = 'extract_file_contents';
//$execution_skip_times = 1; //default is 1

include 'crons_init.php';
foreach($CB_cores as $core){
	$res = mysqli_query_params('use `'.$core['db_name'].'`');
	if(!$res) continue;
	echo "\nProcessing core \"".$core['db_name']."\" ...";

	$cd = prepare_cron($cron_id);
	if(!$cd['success']) exit(1);

	$rez = Array('Total' => 0, 'Processed' =>0, 'Not found'=> 0, 'Processed List' => Array(), 'Not found List' => Array());
	$processed_list = Array();
	$not_fount_list = Array();
	$where = 'skip_parsing = 0 and (parse_status is null)';
	
	$res = mysqli_query_params('select id, path, `type`, pages from files_content where '.$where) or die('error1'); //and name like \'%.pdf\'
	while($r = $res->fetch_assoc()){
		$filename = PROJ_FILES_PATH.$core['name'].DIRECTORY_SEPARATOR.$r['path'].DIRECTORY_SEPARATOR.$r['id'];
		echo "\nFile: $filename (".$r['type'].") ";
		if(file_exists($filename)){
			$skip_parsing = 0;
			$pages = $r['pages'];
			if( substr($r['type'], 0, 5) != 'image'){
				if( !file_exists($filename.'.gz') ){ 
					echo "\nnot image processing content ...";
					$fc = shell_exec('java -Dfile.encoding=UTF8 -jar '.TIKA_APP.' -t "'.$filename.'"');
					$fc = mb_convert_encoding($fc, mb_detect_encoding($fc), 'UTF-8');
					$fc = str_replace(array("\n", "\r", "\t"), ' ', $fc);
					$fc = trim($fc);
					if(!empty($fc)){
						echo "... size: ".strlen($fc)."\n";
						$fc = gzcompress($fc, 9);
						file_put_contents($filename.'.gz', $fc);
					}else $skip_parsing = 1;
					echo "\nExtracting meta ...";
					$sr = shell_exec('java -Dfile.encoding=UTF8 -jar '.TIKA_APP.' -m "'.$filename.'"');
					$sr = mb_convert_encoding($sr, mb_detect_encoding($sr), 'UTF-8');
					preg_match('/Pages:\s+([0-9]+)/i', $sr, $matches);
					$pages = empty($matches[1]) ? null : $matches[1];
					echo " pages: $pages";
				}
			}else $skip_parsing = 1;
			echo "\nupdating db status of this file";
			mysqli_query_params('update files_content set parse_status = 1, pages = $2, skip_parsing = $3 where id = $1', Array($r['id'], $pages, $skip_parsing)) or die('error2');
			$rez['Processed'] = $rez['Processed'] +1;
			$rez['Processed List'][] =  $filename;
		}else{
			echo " - Not found.";
			$rez['Not found'] = $rez['Not found']+1;
			$rez['Not found List'][] = $filename;
		}
		echo '.';
	}
	$res->close();
	$rez['Total'] = $rez['Processed'] + $rez['Not found'];
	mysqli_query_params('update crons set last_end_time = CURRENT_TIMESTAMP, execution_info = $2 where cron_id = $1', array($cron_id, json_encode($rez)) ) or die(mysqli_query_error());
	echo " Done";
}
include 'cron_solr_update_tree.php';
