<?php
namespace CB\UNITTESTS;

use CB\UNITTESTS\DATA;

/**
 * Description of NotificationsTest
 *
 */

class NotificationsTest extends \PHPUnit_Framework_TestCase
{
    private $DB;

    public function setUp()
    {
        $this->oldValues = array(
            'user_id' => $_SESSION['user']['id']
            ,'userVerified' => empty($_SESSION['verified'])
            ,'solrIndexing' => \CB\Config::getFlag('disableSolrIndexing')
        );

        $_SESSION['verified'] = true;

        \CB\Config::setFlag('disableSolrIndexing', true);

        /* create users */
        $usersData = DATA\createUsersData();
        $this->userIds = array();

        foreach ($usersData[0] as $data) {
            $this->userIds[] = $this->createUser($data);
        }

        /* create objects for test notifications on them */
        $objectsData = DATA\createTasksData();

        foreach ($objectsData[0] as $data) {
            $this->objectIds[] = $this->createObject($data);
        }

    }

    /**
     * test notifications module
     *
     * @return [type] [description]
     */
    public function testNotifications()
    {
        //check if everything is prepeared
        $this->assertTrue(sizeof($this->userIds) > 2, 'Less than 3 users created');
        $this->assertTrue(sizeof($this->objectIds) > 2, 'Less than 3 tasks created');

        //add a comment to obj 1 with user 1 referencing user 2
        $userId = $this->userIds[0];
        $pid = $this->objectIds[0];
        $rootUserId = $this->oldValues['user_id'];

        $commentData = array(
            'pid' => $pid
            ,'template_id' => 9
            ,'data' => array(
                '_title' => 'Hello to user @bow!'
            )
        );

        $_SESSION['user']['id'] = $userId;

        /* make a check - user should be denied to add comments */
        try {
            $commentId = $this->createObject($commentData);

        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $this->assertTrue($msg == 'Access is denied', $msg);
        }

        $_SESSION['user']['id'] = $rootUserId;

        $this->addAllowSecurityRule();

        // try again adding a comment,
        // now it should work without access exceptions
        $_SESSION['user']['id'] = $userId;

        $commentId = $this->createObject($commentData);

        $this->assertTrue(is_numeric($commentId), 'Wrong comment ID.');

        //check notifications for user bow, he should receive a new notification from andrew
        $this->assertTrue(
            $this->checkLastNotification(
                $this->userIds[1],
                array(
                    'user_id' => $userId
                    ,'object_id' => $pid
                    ,'read' => 0
                )
            ),
            'Wrong last notification'
        );

        //check notifications for root user, he also should receive a new notification from andrew
        //as owner of the object
        $this->assertTrue(
            $this->checkLastNotification(
                $this->oldValues['user_id'],
                array(
                    'user_id' => $userId
                    ,'object_id' => $pid
                    ,'read' => 0
                )
            ),
            'No notification for root (owner) user.'
        );

        $_SESSION['user']['id'] = $rootUserId;

        /*-------- answer back to previous comment with root and see if notifications are created */
        $commentData['data']['_title'] = 'Reply to Hellow comment.';

        $commentId = $this->createObject($commentData);

        $this->assertTrue(
            $this->checkLastNotification(
                $this->userIds[0],
                array(
                    'user_id' => $rootUserId
                    ,'object_id' => $pid
                    ,'read' => 0
                )
            ),
            'Wrong last notification from root'
        );

        $this->assertTrue(
            $this->checkLastNotification(
                $this->userIds[1],
                array(
                    // 'user_id' => $rootUserId // cant rely on it notifications can be grouped from many users
                    'object_id' => $pid
                    ,'read' => 0
                )
            ),
            'Wrong last notification from root for second user'
        );

        $this->checkMarkingNotificationsAsRead();

    }

    /**
     * get last notification for current user,
     * mark it as read and check the result.
     * Mark all as read
     *
     * @return void
     */
    protected function checkMarkingNotificationsAsRead()
    {
        $userId = \CB\User::getId();

        $n = $this->getLastNotification($userId);

        $this->assertTrue(!empty($n) && ($n['read'] == '0'), 'Last notification read or empty');

        $api = new \CB\Api\Notifications;

        //mark last notification as read
        $r = $api->markAsRead(array('ids' => $n['ids']));

        $this->assertTrue($r['success'] == true, 'Error marking notification as read.');

        //read the notification again and check the result
        $n = $this->getLastNotification($userId);

        $this->assertTrue(!empty($n) && ($n['read'] == '1'), 'Last notification not marked as read');

        //mark all as read
        $r = $api->markAllAsRead();

        $this->assertTrue($r['success'] == true, 'Error marking all notification as read.');
    }

    /**
     *  create an user with given data
     * @return int user id
     */
    public function createUser($data)
    {
        $class = new \CB\UsersGroups();

        $data = $class->addUser($data);

        return $data['data']['id'];
    }

    /**
     *  create an object with given data
     * @return int object id
     */
    public function createObject($data)
    {
        $class = new \CB\Objects();

        $data = $class->create($data);

        return $data['data']['id'];
    }

    /**
     *  add allow for everyone security rule to root node
     * @return int object id
     */
    public function addAllowSecurityRule()
    {

        $class = new \CB\Api\Security();

        $data = array(
            'node_id' => 1,
            'user_group_id' => \CB\Security::getSystemGroupId('everyone'),
            'allow' => 'full_control'
        );

        $rez = $class->updateNodeAccess($data);

        return $rez;
    }

    /**
     * get last notification for a give user id
     * @param  int   $userId [description]
     * @return array | null
     */
    protected function getLastNotification($userId)
    {
        $rez = null;
        //save current user id
        $currentUser = $_SESSION['user']['id'];
        $api = new \CB\Api\Notifications;

        $result = $api->getList(array());

        if (($result['success'] == true) && !empty($result['data'])) {
            $rez = array_shift($result['data']);
        }

        //restore previous user id
        $_SESSION['user']['id'] = $currentUser;

        return $rez;
    }

    /**
     * check if last notification for a user
     * is from userId, for objectId
     * @param  int   $userId
     * @param  array $matches array containing properties to match with
     * @return bool
     */
    protected function checkLastNotification($userId, $matches)
    {
        $rez = false;
        //save current user id
        $currentUser = $_SESSION['user']['id'];

        $_SESSION['user']['id'] = $userId;
        $api = new \CB\Api\Notifications;

        //check if counts are not empty
        $countResult = $api->getNewCount(array());
        if (($countResult['success'] !== true) || empty($countResult['count'])) {
            return $rez;
        }

        //check the last notification with given $matches
        $n = $this->getLastNotification($userId);

        if (!empty($n)) {
            $rez = true;
            foreach ($matches as $k => $v) {
                $rez = $rez && ($n[$k] == $v);
            }
        }

        //restore previous user id
        $_SESSION['user']['id'] = $currentUser;

        return $rez;
    }

    protected function tearDown()
    {

        //remove users and objects

        \CB\Config::setFlag('disableSolrIndexing', $this->oldValues['solrIndexing']);

        if (empty($this->oldValues['userVerified'])) {
            unset($_SESSION['verified']);
        }
    }
}
