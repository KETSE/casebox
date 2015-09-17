<?php
namespace UnitTest\DataModel;

use \CB\DataModel as DM;

class NotificationsTest extends \PHPUnit_Framework_TestCase
{

    /**
     * check exceptions on access with wrong params
     * @return void
     */
    public function testRecordCreate()
    {
        $methods = array(
            'getLast'
            ,'getUnseen'
            ,'getCount'
            ,'markAsRead'
            ,'markAllAsRead'
        );

        foreach ($methods as $method) {
            try {
                DM\Notifications::$method(null);

                $this->assertTrue(false, 'Method ' . $method . ' didnt return exception on empty param');

            } catch (\Exception $e) {
                $this->assertTrue(true);
            }
        }
    }
}
