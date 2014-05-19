<?php
namespace Demosrc;

class Tree
{
    public function onTreeInitialize(&$p)
    {
        $rootId = '0';
        $rootVariations = array(
            $rootId
            ,'/'.$rootId
            ,$rootId.'/'
            ,'/'.$rootId.'/'
        );
        if (in_array($p['params']['path'], $rootVariations)) {
            // echo $p['params']['path'];
            // print_r($rootVariations);
            unset($p['plugins']['Dbnode']);
        }
    }
}
