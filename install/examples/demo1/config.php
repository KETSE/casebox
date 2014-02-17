<?php
/* 	custom config per core for minify
    the casebox will try to load custom <core>_css and <core>_js groups per core if defined
*/

return array(
    'demo1_css' => array(
        '//cores/demo1/css/demo1.css'
    )
    ,'demo1_js' => array(
        '//cores/demo1/js/demo1.CustomizeObjects.js'
        ,'//cores/demo1/js/test.js'
    )
    ,'demo1_api' => array(
        'demo1_CustomizeObjects' => array(
            'methods' => array(
                'getCustomInfo' => array('len' => 1)
            )
        )
    )
);
