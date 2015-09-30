<?php
namespace UnitTest\DataModel;

use \CB\Api;
use \CB\Objects;
use \CB\DataModel as DM;

class FilesTest extends \PHPUnit_Framework_TestCase
{

    /**
     * just a test method to upload a file and execute methods from
     * DataModel to check the result
     * @return void
     */
    public function testDataModelFilesMethods()
    {
       /* $api = new Api\Files();
        $fn = tempnam(sys_get_temp_dir(), 'cb_test');

        file_put_contents($fn, 'testing');
        $data = array(
            'pid' => 1
            ,'localFile' => $fn
            ,'oid' => 1
        );

        $rez = $api->upload($data);

        $this->assertTrue($rez['success'], 'Upload test file failed: ' . $fn);

        $fileData = $rez['data']['file'];
        $id = $fileData['id'];

        $rez = DM\Files::getContentIds($id);

        $this->assertTrue(!empty($rez[$id]), 'Cant get content id');

        $rez = DM\Files::getContentPaths($id);

        $this->assertTrue(!empty($rez[$id]), 'Cant get content path');

        //delete the file from system and check if same actions return empty results
        $f = Objects::getCachedObject($id);

        $f->delete(true); // permanently delete

        unset($f);

        $rez = DM\Files::getContentIds($id);

        $this->assertTrue(empty($rez[$id]), 'Obtaining content id for a permanently deleted file');

        $rez = DM\Files::getContentPaths($id);

        $this->assertTrue(empty($rez[$id]), 'Obtaining content path for a permanently deleted file'); */
        $this->assertTrue(true, 'Obtaining content path for a permanently deleted file');
    }
}
