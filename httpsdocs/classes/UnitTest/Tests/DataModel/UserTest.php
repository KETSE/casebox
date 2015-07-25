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
        if (DM\User::exists($this->testName)) {
            $this->testDelete();
        }
    }

    public function testCreate()
    {
        $id = DM\User::create(
            array(
                'name' => $this->testName
                ,'password' => 'qq'
            )
        );

        $this->assertTrue(is_numeric($id), 'Cant create core');

    }

    public function testRead()
    {
        $id = DM\User::toId($this->testName);

        $rez = DM\User::read($id);

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
        $id = DM\User::toId($this->testName);

        $params = array(
            'id' => $id
            ,'first_name' => '123'
            ,'last_name' => '231'
            ,'sex' => 'f'
            ,'email' => 'f'
            ,'password' => 'a'
            ,'photo' => '/tmp/q.jpg'
            ,'language_id' => 2
            ,'data' => '{}'
            ,'cfg' => '{"db_user": "root"}'
            ,'recover_hash' => '---'
            ,'enabled' => 0
            ,'cid' => 1
        );

        DM\User::update($params);
        $data = DM\User::read($id);

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

        DM\User::updateByName($params);
        $data = DM\User::read($id);

        $params['password'] = md5('aero' . $params['password']);

        $this->assertArraySubset(
            $params,
            $data
        );

        //verify password
        $this->assertTrue(DM\User::verifyPassword($id, 'b'), 'Wrong password');

        //check id by mail
        $id = DM\User::getIdByEmail('f');
        $this->assertTrue(is_numeric($id), 'Cant get Id by mail');

        //check id by recover hash
        $id = DM\User::getIdByRecoveryHash('---');
        $this->assertTrue(is_numeric($id), 'Cant get Id by recover hash');

        //check owner
        $this->assertTrue(DM\User::getOwnerId($id) == 1, 'different owner set');
    }

    public function testIdExists()
    {
        $id = DM\User::toId($this->testName);

        $this->assertTrue(
            DM\User::idExists($id),
            'Id doesnt exist'
        );

        $this->assertTrue(
            !DM\User::idExists(-$id),
            'Id exist'
        );
    }

    public function testDelete()
    {
        $id = DM\User::toId($this->testName);

        $this->assertTrue(
            DM\User::delete($id),
            'Cant delete'
        );
    }

    public function tearDown()
    {
        unset($this->testName);
    }
}
