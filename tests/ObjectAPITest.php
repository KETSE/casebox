<?php
/**
 * Description of ObjectAPITest
 *
 * @author ghindows
 */
namespace CB\TEST;

class ObjectAPITest extends \PHPUnit_Framework_TestCase
{

    /**
     * create core and configuration for it 
     */
    public function testCreateCore()
    {
        $this->assertTrue(true);
    }

    /**
     * test if you can login to core 
     * @depends testCreateCore
     */
    public function testLoginCore()
    {
        $this->assertTrue(true);
    }

    /**
     * create task
     * @depends testCreateCore
     * @depends testLoginCore
     */
    public function testCreateTask()
    {
        $this->assertTrue(true);
    }

    /**
     * 
     * try to logout if user is logedin
     * @depends testCreateCore
     * @depends testLoginCore
     */
    public function testLogoutCore()
    {
        $this->assertTrue(false);
    }

    /**
     * delete created core
     * @depends testCreateCore
     */
    public function testDeleteCore()
    {
        $this->assertTrue(true);
    }
}
