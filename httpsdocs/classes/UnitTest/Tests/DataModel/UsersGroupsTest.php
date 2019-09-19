<?php
namespace UnitTest\DataModel;

use \CB\DataModel as DM;

class UsersGroupsTest extends \PHPUnit_Framework_TestCase
{

    /**
     * covering code
     * @return void
     */
    public function testCovering()
    {
        $rez = DM\UsersGroups::getAvailableGroups();
        $this->assertTrue(!empty($rez), 'Empty groups');

        $rez = DM\UsersGroups::getAvailableUsers();
        $this->assertTrue(!empty($rez), 'Empty users');

        $rez = DM\UsersGroups::getMemberGroupIds(1);
        $this->assertTrue(empty($rez), 'Empty member groups');

        $rez = DM\UsersGroups::getGroupUserIds(2);
        $this->assertTrue(empty($rez), '!Empty group users for everyone');

        $rez = DM\UsersGroups::getDisplayData();
        $this->assertTrue(!empty($rez), 'Display data');
    }
}
