<?php
namespace CB;

require_once '../config.php';

session_start();
$_SESSION['user'] = array('id' => 1);// root

$api = new Api();
$request = $api->processRequest();
$rv = $request->getRequestVars();

if (empty($rv['action']) || empty($rv['method'])) {
    $api->sendResponse(501, '', 'text/html');
}

$action = $rv['action'];
$method = $rv['method'];
$data = @$rv['data'];
//sanitize $action and $method
$action = '\\CB\\Api\\'.preg_replace('/[^a-z_\\\\]+/i', '', strip_tags($action));
$method = preg_replace('/[^a-z]+/i', '', strip_tags($method));

$result = array( 'success' => 'false' );

try {
    $o = new $action();
    $result = call_user_func_array(array($o, $method), array($data));

} catch (Exception $e) {
    $api->sendResponse(501, '', 'text/html');
    exit();
}

$api->sendResponse(200, json_encode($result), 'application/json');
