<?php
namespace UnitTest\DataModel;

use \CB\DataModel as DM;

class UserTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->testName = 'tst_user_tezt';
    }

    public function testExistance()
    {
        //delete core if maibe remained from previous failed tests
        if (DM\Users::exists($this->testName)) {
            $this->testDelete();
        }
    }

    public function testCreate()
    {
        $id = DM\Users::create(
            array(
                'name' => $this->testName
                ,'password' => 'qq'
            )
        );

        $this->assertTrue(is_numeric($id), 'Cant create User');

    }

    public function testRead()
    {
        $id = DM\Users::toId($this->testName);

        $rez = DM\Users::read($id);

        $this->assertArraySubset(
            array(
              'name' => $this->testName
              ,'type' => 2
              ,'language_id' => 1
              ,'enabled' => 1
            ),
            $rez
        );
    }

    public function testUpdate()
    {
        $id = DM\Users::toId($this->testName);

        $params = array(
            'id' => $id
            ,'first_name' => '123'
            ,'last_name' => '231'
            ,'sex' => 'f'
            ,'email' => 'f'
            ,'password' => 'a'
            ,'photo' => '/tmp/q.jpg'
            ,'language_id' => 2
            //,'data' => '{}'
            //,'cfg' => '{"db_user": "root"}'
            ,'recover_hash' => '---'
            ,'enabled' => 0
            ,'cid' => 1
        );

        DM\Users::update($params);
        $data = DM\Users::read($id);

        $params['password'] = md5('aero' . $params['password']);

        $this->assertArraySubset(
            $params,
            $data
        );

        //updateByName
        unset($params['id']);
        $params['name'] = $this->testName;
        $params['password'] = 'b';
        $params['enabled'] = 1;

        DM\Users::updateByName($params);
        $data = DM\Users::read($id);

        $params['password'] = md5('aero' . $params['password']);

        $this->assertArraySubset(
            $params,
            $data
        );

        //verify password
        $this->assertTrue(DM\Users::verifyPassword($id, 'b'), 'Wrong password');

        //check id by mail
        $id = DM\Users::getIdByEmail('f');
        $this->assertTrue(is_numeric($id), 'Cant get Id by mail');

        //check id by recover hash
        $id = DM\Users::getIdByRecoveryHash('---');
        $this->assertTrue(is_numeric($id), 'Cant get Id by recover hash');

        //check owner
        $this->assertTrue(DM\Users::getOwnerId($id) == 1, 'different owner set');
    }

    public function testIdExists()
    {
        $id = DM\Users::toId($this->testName);

        $this->assertTrue(
            DM\Users::idExists($id),
            'Id doesnt exist'
        );

        $this->assertTrue(
            !DM\Users::idExists(-$id),
            'Id exist'
        );
    }

    public function testDelete()
    {
        $id = DM\Users::toId($this->testName);

        $this->assertTrue(
            DM\Users::delete($id),
            'Cant delete'
        );
    }

    public function tearDown()
    {
    }

    /**
     * @depends testCreate
     */
    public function testsetAsLoged()
    {
        $id = DM\Users::create(
            array(
                'name' => $this->testName
                ,'password' => 'qq'
            )
        );

        $this->assertTrue(is_numeric($id), 'Cant create User');

        \CB\User::setAsLoged($id, 'tests_key');

        $this->assertTrue(\CB\User::isLoged(), ' Error: user is not logged');
        $this->assertEquals($id, $_SESSION['user']['id'], 'Sessions user is not equal with setted users');
        $this->assertEquals('tests_key', $_SESSION['key'], 'Sessions key is not equal with setted keys');

    }
}
