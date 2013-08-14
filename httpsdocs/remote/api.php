<?php
namespace ExtDirect;

require_once '../init.php';
require_once 'config.php';

header('Content-Type: text/javascript');

// convert API config to Ext.Direct spec
$actions = array();
foreach ($API as $aname => &$a) {
    $methods = array();
    foreach ($a['methods'] as $mname => &$m) {
        $md = array(
            'name'=>$mname,
            'len'=>$m['len']
        );
        if (isset($m['formHandler']) && $m['formHandler']) {
            $md['formHandler'] = true;
        }
        $methods[] = $md;
    }
    $actions[$aname] = $methods;
}

$cfg = array(
    'url'=>'remote/router.php'
    ,'type'=>'remoting'
    ,'enableBuffer' => true
    ,'actions'=>$actions
);

echo 'Ext.app.REMOTING_API = ';

echo json_encode($cfg);
echo ';';
