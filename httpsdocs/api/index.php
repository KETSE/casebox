<?php
namespace CB;

require_once '../config.php';

$api = new Api();
$ro = $api->processRequest();
// var_dump($ro);
echo $ro->getMethod()."<br />";
echo 'OK';
/*$method = $_SERVER['REQUEST_METHOD'] === 'POST'
        ? @$_POST['method']
        : @$_GET['method'];

// TODO: the switch below may be changed into a more generic approach:
// determine the class first, example, see if method starts with 'cb.files...' for example
// and then create an Api\Files object and call the method
switch ($method) {
    case 'cb.files.download':
        cbFilesDownload();
        break;

    case 'cb.objects.permissions.addRule':
        cbObjectsPermissionsAddRule();
        break;

    default:
        $r = ['status' => 'ok'];
        echo json_encode($r);
        break;
}

function cbFilesDownload()
{

    # check credentials etc etc.
    $id = @$_GET['id'];
    echo $id;

}

function cbObjectsPermissionsAddRule()
{
    // objectId
    //
}
/**/
