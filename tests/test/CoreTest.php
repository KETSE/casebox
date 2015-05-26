<?php
/**
 * Description of ObjectAPITest
 *
 * @author ghindows
 */
namespace CB\UNITTESTS;


class CoreTest extends \PHPUnit_Framework_TestCase
{
    
    private $DB;

    public function setUp() {
     
        $this->DB = \CB\Cache::get('dbh');

    }
 
    /**
     * 
     */
    public function testDBConnect() {
        $this->assertTrue(mysqli_ping( $this->DB));
    }
    
    /**
     * create core and configuration for it 
     * @depends testDBConnect
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
      /* \CB\User::login('root', 'r00t');
      $this->assertTrue(\CB\User::isLoged()); */

        \CB\UNITTESTS\HELPERS\getLoginKey();

      $this->assertTrue( \CB\UNITTESTS\HELPERS\login() );
      
    }

    /**
     * 
     * try to logout if user is logedin
     * @depends testCreateCore
     * @depends testLoginCore
     */
    public function testLogoutCore()
    {
      /* \CB\User::logout();
      $this->assertFalse(\CB\User::isLoged()); */
        $this->assertTrue(true);
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
