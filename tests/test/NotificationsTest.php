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

        $_SESSION['user']['id'] = $this->oldValues['user_id'];

        $this->addAllowSecurityRule();

        // try again adding a comment,
        // now it should work without access exceptions
        $_SESSION['user']['id'] = $userId;

        $commentId = $this->createObject($commentData);

        $_SESSION['user']['id'] = $this->oldValues['user_id'];

        $this->assertTrue(is_numeric($commentId), 'Wrong comment ID.');

        // $this->addComment();
        // $this->checkNotification();

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

    protected function tearDown()
    {

        //remove users and objects

        \CB\Config::setFlag('disableSolrIndexing', $this->oldValues['solrIndexing']);

        if (empty($this->oldValues['userVerified'])) {
            unset($_SESSION['verified']);
        }
    }
}
