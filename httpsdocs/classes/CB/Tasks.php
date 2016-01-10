<?php
namespace CB;

use CB\L;
use CB\User;
use CB\DataModel as DM;

class Tasks
{
    /**
     * update dates of a event or task (startDate, endDate)
     * @param  object $p params object containing id and dates
     * @return json   response
     */
    public function updateDates($p)
    {
        $rez = array('success' => true);
        if (!Security::canManageTask($p['id'])) {
            throw new \Exception(L\get('Access_denied'), 1);
        }

        $obj = Objects::getCachedObject($p['id']);
        $data = $obj->getData();

        $data['cdate'] = $p['date_start'];
        if (empty($p['date_end'])) {
            unset($data['data']['due_date']);
            unset($data['data']['due_time']);
        } else {
            $data['data']['due_date'] = $p['date_end'];
            if (substr($p['date_end'], 12, 5) == '00:00') {
                unset($data['data']['due_time']);
            } else {
                $data['data']['due_time'] = $p['date_end'];
            }
        }

        $obj->update($data);

        $this->afterUpdate($p['id']);

        return $rez;
    }

    /**
     * set complete or incomplete status for a task responsible user
     * @param array $p params
     */
    public function setUserStatus($p)
    {
        $rez = array(
            'success' => true
            ,'id' => $p['id']
        );

        $obj = Objects::getCachedObject($p['id']);
        $data = $obj->getData();

        if ((User::getId() != $data['cid']) && !Security::isAdmin()) {
            throw new \Exception(L\get('Access_denied'));
        }

        if ($obj->getUserStatus($p['user_id']) == Objects\Task::$USERSTATUS_NONE) {
            throw new \Exception(L\get('Wrong_id'));
        }

        $status = ($p['status'] == 1)
                ? Objects\Task::$USERSTATUS_DONE
                : Objects\Task::$USERSTATUS_ONGOING;

        $obj->setUserStatus($status, $p['user_id']);
        // $obj->updateSysData();

        $this->afterUpdate($p['id']);

        return $rez;
    }

    /**
     * task completion method for currently authenticated user
     *
     * @param array $p {
     *     int $id  task id
     * }
     * @return array json responce
     */
    public function complete($p)
    {
        /* check if current user can manage this task */
        if (!Security::canManageTask($p['id'])) {
            throw new \Exception(L\get('Access_denied'));
        }

        $obj = Objects::getCachedObject($p['id']);

        if ($obj->getUserStatus() != Objects\Task::$USERSTATUS_ONGOING) {
            throw new \Exception(L\get('Task_already_completed'));
        }

        $obj->setUserStatus(Objects\Task::$USERSTATUS_DONE);
        // $obj->updateSysData();

        $this->afterUpdate($p['id']);

        return array('success' => true);
    }

    /**
     * method for marking task as closed
     * @param  int  $id task id
     * @return json response
     */
    public function close($id)
    {
        return $this->changeStatus($id, Objects\Task::$STATUS_CLOSED);
    }

    /**
     * reopen a task
     * @param  int  $id
     * @return json response
     */
    public function reopen($id)
    {
        return $this->changeStatus($id, Objects\Task::$STATUS_ACTIVE);
    }

    /**
     * change status for a task
     * @param  int  $status
     * @param  int  $id
     * @return json response
     */
    protected function changeStatus($id, $status)
    {
        $obj = Objects::getCachedObject($id);
        $data = $obj->getData();

        //status change for task is allowed only for owner or admin
        if (!$obj->isOwner() && !Security::isAdmin()) {
            return  array(
                'success' => false
                ,'msg' => L\get('No_access_for_this_action')
            );
        }

        switch ($status) {
            case Objects\Task::$STATUS_ACTIVE:
                $obj->setActive();
                break;

            case Objects\Task::$STATUS_CLOSED:
                $obj->setClosed();
                break;

            default:
                return array(
                    'success' => false
                    ,'id' => $id
                );
        }

        $this->afterUpdate($id);

        return array(
            'success' => true
            ,'id' => $id
        );
    }

    /**
     * method called after a task have been updated
     * used now to update solr and cases date
     * @param  int  $taskId
     * @return void
     */
    protected function afterUpdate($taskId)
    {
        Objects::updateCaseUpdateInfo($taskId);

        $solr = new  Solr\Client();
        $solr->updateTree(array('id' => $taskId));
    }

    /**
     *  set the flags for actions that could be made to the tasks by a specific or current user
     * @param  reference $object_records
     * @return void
     */
    public static function setTaskActionFlags(&$taskData, $userId = false)
    {
        $p = array(&$taskData);
        static::setTasksActionFlags($p, $userId);
    }

    /**
     *  set the flags for actions that could be made to the tasks by a specific or current user
     * @param  reference $object_records
     * @return void
     */
    public static function setTasksActionFlags(&$tasksDataArray, $userId = false)
    {
        $userId = $userId; // dummy codacy assignment
        $taskTemplates = DM\Templates::getIdsByType('task');

        foreach ($tasksDataArray as &$d) {
            if ((!in_array(@$d['template_id'], $taskTemplates)) ||
                empty($d['status'])
            ) {
                continue;
            }

            $task = Objects::getCachedObject($d['id']);
            $d['can'] = $task->getActionFlags();
        }
    }
}
