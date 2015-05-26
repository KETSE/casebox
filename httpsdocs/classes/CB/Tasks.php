<?php
namespace CB;

use CB\L;
use CB\Util;

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

        if (($_SESSION['user']['id'] != $data['cid']) && !Security::isAdmin()) {
            throw new \Exception(L\get('Access_denied'));
        }

        if ($obj->getUserStatus($p['user_id']) == Objects\Task::$USERSTATUS_NONE) {
            throw new \Exception(L\get('Wrong_id'));
        }

        $status = ($p['status'] == 1)
                ? Objects\Task::$USERSTATUS_DONE
                : Objects\Task::$USERSTATUS_ONGOING;

        $obj->setUserStatus($status, $p['user_id']);
        $obj->update();

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
        $data = $obj->getData();

        if ($obj->getUserStatus() != Objects\Task::$USERSTATUS_ONGOING) {
            throw new \Exception(L\get('Task_already_completed'));
        }

        $obj->setUserStatus(Objects\Task::$USERSTATUS_DONE);
        $obj->update();

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
     * get task html view for sending  to email
     * To be reviewed and removed soon
     * @param  int     $id            task id
     * @param  int     $user_id       destination user id
     * @param  array   $removed_users list of users that have been removed from task
     * @return varchar html content
     */
    public static function getTaskInfoForEmail($id, $user_id = false, $removed_users = false)
    {
        $rez = '';
        $coreUrl = Config::get('core_url');

        $user = array();

        if ($user_id == false) {
            $user = &$_SESSION['user'];
        } else {
            $user = User::getPreferences($user_id);
        }

        $obj = Objects::getCachedObject($id);

        if ($obj) {
            $d = $obj->getData();
            $sd = $d['sys_data'];

            $ed = $obj->getEndDate();
            $status = $obj->getStatus();

            $datetime_period = ($sd['task_allday'] == 1)
                ? Util\formatDatePeriod($d['cdate'], $ed)
                : Util\formatDateTimePeriod($d['cdate'], $ed, @$user['cfg']['timezone']);

            $created_date_text = Util\formatMysqlDate($d['cdate'], 'Y, F j H:i');

            $tickImage = 'data:image/png;base64,'.base64_encode(file_get_contents(DOC_ROOT . 'css/i/ico/tick-circle.png'));

            $users = array();
            $assigned = @$obj->getFieldValue('assigned', 0)['value'];
            if (!empty($assigned)) {
                $ures = DB\dbQuery(
                    'SELECT u.id
                        ,u.`name`
                        ,first_name
                        ,last_name
                        ,u.photo
                        ,u.sex
                    FROM users_groups u
                    WHERE u.id IN (0'.$assigned.')
                    ORDER BY 1'
                ) or die(DB\dbQueryError());

                while ($ur = $ures->fetch_assoc()) {
                    $name = User::getDisplayName($ur);

                    $photoFile = User::getPhotoFilename($ur, true);
                    $photo = 'data:image/png;base64,'.base64_encode(file_get_contents($photoFile));
                    $completed = ($obj->getUserStatus($ur['id']) == Objects\Task::$USERSTATUS_DONE);

                    $users[] = "\n\r".'<tr><td style="width: 1% !important; padding:5px 5px 5px 0px; vertical-align:top; white-space: nowrap">'.
                    "\n\r".'<img src="' . $photo . '" style="width:32px; height: 32px" alt="'.$name.'" title="'.$name.'"/>'.
                    "\n\r".( $completed ? '<img src="' . $tickImage . '" style="width:16px;height:16px; margin-left: -16px"/>': '').
                    "\n\r".'</td><td style="padding: 5px 5px 5px 0; vertical-align:top"><b>'.$name.'</b>'.
                    "\n\r".'<p style="color:#777;margin:0;padding:0">'.
                    "\n\r".( $completed ? L\get('Completed', $user['language_id']).': <span style="color: #777">'.
                        '</span>' : L\get('waitingForAction', $user['language_id']) ).
                    "\n\r".'</p>'.
                    '</td></tr>';

                }
                $ures->close();
            }

            /* add removed users */
            if (!empty($removed_users)) {
                $removed_users = Util\toNumericArray($removed_users);
            }
            if (!empty($removed_users)) {
                $ures = DB\dbQuery(
                    'SELECT u.id
                        ,u.name
                        ,u.first_name
                        ,u.last_name
                        ,u.photo
                        ,u.sex
                    FROM users_groups u
                    WHERE u.id IN (0'.implode(', ', $removed_users).')
                    ORDER BY 1'
                ) or die(DB\dbQueryError());
                while ($ur = $ures->fetch_assoc()) {
                    $name = User::getDisplayName($ur);

                    $photoFile = User::getPhotoFilename($ur, true);
                    $photo = 'data:image/png;base64,'.base64_encode(file_get_contents($photoFile));

                    $users[] = "\n\r".'<tr><td style="width: 1% !important; padding:5px 5px 5px 0px; vertical-align:top; white-space: nowrap">'.
                    "\n\r".'<img src="' . $photo . '" style="width:32px; height: 32px; opacity: 0.6" alt="'.$name.'" title="'.$name.'"/>'.
                    "\n\r".'</td><td style="padding: 5px 5px 5px 0; vertical-align:top"><b style="color: #777; text-decoration: line-through">'.$name.'</b>'.
                    "\n\r".'</td></tr>';
                }
                $ures->close();
            }
            /* end of add removed users */

            $users =  empty($users) ? '' : '<tr><td style="width: 1%; padding: 5px 15px 5px 0; color: #777; vertical-align:top">'.
                L\get('TaskAssigned', $user['language_id']).':</td><td style="vertical-align:top">'.
                '<table style="font-family: \'lucida grande\',tahoma,verdana,arial,sans-serif; font-size: 11px; '.
                'color: #333; width: 100%; display: table; border-collapse: separate; border-spacing: 0;"><tbody>'.
                implode('', $users).'</tbody></table></td></tr>';

            $ownerName = User::getDisplayName($d['cid']);
            $ownerPhoto = 'data:image/png;base64,'.base64_encode(file_get_contents(User::getPhotoFilename($d['cid'], true)));

            // create files block
            $files_text = ''; // static::getTaskFiles($id, true);
            $description = nl2br(Util\adjustTextForDisplay(@$obj->getFieldValue('description', 0)['value']));

            $rez = file_get_contents(TEMPLATES_DIR.'task_notification_email.html');

            $rez = str_replace(
                array(
                    '{top}'
                    ,'{name_style}'
                    ,'{name}'
                    ,'{datetime_period}'
                    ,'{description}'
                    ,'{Status}'
                    ,'{status_style}'
                    ,'{status_text}'
                    ,'{Created}'
                    ,'{created_date_text}'
                    ,'{Importance}'
                    ,'{importance_text}'
                    ,'{Category}'
                    ,'{category_style}'
                    ,'{category_text}'
                    ,'{Path}'
                    ,'{path_text}'
                    ,'{Owner}'
                    ,'{owner_text}'
                    ,'{owner_image}'
                    ,'{assigned_text}'
                    ,'{Files}'
                    ,'{files_text}'
                    ,'{Reminders}'
                    ,'{reminders_text}'
                    ,'{bottom}'
                ),
                array(
                    ''
                    ,'font-size: 1.5em; display: block;'.( ($status == 3 ) ? 'color: #555; text-decoration: line-through' : '')
                    ,'<a href="' . $coreUrl . 'view/' . $id . '/">' . Util\adjustTextForDisplay($d['name']) . '</a>'
                    ,$datetime_period
                    ,$description
                    ,L\get('Status', $user['language_id'])
                    ,'status-style'
                    ,L\get('taskStatus'.$status, $user['language_id'])
                    ,L\get('Created', $user['language_id'])
                    ,$created_date_text
                    ,''
                    ,''
                    ,L\get('Category', $user['language_id'])
                    ,'category_style'
                    ,''
                    ,L\get('Path', $user['language_id'])
                    ,Util\adjustTextForDisplay(@$d['path_text'])
                    ,L\get('Owner', $user['language_id'])
                    ,$ownerName
                    ,$ownerPhoto
                    ,$users
                    ,L\get('Files')
                    ,$files_text
                    ,''
                    ,''
                    ,''
                ),
                $rez
            );
        }

        return $rez;
    }

    /**
     * set additional data for storing into solr
     * @param  reference $objectRecord
     * @return void
     */
    public static function getSolrData(&$objectRecord)
    {
        //make standart analysis of task object
        Objects::getSolrData($objectRecord);

        //make custom analysis for tasks
        $obj = Objects::getCachedObject($objectRecord['id']);

        $data = $obj->getData();
        $sd = $data['sys_data'];

        $template = $obj->getTemplate();

        $objectRecord['task_status'] = @$sd['task_status'];

        $user_ids = Util\toNumericArray($obj->getFieldValue('assigned', 0)['value']);
        if (!empty($user_ids)) {
            $objectRecord['task_u_assignee'] = $user_ids;
        }

        $user_ids[] = @Util\coalesce($data['oid'], $data['cid']);

        $objectRecord['task_u_all'] = array_unique($user_ids);

        $objectRecord['content'] = @$obj->getFieldValue('description', 0)['value'];

        if (!empty($sd['task_d_closed'])) {
            $objectRecord['task_d_closed'] = $sd['task_d_closed'];
        }

        //get users that didnt complete the task yet
        $objectRecord['task_u_done'] = $sd['task_u_done'];
        $objectRecord['task_u_ongoing'] = $sd['task_u_ongoing'];

        //set class
        $objectRecord['cls'] = $template->formatValueForDisplay(
            $template->getField('color'),
            $obj->getFieldValue('color', 0)['value'],
            false
        );
    }

    /**
     * set additional data for storing into solr for a set of records
     * @param  reference $object_records
     * @return void
     */

    public static function getBulkSolrData(&$object_records)
    {
        $process_object_ids = array();
        foreach ($object_records as $object_id => &$object_record) {
            if (@$object_record['template_type'] == 'task') {
                static::getSolrData($object_record);
            }
        }
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
        $taskTemplates = Templates::getIdsByType('task');

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
