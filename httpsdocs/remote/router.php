<?php
	require_once('../init.php');
	require_once('../lib/DB.php');
	connect2DB();
	require('config.php');
	require('classes/Log.php');
	require('classes/Security.php');

class BogusAction {
	public $action;
	public $method;
	public $data;
	public $tid;
}

$isForm = false;
$isUpload = false;
header('Content-Type: application/json; charset=UTF-8');
if(isset($HTTP_RAW_POST_DATA)){
	//header('Content-Type: application/json; charset=UTF-8');
	$data = json_decode($HTTP_RAW_POST_DATA);
}else if(isset($_POST['extAction'])){ // form post
	$isForm = true;
	$isUpload = $_POST['extUpload'] == 'true';
	$data = new BogusAction();
	$data->action = $_POST['extAction'];
	$data->method = $_POST['extMethod'];
	$data->tid = isset($_POST['extTID']) ? $_POST['extTID'] : null; // not set for upload
	$data->data = array($_POST, $_FILES);
}else die('Invalid request.');


function doRpc($cdata){
	global $API;

	if(!is_loged() && (($cdata->action != 'Auth') || ($cdata->method != 'login'))){
		return array(
			array(
				'type' => 'exception'
				,'name' => 'login'
				,'tid' => $cdata->tid
				,'action' => $cdata->action
				,'method' => $cdata->method
				,'result' => array('success' => false)
			)
		);
	  }
	
	try {
		if(!isset($API[$cdata->action])) throw new Exception('Call to undefined action: ' . $cdata->action);

		$action = $cdata->action;
		$a = $API[$action];

		doAroundCalls($a['before'], $cdata);

		$method = $cdata->method;
		$mdef = $a['methods'][$method];
		if(!$mdef){
			throw new Exception("Call to undefined method: $method on action $action");
		}
		doAroundCalls($mdef['before'], $cdata);

		$r = array(
			'type'=>'rpc',
			'tid'=>$cdata->tid,
			'action'=>$action,
			'method'=>$method
		);

		require_once("classes/$action.php");
		$o = new $action();

		$params = isset($cdata->data) && is_array($cdata->data) ? $cdata->data : array();

		$r['result'] = call_user_func_array(array($o, $method), $params);

		doAroundCalls($mdef['after'], $cdata, $r);
		doAroundCalls($a['after'], $cdata, $r);
	}
	catch(Exception $e){
		$r['type'] = 'exception';
		$r['result'] = array('success' => false);
		$r['msg'] = $e->getMessage();
		if(is_debug_host()){
			$r['where'] = $e->getTraceAsString();
		}//else $r['message'] = 'Error';
	}
	return $r;
}

function doAroundCalls(&$fns, &$cdata, &$returnData=null){
	if(!$fns) return;
	if(is_array($fns)) foreach($fns as $f) $f($cdata, $returnData);
	else $fns($cdata, $returnData);
}

$response = null;
if(is_array($data)){
	$response = array();
	foreach($data as $d){
		$response[] = doRpc($d);
	}
}else{
	$response = doRpc($data);
}
if($isForm && $isUpload){
	header('Content-Type: text/html; charset=UTF-8');
	echo '<html><body><textarea>';
	echo json_encode($response);
	echo '</textarea></body></html>';
}else echo json_encode($response);
