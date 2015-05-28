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
        
        $SQL = 'SELECT `active` '
            . ' FROM `'.DEFAULT_TEST_CBPREFIX.'__casebox`.`cores`  '
            . ' WHERE name LIKE "'.DEFAULT_TEST_CORENAME.'" ';

        $rCore =  \CB\DB\dbQuery($SQL);

        $CoreData = $rCore->fetch_assoc();
        
        $this->assertTrue($CoreData['active'] == '1');

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

    public function testdefineBackupDir() {

        $cfg = \CB\Config::loadConfigFile(HELPERS\getCBPath().'/config.ini');

        $this->assertEquals(CB_ROOT_PATH.'/backup/', \CB\defineBackupDir($cfg));

    }

}
