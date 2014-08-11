<?php
namespace CB;

require_once 'init.php';

//error_log('Upload Phase1');
error_log(print_r($_SERVER, true));

if (isset($_SERVER['HTTP_X_FILE_OPTIONS'])) {

    error_log('Upload: Phase 2');
    // AJAX call
    $file = json_decode($_SERVER['HTTP_X_FILE_OPTIONS'], true);
    $file['error'] = UPLOAD_ERR_OK;
    $file['tmp_name'] = tempnam(Config::get('incomming_files_dir'), 'cbup');
    $file['name'] = urldecode($file['name']);



    if (empty($file['content_id'])) {
        file_put_contents(
            $file['tmp_name'],
            file_get_contents('php://input')
        );
    }
    $_FILES = array('file' => $file);
    $browser = new Browser();
    $result = $browser->saveFile(
        array(
            'pid' => $file['pid']
            ,'response' => @$file['response']
        )
    );
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
}
