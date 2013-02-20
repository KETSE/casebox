<?php
/* 	custom config per core for minify
	the casebox will try to load custom <core>_css and <core>_js groups per core if defined
*/

return array(
	'dev_css' => array(
		'//cores/dev/css/dev.css'
	)
	,'dev_js' => array(
		'//cores/dev/js/dev.customizeObjects.js'
		,'//cores/dev/js/test.js'
	)
	,'dev_api' => array(
		'customizeObjects' => array(
			'methods' => array(
				'getCustomInfo' => array('len' => 1)
			)
		)
	)
);

?>