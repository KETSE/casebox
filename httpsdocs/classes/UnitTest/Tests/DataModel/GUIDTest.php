<?php
namespace UnitTest\DataModel;

// use \CB\Api;
// use \CB\Objects;
use \CB\DataModel as DM;

class GUIDTest extends \PHPUnit_Framework_TestCase
{

    public function testRecordCreateExceptions()
    {
        //try create with no data to receive exception
        try {
            DM\GUID::create(array());
            $this->assertTrue(false, 'No exception on create with empty data');

        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
    }

    public function testRecordCreate()
    {
        $name = 'aTestGUIDName';
        $id = DM\GUID::toId($name);

        if (is_numeric($id)) {
            DM\GUID::delete($id);
        }

        $id = DM\GUID::toId($name);
        $this->assertTrue(!is_numeric($id), 'toId: returned an id for non existent name');

        $rez = DM\GUID::readNames(
            array(
                $name
            )
        );

        $this->assertTrue(empty($rez[$name]), 'readNames: returned id for non existent name');

        // now create record and check the same
        $id = DM\GUID::create(
            array(
                'name' => $name
            )
        );

        $this->assertTrue(is_numeric($id), 'Cant create a guid');

        $id = DM\GUID::toId($name);
        $this->assertTrue(is_numeric($id), 'toId: cant get id for created guid name');

        $rez = DM\GUID::readNames(
            array(
                $name
            )
        );

        $this->assertTrue(!empty($rez[$name]), 'readNames: doesnt return id for our name');
    }
}
