<?php
/**
*	initialization file
*	@author Ţurcanu Vitalie <vitalie.turcanu@gmail.com>
*	@access private
*	@package CaseBox
*	@copyright Copyright (c) 2010, CaseBox
**/
namespace CB;

require_once dirname(__FILE__) . '/config.php';

//Starting Session
$sessionHandler = new Session();
session_set_save_handler($sessionHandler, true);
session_start();

// check if loged in
# simple hack to call init.php from another script without a redirect to login.
if (!@$webDAVMode && !(php_sapi_name() == "cli")) {
    if (!in_array(@$_GET['command'], array('login', 'recover')) && !User::isLoged()) {
        if (@$_SERVER['SCRIPT_NAME'] !== '/remote/router.php') {
            header('Location: ' . Config::get('core_url') . 'login/');
            exit(0);
        }
    }
}

$sessionPersistence = Config::get('session.persistent', true);
if (empty($sessionPersistence)) {
    // regenerate session id
    session_regenerate_id(false);
}

// include languages and define Language constants and translations
require_once 'lib/language.php';

L\initTranslations();

/* verify required CaseBox folder existance */
$required_folders = array(
    MINIFY_CACHE_DIR
    ,Config::get('files_dir')
    ,Config::get('files_preview_dir')
    ,Config::get('incomming_files_dir')
    ,Config::get('upload_temp_dir')
    ,Config::get('photos_path')
);

foreach ($required_folders as $rfp) {
    if (!file_exists($rfp)) {
        @mkdir($rfp, 0750, true);
    }
}
/* end of verify required CaseBox folder existance */
