<?php
	ini_set('max_execution_time', 0);
  ini_set('memory_limit', '-1');
class preview_extractor{
	function preview_extractor(){
		global $argv;
		if(empty($_SERVER['SERVER_NAME'])){
			if(empty($argv[1])) die('no core is passed');
			$t = explode('_', $argv[1]);
			$_SERVER['SERVER_NAME'] = array_pop($t).'.dummy.com';
			$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		}
		require_once dirname(__FILE__).'/../config.php';
		require_once 'DB.php';
		connect2DB();
	}
	function removeFromQueue($id){
		mysqli_query_params('delete from file_previews where id = $1', $id) or die(mysqli_query_error());
	}
	
	function purify($html, $options = array()){
		if(empty($html)) return '';
		require_once CB_HTML_PURIFIER;
		require_once 'HTMLPurifier.func.php';

		$cs = mb_detect_encoding($html);
		file_put_contents('html.log', $html);
		echo 'detected encoding: '.$cs."\n";
		if(empty($cs)) $cs = 'UTF-8';
		$cs = @iconv($cs, 'UTF-8', $html);
		if(empty($cs)) $cs = $html;
		
		$config = HTMLPurifier_Config::createDefault();
		$config->set('AutoFormat.AutoParagraph', false);
		$config->set('AutoFormat.RemoveEmpty.RemoveNbsp', true);
		//$config->set('AutoFormat.RemoveEmpty', true);//slows down htmls parsing 
		//$config->set('AutoFormat.RemoveSpansWithoutAttributes', true); //medium slows down htmls parsing 
		$config->set('HTML.ForbiddenElements', array('head'));
		$config->set('HTML.SafeIframe', true);
		$config->set('HTML.TargetBlank', true);
		$config->set('URI.DefaultScheme', 'https');
		$config->set('Attr.EnableID', true);
		if(!empty($options))
			foreach($options as $k => $v) $config->set($k, $v);
		$purifier = new HTMLPurifier($config);
		$html = $purifier->purify($cs);/**/
		$html = str_replace('/preview/#', '#', $html);
		return $html;
	}
}

?>