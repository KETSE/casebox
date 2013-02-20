<?php
  include 'sys_config.php';

	// UNIX: '/usr/local/sbin/unoconv'
	define('CB_UNOCONV', '"c:\\Program Files (x86)\\LibreOffice 4.0\\program\\python.exe" c:\\opt\\unoconv\\unoconv');
	define('CB_PDF2SWF_PATH', file_exists('d:\\soft\\SWFTools\\') ? 'd:\\soft\\SWFTools\\' : '/usr/local/bin/');
