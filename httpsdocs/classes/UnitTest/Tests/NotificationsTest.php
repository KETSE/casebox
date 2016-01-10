<?php
namespace UnitTest;

use CB\DataModel as DM;

/**
 * Description of NotificationsTest
 *
 * too complex to be reanalized and replaced by other tests
 */

class NotificationsTest extends \PHPUnit_Framework_TestCase
{
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
        $usersData = Data\Providers::createUsersData();
        $this->userIds = array();

        foreach ($usersData[0] as $data) {
            $this->userIds[] = $this->createUser($data);
        }

        /* create objects for test notifications on them */
        $objectsData = Data\Providers::createTasksData();

        $userIds = $this->userIds;

        $id = array_shift($userIds);
        array_push($userIds, $id);

        foreach ($objectsData[0] as $data) {
            $data['data']['assigned'] = array_shift($userIds);
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
        $this->assertTrue(sizeof($this->userIds) > 3, 'Less than 4 users created');
        $this->assertTrue(sizeof($this->objectIds) > 2, 'Less than 3 tasks created');

        //add a comment to obj 1 with user 1 referencing user 2
        $firstUserId = $this->userIds[0];
        $secondUserId = $this->userIds[1];
        // $thirdUserId = $this->userIds[2];
        // $forthUserId = $this->userIds[3];

        $rootUserId = $this->oldValues['user_id'];

        $pid = $this->objectIds[0];

        $commentData = array(
            'pid' => $pid
            ,'template_id' => 9
            ,'data' => array(
                '_title' => 'Hello to user @bow!'
            )
        );

        //$_SESSION['user']['id'] = $firstUserId;
        \CB\User::setAsLoged($firstUserId, $_SESSION['key']);

        /* make a check - user should be denied to add comments */
        try {
            $commentId = $this->createObject($commentData);

        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $this->assertTrue($msg == 'Access is denied', $msg);
        }

        //$_SESSION['user']['id'] = $rootUserId;
         \CB\User::setAsLoged($rootUserId, $_SESSION['key']);

        $this->addAllowSecurityRule();

        // try again adding a comment,
        // now it should work without access exceptions
        //$_SESSION['user']['id'] = $firstUserId;
        \CB\User::setAsLoged($firstUserId, $_SESSION['key']);

        $commentId = $this->createObject($commentData);

        $this->assertTrue(is_numeric($commentId), 'Wrong comment ID.');

        //check notifications for user bow,
        //he should receive 2 new notifications from root and andrew
        $lastNotification = [
            'user_id' => $firstUserId
            , 'object_id' => $pid
            , 'read' => 0
        ];

        $this->assertTrue(
            $this->checkLastNotification(
                $secondUserId,
                $lastNotification
            ),
            'Wrong last notification : Last ('. print_r($lastNotification, true) . ')'
        );

        //check notifications for root user, he also should receive a new notification from andrew
        //as owner of the object
        $this->assertTrue(
            $this->checkLastNotification(
                $rootUserId,
                array(
                    'user_id' => $firstUserId
                    ,'object_id' => $pid
                    ,'read' => 0
                )
            ),
            'No notification for root (owner) user.'
        );

        //add comments with all 4 users and check notifications to cover
        //code for 3 and more users notifications grouping
        //and check root notifications with each comment
        for ($i = 0; $i < 4; $i++) {

            //$_SESSION['user']['id'] = $this->userIds[$i];
            \CB\User::setAsLoged($this->userIds[$i], $_SESSION['key']);
            $commentData['data']['_title'] = 'Comment from user #' . $i .'.';

            $this->createObject($commentData);

            $this->assertTrue(
                $this->checkLastNotification(
                    $rootUserId,
                    array(
                        'user_id' => $this->userIds[$i]
                        ,'object_id' => $pid
                        ,'read' => 0
                    )
                ),
                'Wrong last notification for root from user #' . $i . '.'
            );
        }

        // $_SESSION['user']['id'] = $rootUserId;
        \CB\User::setAsLoged($rootUserId, $_SESSION['key']);

        /*-------- answer back to previous comment with root and see if notifications are created */
        $commentData['data']['_title'] = 'Reply to Hellow comment.';

        $commentId = $this->createObject($commentData);

        //check notifications for all 4 users
        for ($i=0; $i < 3; $i++) {
            $this->assertTrue(
                $this->checkLastNotification(
                    $this->userIds[$i],
                    array(
                        'object_id' => $pid
                        ,'read' => 0
                    )
                ),
                'Wrong last notification from root for user #' . $i . '.'
            );
        }

        $this->checkMarkingNotificationsAsRead();

        //end of big testing schema

        //cover for now some simple code from Notifications

        $this->assertTrue(
            \CB\Notifications::getActionDeclination('reopen', 'en') == 'reopened',
            'Declination not correct for reopen.'
        );

        $this->assertTrue(
            \CB\Notifications::getActionDeclination('SomeWrongValue!>', 'en') == 'SomeWrongValue!>',
            'Declination not correct for a wrong value.'
        );

        $this->assertTrue(
            \CB\Notifications::getActionDeclination('file_upload', 'en') == 'uploaded a file to',
            'Declination not correct for file_upload.'
        );

        $this->assertTrue(
            \CB\Notifications::getActionDeclination('file_update', 'en') == 'updated a file in',
            'Declination not correct for file_update.'
        );

        //update a task and delete them all
        $obj = \CB\Objects::getCachedObject($pid);
        $data = $obj->getData();
        $data['data']['due_date'] = '2012-12-17T00:00:00Z';
        unset($data['data']['importance']);
        $data['data']['description'] .= ' *update* ';
        $obj->update($data);

        foreach ($this->objectIds as $id) {
            $obj = \CB\Objects::getCachedObject($id);
            $obj->delete(false);
        }

        //get unset notifications and and access functions for preparing email
        $recs = DM\Notifications::getUnseen();
        foreach ($recs as $action) {
            $userData = \CB\User::getPreferences($action['to_user_id']);
            // $sender =
            \CB\Notifications::getSender($action['from_user_id']);
            // $body =
            \CB\Notifications::getMailBodyForAction($action, $userData);
        }
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

        \CB\User::setAsLoged($userId, $_SESSION['key']);
        $api = new \CB\Api\Notifications;

        $result = $api->getList(array('limit' => 900));

        if (($result['success'] == true) && !empty($result['data'])) {
            $rez = array_shift($result['data']);
        }

        //restore previous user id
        //$_SESSION['user']['id'] = $currentUser;
        \CB\User::setAsLoged($currentUser, $_SESSION['key']);

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

        \CB\User::setAsLoged($userId, $_SESSION['key']);
        //$_SESSION['user']['id'] = $userId;
        $api = new \CB\Api\Notifications;

        //check if counts are not empty
        $countResult = $api->getNew(array());
        if (($countResult['success'] !== true) || empty($countResult['data'])) {
            trigger_error(print_r($countResult, true), E_USER_ERROR);

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
        //$_SESSION['user']['id'] = $currentUser;
         \CB\User::setAsLoged($currentUser, $_SESSION['key']);
        if (!$rez) {
            trigger_error(print_r($n, true), E_USER_ERROR);
        }

        return $rez;
    }

    protected function tearDown()
    {

        //remove users and objects
        DM\Users::delete($this->userIds);

        \CB\Config::setFlag('disableSolrIndexing', $this->oldValues['solrIndexing']);

        if (empty($this->oldValues['userVerified'])) {
            unset($_SESSION['verified']);
        }
    }
}
