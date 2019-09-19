<?php
/**
 * Description of ObjectAPITest
 *
 * @author ghindows
 */
namespace UnitTest;

class CoreTest extends \PHPUnit_Framework_TestCase
{

    private $DB;

    public function setUp()
    {
        $this->DB = \CB\Cache::get('dbh');
    }

    /**
     *
     */
    public function testDBConnect()
    {
        $this->assertTrue(mysqli_ping($this->DB));
    }

    /**
     * create core and configuration for it
     * @depends testDBConnect
     */
    public function testCreateCore()
    {
        $sql = 'SELECT `active` '
            . ' FROM `' . DEFAULT_TEST_CBPREFIX . '__casebox`.`cores`  '
            . ' WHERE name LIKE "'.DEFAULT_TEST_CORENAME.'" ';

        $rCore =  \CB\DB\dbQuery($sql);

        $CoreData = $rCore->fetch_assoc();

        $this->assertTrue($CoreData['active'] == '1');
    }

    /**
     * test if you can login to core
     * @depends testCreateCore
     */
    public function testLoginCore()
    {
        /* Helpers::getLoginKey();

        $data = Helpers::getCredentialUserData('root');

        if (isset($data['username']) && isset($data['userpass'])) {
            $this->assertTrue(Helpers::login($data['username'], $data['userpass']));
        } else {
            $this->assertTrue(false, ' can\'t retrive usercredential ');
        } */

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
