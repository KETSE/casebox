<?php
namespace CB;

$method = $method = $_SERVER['REQUEST_METHOD'] === 'POST'
                    ? $_POST['method']
                    : $_GET['method'];


// TODO: the switch below may be changed into a more generic approach:
// determine the class first, example, see if method starts with 'cb.files...' for example
// and then create an Api\Files object and call the method
switch ($method) {
    case 'cb.files.download':
        cbFilesDownload();
        break;
    default:
        $r = ['status' => 'ok'];
        echo json_encode($r);
        break;
}




function cbFilesDownload()
{
    $id = $_GET['id'];
    echo $id;

}
