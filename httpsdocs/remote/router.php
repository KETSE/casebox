<?php
namespace ExtDirect;

use CB\Config;

register_shutdown_function('ExtDirect\\extDirectShutdownFunction');

$path = dirname(__FILE__) . DIRECTORY_SEPARATOR;

require_once dirname($path) . DIRECTORY_SEPARATOR . 'init.php';
require $path . 'config.php';

$isForm = false;
$isUpload = false;

header('Content-Type: application/json; charset=UTF-8');

if (isset($HTTP_RAW_POST_DATA)) {
    $data = json_decode($HTTP_RAW_POST_DATA, true);
} elseif (isset($_POST['extAction'])) {
    // form post
    $isForm = true;
    $isUpload = ($_POST['extUpload'] == 'true');
    $data = array(
        'action' => $_POST['extAction']
        ,'method' => $_POST['extMethod']
        ,'tid' => isset($_POST['extTID']) ? intval($_POST['extTID']) : null // not set for upload
        ,'data' => array($_POST, $_FILES)
    );
} else {
    die('Invalid request.');
}

\CB\Cache::set('ExtDirectData', $data);

function doRpc($cdata)
{
    $API = \CB\Cache::get('ExtDirectAPI');

    if (!\CB\User::isLoged() && ( ($cdata['action'] != 'User') || ($cdata['method'] != 'login') )) {
        return array(
            array(
                'type' => 'exception'
                ,'name' => 'login'
                ,'tid' => $cdata['tid']
                ,'action' => $cdata['action']
                ,'method' => $cdata['method']
                ,'result' => array('success' => false)
            )
        );
    }

    try {
        if (!isset($API[$cdata['action']])) {
            throw new \Exception('Call to undefined action: ' . $cdata['action']);
        }

        $action = $cdata['action'];
        $a = $API[$action];

        doAroundCalls($a['before'], $cdata);

        $method = $cdata['method'];
        $mdef = $a['methods'][$method];
        if (!$mdef) {
            throw new \Exception("Call to undefined method: $method on action $action");
        }
        doAroundCalls($mdef['before'], $cdata);

        $r = array(
            'type'=>'rpc',
            'tid'=>$cdata['tid'],
            'action'=>$action,
            'method'=>$method
        );

        $action = str_replace('_', '\\', $action);
        $o = new $action();

        $params = isset($cdata['data']) && is_array($cdata['data']) ? $cdata['data'] : array();

        $r['result'] = call_user_func_array(array($o, $method), $params);

        doAroundCalls($mdef['after'], $cdata, $r);
        doAroundCalls($a['after'], $cdata, $r);

    } catch (\Exception $e) {
        $r['type'] = 'exception';
        $r['result'] = array(
            'success' => false,
            'msg' => $e->getMessage()
        );

        if (\CB\isDebugHost()) {
            $r['where'] = $e->getTraceAsString();
        }

        //notify admin
        @mail(
            Config::get('ADMIN_EMAIL'),
            'Remote router exception on ' . Config::get('core_url'),
            var_export($r, true),
            'From: '.Config::get('SENDER_EMAIL'). "\r\n"
        );

    }

    return $r;
}

function doAroundCalls(&$fns, &$cdata, &$returnData = null)
{
    if (!$fns) {
        return;
    }
    if (is_array($fns)) {
        foreach ($fns as $f) {
            $f($cdata, $returnData);
        }
    } else {
        $fns($cdata, $returnData);
    }
}

function sanitizeParams(&$cdata)
{
    $cdata['action'] = preg_replace('/[^a-z_\\\\]+/i', '', strip_tags($cdata['action']));
    $cdata['method'] = preg_replace('/[^a-z]+/i', '', strip_tags($cdata['method']));
    $cdata['tid'] = intval(strip_tags($cdata['tid']));

    return $cdata;
}

$response = null;
if (empty($data['action'])) {
    $response = array();
    foreach ($data as $d) {
        $response[] = doRpc(sanitizeParams($d));
    }
} else {
    $response = doRpc(sanitizeParams($data));
}
if ($isForm && $isUpload) {
    header('Content-Type: text/html; charset=UTF-8');
    echo '<html><body><textarea>';
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    echo '</textarea></body></html>';
} else {
    header('X-Frame-Options: deny');

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}

/**
 * catch server side errors and return json encoded exception
 * @return void
 */
function extDirectShutdownFunction()
{
    $data = \CB\Cache::get('ExtDirectData');

    $error = error_get_last();

    if (in_array($error['type'], array(1, 4))) {
        $data['type'] = 'exception';
        $data['result'] = array('success' => false);
        $data['msg'] = 'Internal server error.';

        if (\CB\isDebugHost()) {
            $data['msg'] = $error['message'];
            $data['where'] = print_r(debug_backtrace(false), true);
        }

        //notify admin
        @mail(
            Config::get('ADMIN_EMAIL'),
            'Remote router error on ' . Config::get('core_url'),
            var_export($data, true),
            'From: '.Config::get('SENDER_EMAIL'). "\r\n"
        );

        echo json_encode(
            $data,
            JSON_UNESCAPED_UNICODE
        );

    }
}
