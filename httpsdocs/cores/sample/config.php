<?php
/* 	custom config per core for minify
    the casebox will try to load custom <core>_css and <core>_js groups per core if defined
*/

return array(
    'sample_css' => array(
        '//cores/sample/css/sample.css'
    )
    ,'sample_js' => array(
        '//cores/sample/js/sample.CustomizeObjects.js'
        ,'//cores/sample/js/test.js'
    )
    ,'sample_api' => array(
        'sample_CustomizeObjects' => array(
            'methods' => array(
                'getCustomInfo' => array('len' => 1)
            )
        )
    )
);
