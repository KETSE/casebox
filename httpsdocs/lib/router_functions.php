<?php
namespace ExtDirect;

use CB\Config;
use CB\Util;

function doRpc($cdata)
{
    $API = \CB\Cache::get('ExtDirectAPI');

    if (!\CB\User::isLoged() && ( ($cdata['action'] != 'User') || ($cdata['method'] != 'login') ) && !(php_sapi_name() == "cli") ) {
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

        if (\CB\IS_DEBUG_HOST) {
            $r['where'] = $e->getTraceAsString();
        }

        //notify admin
       if (!(php_sapi_name() == "cli")) {
           @mail(
            Config::get('ADMIN_EMAIL'),
            'Remote router exception on ' . Config::get('core_url'),
            var_export($r, true),
            'From: '.Config::get('SENDER_EMAIL'). "\r\n"
       );
       
       }

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

