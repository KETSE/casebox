<?php
namespace UnitTest\DataModel;

use \CB\DataModel as DM;

class CoreTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->testCoreName = 'tst_qzq_tezt';
    }

    public function testCoreExistance()
    {
        //delete core if maibe remained from previous failed tests
        if (DM\Core::exists($this->testCoreName)) {
            $this->testDeleteCore();

        } else {
            $this->assertTrue(true, 'Cant test core existance');
        }
    }

    public function testCoreCreate()
    {
        $coreId = DM\Core::create(
            array(
                'name' => $this->testCoreName
            )
        );

        $this->assertTrue(is_numeric($coreId), 'Cant create core');

    }

    public function testCoreRead()
    {
        $data = DM\Core::read($this->testCoreName);

        $this->assertArraySubset(
            array(
              'name' => $this->testCoreName
              ,'active' => 1
            ),
            $data
        );
    }

    public function testCoreUpdate()
    {
        $data = DM\Core::update(
            array(
                'name' => $this->testCoreName
                ,'cfg' => '{"db_user": "root"}'
                ,'active' => 0
            )
        );
        $data = DM\Core::read($this->testCoreName);

        $this->assertArraySubset(
            array(
                'name' => $this->testCoreName
                ,'cfg' => array(
                    "db_user" => "root"
                )
                ,'active' => 0
            ),
            $data
        );
    }

    public function testDeleteCore()
    {
        $this->assertTrue(
            DM\Core::delete($this->testCoreName),
            'Cant delete core'
        );
    }

    public function tearDown()
    {
        unset($this->testCoreName);
    }
}
