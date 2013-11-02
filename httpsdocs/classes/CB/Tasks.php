<?php
namespace CB;

class Tasks
{
    /**
     * loading task data
     * @param  int  $id
     * @return json response
     */
    public function load($id)
    {
        $rez = array('success' => false);
        $res = DB\dbQuery(
            'SELECT
                t.id
                ,t.`title`
                ,t.date_start
                ,t.`date_end`
                ,t.missed
                ,t.`type`
                ,t.privacy
                ,t.responsible_user_ids
                ,t.autoclose
                ,t.description
                ,t.parent_ids
                ,t.child_ids
                ,DATEDIFF(t.`date_end`
                ,UTC_DATE()) `days`
                ,m.pid
                ,m.template_id
                ,(SELECT reminds
                     FROM tasks_reminders
                     WHERE task_id = $1
                         AND user_id = $2) reminds
                , (SELECT name
                     FROM tree
                     WHERE id = ti.case_id) `case`
                  ,(SELECT concat(coalesce(concat(date_format(date_start, \''.$_SESSION['user']['cfg']['short_date_format'].'\'), \' - \'), \'\'), coalesce(custom_title, title))
                     FROM objects
                     WHERE id = t.object_id) `object`
                ,t.status
                ,t.cid
                ,t.completed
                ,t.cdate
                ,t.importance
                ,t.category_id
                ,t.allday
                ,ti.pids `path`
                ,ti.path `pathtext`
            FROM tasks t
            JOIN tree m on t.id = m.id
            JOIN tree_info ti on t.id = ti.id
            WHERE t.id = $1',
            array(
                $id
                ,$_SESSION['user']['id']
            )
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $this->getTaskStyles($r);
            $r['days'] = Util\formatLeftDays($r['days']);
            $r['date_start'] = Util\dateMysqlToISO($r['date_start']);
            $r['date_end'] = Util\dateMysqlToISO($r['date_end']);
            $r['cdate'] = Util\dateMysqlToISO($r['cdate']);
            $r['completed'] = Util\dateMysqlToISO($r['completed']);
            $r['path'] = explode(',', $r['path']);
            array_pop($r['path']);
            $r['path'] = implode('/', $r['path']);
            $c = explode('/', $r['path']);
            $r['create_in'] = array_pop($c);
            $rez = array('success' => true, 'data' => $r);
        } else {
            throw new \Exception(L\Object_not_found);
        }
        $res->close();

        $res = DB\dbQuery(
            'SELECT id
                 , name
            FROM tree
            WHERE pid = $1
                AND `type` = 5
            ORDER BY `name`',
            $id
        ) or die(DB\dbQueryError());
        while ($r = $res->fetch_assoc()) {
            $rez['data']['files'][] = $r;
        }
        $res->close();

        $res = DB\dbQuery(
            'SELECT u.id
                ,ru.status
                ,ru.thesauri_response_id
                ,ru.`time`
            FROM tasks_responsible_users ru
            JOIN users_groups u ON ru.user_id = u.id
            WHERE ru.task_id = $1
            ORDER BY u.l'.USER_LANGUAGE_INDEX,
            $id
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $rez['data']['users'][] = $r;
            if ($r['id'] == $_SESSION['user']['id']) {
                $rez['data']['user'] = $r;
            }
        }
        $res->close();

        $rez['data']['admin'] = (Security::isAdmin() || ($rez['data']['cid'] == $_SESSION['user']['id']));
        $rez['data']['type'] = intval($rez['data']['type']);
        $rez['data']['privacy'] = intval($rez['data']['privacy']);

        return $rez;
    }

    public function save($p)
    {
        if (!isset($p['id'])) {
            $p['id'] = null;
        }
        if (!isset($p['pid'])) {
            $p['pid'] = null;
        }
        $p['type'] = 0;//intval($p['type']);

        $log_action_type = 25; //suppose that only notifications are changed

        $removed_responsible_users = array();

        if (!isset($p['id'])) {
            $p['id'] = null;
        }
        if (!Util\validId($p['id']) || Security::canManageTask($p['id'])) {
            /* update the task details only if is admin or owner of the task /**/

            $log_action_type = 21;// suppose adding new task
            if (is_numeric($p['create_in'])) {
                $p['pid'] = $p['create_in'];
            }
            if (!is_numeric($p['pid'])) {
                $p['pid'] = null;
            }

            $p['date_start'] = empty($p['date_start']) ? null : Util\dateISOToMysql($p['date_start']);
            if (!isset($p['allday'])) {
                $p['allday'] = 0;
            }

            if (($p['template_id'] !== CONFIG\DEFAULT_EVENT_TEMPLATE)) {
                $p['date_end'] = empty($p['date_end']) ? null : Util\dateISOToMysql($p['date_end']);
            } else {
                $p['date_end'] = null;
            }

            if (empty($p['time'])) {
                $p['time'] = null;//'00:00';
            }

            /* estimating deadline status in dependance with parent tasks statuses */
            if (($p['type'] == 6) && !empty($p['parent_ids'])) {
                $p['parent_ids'] = explode(',', $p['parent_ids']);
                $p['parent_ids'] = array_filter($p['parent_ids'], 'is_numeric');
                $p['parent_ids'] = implode(',', $p['parent_ids']);
            } else {
                $p['parent_ids'] = null;
            }
            $status = 4;//pending
            if (empty($p['parent_ids'])) {
                $status = 2;//active
                /* if it's overdue - mysql trigger will change the status */
            } else {
                $res = DB\dbQuery(
                    'SELECT COUNT(id) `count`
                         , sum(status) `status`
                    FROM tasks
                    WHERE id IN ('.$p['parent_ids'].')'
                ) or die(DB\dbQueryError());

                if (($r = $res->fetch_assoc()) && ($r['count']*2 == $r['status'])) {
                    $status = 2; //all parent tasks are completed
                }
                $res->close();
            }
            /* end of estimating deadline status in dependance with parent tasks statuses */
            if (empty($p['id'])) {
                fireEvent('beforeNodeDbCreate', $p);
                $res = DB\dbQuery(
                    'INSERT INTO tree (pid, name, `type`, template_id, cid, uid)
                    VALUES (
                        $1
                        ,$2
                        ,$3
                        ,$4
                        ,$5
                        ,$5)',
                    array(
                        $p['pid']
                        ,$p['title']
                        ,$p['type']
                        ,$p['template_id']
                        ,$_SESSION['user']['id']
                    )
                ) or die(DB\dbQueryError());
                $p['id'] = DB\dbLastInsertId();
            } else {
                //DB\dbQuery('delete from tasks_dependance where task_id = $1', $p['id']) or die(DB\dbQueryError());
                $log_action_type = 22; // updating task

                /* selecting removed responsible_users */
                $res = DB\dbQuery(
                    'SELECT user_id
                    FROM tasks_responsible_users
                    WHERE task_id = $1
                        AND $2 NOT LIKE concat(\'%,\',user_id,\',%\')',
                    array(
                        $p['id']
                        , ','.$p['responsible_user_ids'].','
                    )
                ) or die(DB\dbQueryError());

                while ($r = $res->fetch_assoc()) {
                    $removed_responsible_users[] = $r['user_id'];
                }
                $res->close();

                fireEvent('beforeNodeDbUpdate', $p);
            }

            if (!isset($p['autoclose'])) {
                $p['autoclose'] = 1;
            }

            DB\dbQuery(
                'INSERT INTO tasks (
                    id
                    ,`title`
                    ,`date_start`
                    ,`date_end`
                    ,`time`
                    ,`type`
                    ,`privacy`
                    ,responsible_user_ids
                    ,description
                    ,parent_ids
                    ,reminds
                    ,cid
                    ,status
                    ,autoclose
                    ,importance
                    ,category_id
                    ,allday
                    ,uid
                    ,udate)
                VALUES (
                    $1
                    ,$2
                    ,$3
                    ,$4
                    ,$5
                    ,$6
                    ,$7
                    ,$8
                    ,$9
                    ,$10
                    ,$11
                    ,$12
                    ,$13
                    ,$14
                    ,$15
                    ,$16
                    ,$17
                    ,NULL
                    ,NULL)
                ON DUPLICATE KEY
                UPDATE `title` = $2
                    ,`date_start` = $3
                    ,`date_end` = $4
                    ,`time` = $5
                    ,`type` = $6
                    ,`privacy` = $7
                    ,responsible_user_ids = $8
                    ,description = $9
                    ,parent_ids = $10
                    ,reminds = $11
                    ,uid = $12
                    ,udate = CURRENT_TIMESTAMP
                    ,status = CASE status WHEN 2 THEN 2 ELSE $13 END
                    ,autoclose = $14
                    ,importance = $15
                    ,category_id = $16
                    ,allday = $17',
                @array(
                    $p['id']
                    ,$p['title']
                    ,$p['date_start']
                    ,$p['date_end']
                    ,$p['time']
                    ,$p['type']
                    ,intval($p['privacy'])
                    ,$p['responsible_user_ids']
                    ,$p['description']
                    ,$p['parent_ids']
                    ,$p['reminds']
                    ,$_SESSION['user']['id']
                    ,$status
                    ,$p['autoclose']
                    ,$p['importance']
                    ,$p['category_id']
                    ,$p['allday']
                )
            ) or die(DB\dbQueryError());

            /*storing specified files*/

            $files = new Files();
            $params = array(
                'pid' => $p['id']
                ,'date' => $p['date_start']
                ,'response' => 'newversion'
                ,'files' => &$_FILES
            );
            $files->storeFiles($params);
            unset($files);
            /*end of specified files*/
            Objects::updateCaseUpdateInfo($p['id']);

        }
        $remind_users = null;
        if (($log_action_type == 21) || ($log_action_type == 22)) {
            $remind_users = Util\toNumericArray($p['responsible_user_ids']);
            if (!empty($removed_responsible_users)) {
                $remind_users = array_merge($remind_users, $removed_responsible_users);
            }
        }

        $logParams = array(
            'action_type' => $log_action_type
            ,'task_id' => $p['id']
            ,'to_user_ids' => $p['responsible_user_ids']
            ,'remind_users' => $remind_users
            ,'removed_users' => $removed_responsible_users
            ,'info' => 'title: '.$p['title']);
        Log::add($logParams);

        $this->saveReminds($p);

        switch ($log_action_type) {
            case 21: //created
                fireEvent('nodeDbCreate', $p);
                break;
            case 22: //updated
                fireEvent('nodeDbUpdate', $p);
                break;

        }

        Solr\Client::runCron();
        $rez = $this->load($p['id']);
        $rez['logParams'] = &$logParams;

        return $rez;
    }

    /**
     * update dates of a event or task (startDate, endDate)
     * @param  object $p params object containing id and dates
     * @return json   response
     */
    public function updateDates($p)
    {
        $rez = array('success' => true);
        if (!Security::canManageTask($p['id'])) {
            throw new Exception(L\Access_denied, 1);
        }
        $res = DB\dbQuery(
            'SELECT
                t.allday
                ,tt.template_id
            FROM tasks t
            JOIN tree tt on t.id = tt.id
            WHERE t.id = $1',
            $p['id']
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $p['date_start'] = Util\dateISOToMysql($p['date_start']);
            $p['date_end'] = empty($p['date_end'])
                ? null
                : Util\dateISOToMysql($p['date_end']);

            DB\dbQuery(
                'UPDATE tasks set date_start = $2, date_end = $3 WHERE id = $1',
                array($p['id'], $p['date_start'], $p['date_end'])
            ) or die(DB\dbQueryError());
        } else {
            $rez['success'] = false;
        }
        $res->close();
        Objects::updateCaseUpdateInfo($p['id']);
        Solr\Client::runCron();

        return $rez;
    }

    /**
     * save reminds for a task with deadlines
     * @param  array   $p               task properties
     * @param  integer $log_action_type
     * @return json    response
     */
    public function saveReminds($p, $log_action_type = 25)
    {
        DB\dbQuery(
            'INSERT INTO tasks_reminders (task_id, user_id, reminds)
            VALUES ($1
                  , $2
                  , $3) ON duplicate KEY
            UPDATE reminds = $3',
            array(
                $p['id']
                ,$_SESSION['user']['id']
                ,$p['reminds']
            )
        ) or die(DB\dbQueryError());
        /* end of save reminds for currents user /**/

        /* create notifications for specified reminders */
        /* if no deadline is set for the task then no notifications will be set */
        $res = DB\dbQuery(
            'SELECT
                t.title
                ,t.date_start
                ,t.date_end
                ,ti.path
            FROM tasks t
            JOIN tree_info ti on t.id = ti.id
            WHERE t.id = $1',
            $p['id']
        ) or die(DB\dbQueryError());
        if ($r = $res->fetch_assoc()) {
            $p = array_merge($p, $r);
        }
        $res->close();

        if (!empty($p['date_end'])) {
            //selecting currently used notification ids to be updated with new data
            $ids = array();
            $res = DB\dbQuery(
                'SELECT id
                FROM notifications
                WHERE task_id = $1
                    AND user_id = $2
                    AND subtype = 1',
                array(
                    $p['id']
                    ,$_SESSION['user']['id']
                )
            ) or die(DB\dbQueryError());

            while ($r = $res->fetch_assoc()) {
                $ids[] = $r['id'];
            }
            $res->close();
            //end of selecting currently used notification ids to be updated with new data

            $a = explode('-', $p['reminds']);

            $subject = L\Reminder.': '.$p['title'].
                ' @ '.Util\formatDateTimePeriod($p['date_start'], $p['date_end'], @$_SESSION['user']['cfg']['TZ']).
                ' ('.$p['path'].')';
            $message = '<generateTaskViewOnSend>';
            foreach ($a as $r) {
                $rem = explode('|', $r);    // user|remindType|remind delay|remindUnits
                if ($rem[0] != 1) {
                    continue; // not by mail
                }
                $id = empty($ids) ? null : array_shift($ids);
                $unit = 'HOUR';
                switch ($rem[2]) {
                    case 1:
                        $unit = 'MINUTE';
                        break;
                    case 2:
                        $unit = 'HOUR';
                        break;
                    case 3:
                        $unit = 'DAY';
                        break;
                    case 4:
                        $unit = 'WEEK';
                        break;
                }
                DB\dbQuery(
                    'INSERT INTO notifications (
                        id
                        ,action_type
                        ,task_id
                        ,subtype
                        ,subject
                        ,message
                        ,time
                        ,user_id)
                    VALUES (
                        $1
                        ,$2
                        ,$3
                        ,1
                        ,$4
                        ,$5
                        ,DATE_ADD($6, INTERVAL $7 '.$unit.')
                        ,$8)
                    ON DUPLICATE KEY
                    UPDATE action_type = $2
                        ,task_id = $3
                        ,subtype = 1
                        ,subject = $4
                        ,message = $5
                        ,time = DATE_ADD($6, INTERVAL $7 '.$unit.')
                        ,user_id = $8',
                    array(
                        $id
                        ,$log_action_type
                        ,$p['id']
                        ,$subject
                        ,$message
                        ,$p['date_end']
                        ,-$rem[1]
                        ,$_SESSION['user']['id']
                    )
                ) or die(DB\dbQueryError());
            }
            if (!empty($ids)) {
                DB\dbQuery(
                    'DELETE
                    FROM notifications
                    WHERE task_id = $1
                        AND id IN (0'.implode(', ', $ids).')',
                    $p['id']
                ) or die(DB\dbQueryError());
            }
        } else {
            DB\dbQuery(
                'DELETE
                FROM notifications
                WHERE task_id = $1
                    AND user_id = $2
                    AND subtype = 1',
                array(
                    $p['id']
                    ,$_SESSION['user']['id']
                )
            ) or die(DB\dbQueryError());
        }

        return array('success' => true, 'reminds' => $p['reminds']);
    }

    /**
     * set complete or incomplete status for a responsible task user
     * @param array $p params
     */
    public function setUserStatus($p)
    {
        $rez = array('success' => true, 'id' => $p['id']);
        $task = array();
        $res = DB\dbQuery(
            'SELECT responsible_user_ids
                ,autoclose
                ,title
                ,status
                ,autoclose
                ,cid
            FROM tasks
            WHERE id = $1',
            $p['id']
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $task  = $r;
        }
        $res->close();

        $responsible_users = explode(',', $task['responsible_user_ids']);
        if (($_SESSION['user']['id'] != $task['cid']) && !Security::isAdmin()) {
            throw new \Exception(L\Access_denied);
        }
        if (!in_array($p['user_id'], $responsible_users)) {
            throw new \Exception(L\Wrong_id);
        }
        if (empty($p['status'])) {
            $p['status'] = 0;
        }
        @DB\dbQuery(
            'INSERT INTO tasks_responsible_users (
                task_id
                ,user_id
                ,status
                ,thesauri_response_id
                ,`time`)
            VALUES($1, $2, $3, $4, CURRENT_TIMESTAMP)
            ON DUPLICATE KEY
            UPDATE status = $3
                ,thesauri_response_id = $4
                ,`time` = CURRENT_TIMESTAMP',
            array(
                $p['id']
                ,$p['user_id']
                ,$p['status']
                ,$p['thesauri_response_id']
            )
        ) or die(DB\dbQueryError());

        $autoclosed = false;
        $action_type = 29; //aboutTaskCompletionDecline
        if ($p['status'] == 1) {
            $action_type = 30; //aboutTaskCompletionOnBehalt
            $autoclosed = $this->checkAutocloseTask($p['id']);
        }
        Log::add(
            array(
                'action_type' => $action_type
                ,'task_id' => $p['id']
                ,'to_user_ids' => $p['user_id']
                ,'remind_users' => $task['cid'].','.$p['user_id']
                ,'autoclosed' => $autoclosed
                ,'info' => 'title: '.$task['title']
            )
        ); // TO REVIEW

        Objects::updateCaseUpdateInfo($p['id']);

        Solr\Client::runCron();

        return $rez;
    }

    /**
     * check if a task is autoclosable and if so - change it's status to closed
     * @param  int     $id task id
     * @return boolean
     */
    public function checkAutocloseTask($id)
    {
        $rez = false;
        /* suppose that task is autoclose = 1 and dont check this for now*/
        $res = DB\dbQuery(
            'SELECT user_id
            FROM tasks_responsible_users
            WHERE task_id = $1
                AND status = 0',
            $id
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            //there are unset user statuses, so nothing to change for this task
        } else {
            DB\dbQuery(
                'UPDATE tasks
                SET completed = CURRENT_TIMESTAMP
                WHERE id = $1',
                $id
            ) or die(DB\dbQueryError());

            $res = DB\dbQuery(
                'SELECT title
                    ,autoclose
                    ,cid
                    ,responsible_user_ids
                FROM tasks
                WHERE id = $1',
                $id
            ) or die(DB\dbQueryError());

            if (($r = $res->fetch_assoc()) && ($r['autoclose'] == 1)) {
                $res->close();
                DB\dbQuery(
                    'UPDATE tasks
                    SET status = 3
                    WHERE id = $1
                        AND status <> 3
                        AND autoclose = 1',
                    $id
                ) or die(DB\dbQueryError());

                $this->updateChildTasks($id);
                $rez = true;
            } else {
                $res->close();
            }
        }

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
        $task = array();

        /* check if current user can manage this task */
        if (!Security::canManageTask($p['id'])) {
            throw new \Exception(L\Access_denied);
        }

        /* load task data */
        $res = DB\dbQuery(
            'SELECT ti.case_id
                ,t.object_id
                ,t.status
                ,ru.status `user_status`
                ,t.cid
                ,t.responsible_user_ids
                ,t.title
            FROM tasks t
            LEFT JOIN tree_info ti ON t.id = ti.id
            JOIN tasks_responsible_users ru ON t.id = ru.task_id
            WHERE t.id = $1
                AND ru.user_id = $2',
            array(
                $p['id']
                ,$_SESSION['user']['id']
            )
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            if ($r['user_status'] == 1) {
                throw new \Exception(L\Task_already_completed);
            }
            $task = $r;
        }
        $res->close();

        DB\dbQuery(
            'UPDATE tasks_responsible_users
            SET status = 1
                ,`time` = CURRENT_TIMESTAMP
            WHERE task_id = $1
                AND user_id = $2',
            array(
                $p['id']
                ,$_SESSION['user']['id']
            )
        ) or die(DB\dbQueryError());

        DB\dbQuery(
            'INSERT INTO messages (
                node_id
                ,`type`
                ,subject
                ,message
                ,cid)
            VALUES ($1, $2, $3, $4, $5)',
            array(
                $p['id'] // Util\coalesce($task['case_id'], $task['object_id'], $p['id'])
                ,'task_complete'
                ,'Complete task'
                ,$p['message']
                ,$_SESSION['user']['id']
            )
        ) or die(DB\dbQueryError());

        Log::add(
            array(
                'action_type' => 23
                ,'task_id' => $p['id']
                ,'remind_users' => $task['cid']
                ,'autoclosed' => $this->checkAutocloseTask($p['id'])
                ,'info' => 'title: '.$task['title']
            )
        );

        Objects::updateCaseUpdateInfo($p['id']);

        Solr\Client::runCron();

        return array('success' => true);
    }

    /**
     * method for marking task as closed
     * @param  int  $id task id
     * @return json response
     */
    public function close($id)
    {
        $task = array();
        $res = DB\dbQuery(
            'SELECT cid
                 , title
                 , responsible_user_ids
            FROM tasks
            WHERE id = $1',
            $id
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $task = $r;
        }
        $res->close();
        if (($_SESSION['user']['id'] !== $task['cid']) && !Security::isAdmin()) {
            return  array('success' => false, 'msg' => L\No_access_to_close_task);
        }
        DB\dbQuery(
            'UPDATE tasks SET status = 3 WHERE id = $1',
            $id
        ) or die(DB\dbQueryError());

        /* log and notify all users about task closing */
        Log::add(
            array(
                'action_type' => 27
                ,'task_id' => $id
                //,'to_user_ids' => $task['responsible_user_ids']
                ,'remind_users' => $task['cid'].','.$task['responsible_user_ids']
                ,'info' => 'title: '.$task['title']
            )
        );
        $this->updateChildTasks($id);

        Objects::updateCaseUpdateInfo($id);

        Solr\Client::runCron();

        return array('success' => true, 'id' => $id);
    }

    /**
     * reopen a task
     * @param  int  $id
     * @return json response
     */
    public function reopen($id)
    {
        $task = array();
        $res = DB\dbQuery(
            'SELECT cid
                ,title
                ,responsible_user_ids
            FROM tasks
            WHERE id = $1',
            $id
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $task  = $r;
        }
        $res->close();
        if (($_SESSION['user']['id'] !== $task['cid']) && !Security::isAdmin()) {
            return  array('success' => false, 'msg' => L\No_access_for_this_action);
        }
        DB\dbQuery(
            'UPDATE tasks
            SET status = CASE
                    WHEN ((date_end IS NULL)
                           OR (date_end > CURRENT_TIMESTAMP))
                    THEN 2
                    ELSE 1
                 END
            WHERE id = $1',
            $id
        ) or die(DB\dbQueryError());

        /* update responsible user statuses to incomplete*/
        DB\dbQuery(
            'UPDATE tasks_responsible_users
            SET status = 0
            WHERE task_id = $1',
            $id
        ) or die(DB\dbQueryError());
        /* end of update responsible user statuses to incomplete*/

        /* log and notify all users about task closing */
        Log::add(
            array(
                'action_type' => 31
                ,'task_id' => $id
                ,'remind_users' => $task['cid'].','.$task['responsible_user_ids']
                ,'info' => 'title: '.$task['title']
            )
        );
        $this->updateChildTasks($id);

        Objects::updateCaseUpdateInfo($id);

        Solr\Client::runCron();

        return array('success' => true, 'id' => $id);
    }

    public function updateChildTasks($task_id)
    {
        // selecting child tasks (that depend on this task completition)
        $updatingChildTasks = array();
    }

    /**
     * get task css classes depending on task status
     * @param  [type] $task [description]
     * @return [type] [description]
     */
    private function getTaskStyles(&$task)
    {
        $cls = '';
        $iconCls = 'icon-calendar-medium-clean';
        if ($task['status'] == 4) {
            $cls = 'cO';
        }
        if (!empty($task['missed'])) {

        }
        if (!empty($task['completed'])) {
            $cls = ($task['status'] != 3) ? 'cGR' : 'cG';
        }
        if (!empty($cls)) {
            $task['cls'] = $cls;
        }
        if (!empty($iconCls)) {
            $task['iconCls'] = $iconCls;
        }
    }

    /**
     * get task html view for sending  to email
     * @param  int     $id            task id
     * @param  int     $user_id       destination user id
     * @param  array   $removed_users list of users that have been removed from task
     * @return varchar html content
     */
    public static function getTaskInfoForEmail($id, $user_id = false, $removed_users = false)
    {
        $rez = '';
        $user = array();
        if ($user_id == false) {
            $user = &$_SESSION['user'];
        } else {
            $user = User::getPreferences($user_id);
            if (empty($user['language_id'])) {
                $user['language_id'] = 1;
            }
        }
        $res = DB\dbQuery(
            'SELECT
                `title`
                ,date_start
                ,date_end
                ,description
                ,status
                ,category_id
                ,importance
                ,`type`
                ,allday
                ,cid
                ,ti.path `path_text`
                ,(SELECT l'.$user['language_id'].'
                    FROM users_groups
                    WHERE id = t.cid) owner_text
                ,cdate
                ,responsible_user_ids
                ,(SELECT reminds
                    FROM tasks_reminders
                    WHERE task_id = $1
                        AND user_id = $2) reminders
                ,DATABASE() `db`
            FROM tasks t
            JOIN tree_info ti ON t.id = ti.id
            WHERE t.id = $1',
            array(
                $id,
                @$user['id']
            )
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $format = 'Y, F j';
            if ($r['allday'] != 1) {
                $format .= ' H:i';
            }
            $datetime_period = Util\formatMysqlDate($r['date_start'], $format);
            if (!empty($r['date_end'])) {
                $i = strtotime($r['date_end']);
                $datetime_period .= ' - '.Util\formatMysqlDate($r['date_end'], $format);
            }
            $created_date_text = Util\formatMysqlDate($r['cdate'], 'Y, F j H:i');
            $importance_text = '';
            switch ($r['importance']) {
                case 1:
                    $importance_text = L\get('Low', $user['language_id']);
                    break;
                case 2:
                    $importance_text = L\get('Medium', $user['language_id']);
                    break;
                case 3:
                    $importance_text = L\get('High', $user['language_id']);
                    break;
            }
            //$left = Util\formatLeftDays($r['days']);
            $users = array();
            $ures = DB\dbQuery(
                'SELECT u.id
                    ,u.l'.$user['language_id'].' `name`
                    ,ru.status
                    ,ru.time
                    ,(SELECT `message`
                        FROM messages
                        WHERE node_id = ru.task_id
                            AND cid = u.id
                            AND `type` = \'task_complete\'
                        ORDER BY cdate DESC LIMIT 1) `complete_message`
                FROM users_groups u
                LEFT JOIN tasks_responsible_users ru ON u.id = ru.user_id
                    AND ru.task_id = $1
                WHERE u.id IN (0'.$r['responsible_user_ids'].')
                ORDER BY 1',
                $id
            ) or die(DB\dbQueryError());

            while ($ur = $ures->fetch_assoc()) {
                $users[] = "\n\r".'<tr><td style="width: 1% !important; padding:5px 5px 5px 0px; vertical-align:top; white-space: nowrap">'.
                "\n\r".'<img src="'.Util\getCoreHost($r['db']).'photo/'.$ur['id'].'.jpg" style="width:32px; height: 32px" alt="'.$ur['name'].'" title="'.$ur['name'].'"/>'.
                "\n\r".( ($ur['status'] == 1) ? '<img src="'.Util\getCoreHost($r['db']).'css/i/ico/tick-circle.png" style="width:16px;height:16px; margin-left: -16px"/>': '').
                "\n\r".'</td><td style="padding: 5px 5px 5px 0; vertical-align:top"><b>'.$ur['name'].'</b>'.
                "\n\r".'<p style="color:#777;margin:0;padding:0">'.
                "\n\r".( ($ur['status'] == 1) ? L\get('Completed', $user['language_id']).': <span style="color: #777" title="'.$ur['time'].'">'.
                    Util\formatMysqlDate($ur['time'], 'Y, F j H:i').'</span>' : L\get('waitingForAction', $user['language_id']) ).
                "\n\r".'</p>'.
                ( (($ur['status'] == 1) && !empty($ur['complete_message'])) ? '<p>'.nl2br(Util\adjustTextForDisplay($ur['complete_message'])).'</p>': '').
                '</td></tr>';

            }
            $ures->close();

            /* add removed users */
            if (!empty($removed_users)) {
                $removed_users = Util\toNumericArray($removed_users);
            }
            if (!empty($removed_users)) {
                $ures = DB\dbQuery(
                    'SELECT u.id
                         , u.l'.$user['language_id'].' `name`
                    FROM users_groups u
                    WHERE u.id IN (0'.implode(', ', $removed_users).')
                    ORDER BY 1'
                ) or die(DB\dbQueryError());
                while ($ur = $ures->fetch_assoc()) {
                    $users[] = "\n\r".'<tr><td style="width: 1% !important; padding:5px 5px 5px 0px; vertical-align:top; white-space: nowrap">'.
                    "\n\r".'<img src="'.Util\getCoreHost($r['db']).'photo/'.$ur['id'].'.jpg" style="width:32px; height: 32px; opacity: 0.6" alt="'.$ur['name'].'" title="'.$ur['name'].'"/>'.
                    "\n\r".'</td><td style="padding: 5px 5px 5px 0; vertical-align:top"><b style="color: #777; text-decoration: line-through">'.$ur['name'].'</b>'.
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

            $files = array();
            $files_text = '';
            $fres = DB\dbQuery(
                'SELECT id, name
                FROM tree
                WHERE pid = $1
                    AND `type` = 5
                ORDER BY `name`',
                $id
            ) or die(DB\dbQueryError());

            while ($fr = $fres->fetch_assoc()) {
                $files[] = $fr;
            }
            $fres->close();

            if (!empty($files)) {
                $files_text .= '<tr><td style="width: 1%; padding: 5px 15px 5px 0; color: #777; vertical-align:top">'.
                    L\get('Files', $user['language_id']).':</td><td style="vertical-align:top"><ul style="list-style: none; padding:0;margin:0">';
                foreach ($files as $f) {
                    $files_text .= '<li style="margin:0;padding: 3px 0"><a href="#" name="file" fid="'.$f['id'].
                        '" style="text-decoration: underline; color: #15C"><img style="float:left;margin-right:5px" src="'.
                        Util\getCoreHost($r['db']).'css/i/ext/'.Files::getIconFileName($f['name']).'"> '.$f['name'].'</a></li>';
                }
                $files_text .= '</ul></td></tr>';
            }

            $reminders_text = '';
            if (!empty($r['reminders'])) {
                $reminders_text .= '<tr><td  style="width: 1%; padding: 5px 15px 5px 0; color: #777; vertical-align:top">'.
                    L\get('Reminders', $user['language_id']).':</td><td style="vertical-align:top">'.
                    '<ul style="list-style: none; text-decoration: none; color: #333;margin:0;padding: 0">';
                $ra = explode('-', $r['reminders']);
                foreach ($ra as $rem) {
                    $rem = explode('|', $rem);
                    $units = '';
                    switch ($rem[2]) {
                        case 1:
                            $units = L\get('ofMinutes', $user['language_id']);
                            break;
                        case 2:
                            $units = L\get('ofHours', $user['language_id']);
                            break;
                        case 3:
                            $units = L\get('ofDays', $user['language_id']);
                            break;
                        case 4:
                            $units = L\get('ofWeeks', $user['language_id']);
                            break;
                    }
                    $reminders_text .= '<li>'.$rem[1].' '.$units.'</li>';
                }
                $reminders_text .= '</ul></td></tr>';
            }

            // $message = str_replace( array('<i', '</i>'), array('<strong', '</strong>'), $message);
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
                    ,'{owner_image}'
                    ,'{owner_text}'
                    ,'{assigned_text}'
                    ,'{Files}'
                    ,'{files_text}'
                    ,'{Reminders}'
                    ,'{reminders_text}'
                    ,'{bottom}'
                ),
                array(
                    '' //$message
                    ,'font-size: 1.5em; display: block;'.( ($r['status'] == 3 ) ? 'color: #555; text-decoration: line-through' : '')
                    ,$r['title']
                    ,$datetime_period
                    ,$r['description']
                    ,L\get('Status', $user['language_id'])
                    ,'status-style'
                    ,L\get('taskStatus'.$r['status'], $user['language_id'])
                    ,L\get('Created', $user['language_id'])
                    ,$created_date_text
                    ,L\get('Importance', $user['language_id'])
                    ,$importance_text
                    ,L\get('Category', $user['language_id'])
                    ,'category_style'
                    ,Util\getThesauriTitles($r['category_id'], $user['language_id'])
                    ,L\get('Path', $user['language_id'])
                    ,$r['path_text']
                    ,L\get('Owner', $user['language_id'])
                    ,Util\getCoreHost($r['db']).'photo/'.$r['cid'].'.jpg'
                    ,$r['owner_text']
                    ,$users //{assigned_text}
                    ,L\get('Files', $user['language_id'])
                    ,$files_text
                    ,L\get('Reminders', $user['language_id'])
                    ,$reminders_text
                    ,''
                ),
                $rez
            );
        }
        $res->close();

        return $rez;
    }

    /**
     * generate html preview for a task
     * @param  int     $id task id
     * @return varchar html
     */
    public function getPreview($id)
    {
        if (!is_numeric($id)) {
            return '';
        }
        $d = $this->load($id);
        if ($d['success'] != true) {
            return '';
        }
        $d = $d['data'];

        $rez = '<div class="taskview">
            <h2 '.( ($d['status'] == 3) ? 'class=\'completed\'"' : '' ).'>{name}</h2>
            <div class="datetime">{datetime_period}</div>
            <div class="info">{description}</div>
            <table class="props"><tbody>
            <tr><td class="k">'.L\Status.':</td><td><span class="status{status}">{status_text}</span></td></tr>
            <tr><td class="k">'.L\Importance.':</td><td>{importance_text}</td></tr>
            <tr><td class="k">'.L\Category.':</td><td><img src="/css/i/s.gif" class="icon {category_icon}"> {category_text}</td></tr>
            <tr><td class="k">'.L\Path.':</td><td><a class="path" path="{path}" href="#">{path_text}</a></td></tr>
            <tr><td class="k">'.L\Owner.':</td><td><table class="people"><tbody>
                <tr><td class="user"><img class="photo32" src="photo/{cid}.jpg"></td><td><b>{creator_name}</b><p class="gr">'.L\Created.': '.
                '<span class="dttm" title="{full_create_date}">{create_date}</span></p></td></tr></tbody></table></td></tr>';

        $date_format = str_replace('%', '', $_SESSION['user']['cfg']['short_date_format']);
        $format = 'Y, F j';//$date_format;
        if ($d['allday'] != 1) {
            $format .= ' H:i';
        }
        $i = strtotime($d['date_start']);
        $d['datetime_period'] = date($format, $i);

        if (!empty($d['date_end'])) {
            $i = strtotime($d['date_end']);
            $d['datetime_period'] .= ' - '.date($format, $i);
        }

        $d['importance_text'] = '';
        switch ($d['importance']) {
            case 1:
                $d['importance_text'] = L\Low;
                break;
            case 2:
                $d['importance_text'] = L\Medium;
                break;
            case 3:
                $d['importance_text'] = L\High;
                break;
        }

        $params = array( '{name}' => Util\adjustTextForDisplay($d['title'])
            ,'{datetime_period}' => $d['datetime_period']
            ,'{description}' => nl2br(Util\adjustTextForDisplay($d['description']))
            ,'{status}' => $d['status']
            ,'{status_text}' => L\get('taskStatus'.$d['status'])
            ,'{importance_text}' => $d['importance_text']
            ,'{category_icon}' => Util\getThesauryIcon($d['category_id'])
            ,'{category_text}' => Util\getThesauriTitles($d['category_id'])
            ,'{path}' => $d['path']
            ,'{path_text}' => $d['pathtext']
            ,'{cid}' => $d['cid']
            ,'{creator_name}' => User::getDisplayName($d['cid'])
            ,'{full_create_date}' => date($date_format.' H:i', strtotime($d['cdate']))
            ,'{create_date}' => date($date_format.' H:i', strtotime($d['cdate']))
            );
        $rez = str_replace(array_keys($params), array_values($params), $rez);

        if (!empty($d['users'])) {
            $rez .= '<tr><td class="k">'.L\TaskAssigned.':</td><td><table class="people"><tbody>';
            foreach ($d['users'] as $u) {
                $un = User::getDisplayName($u['id']);
                $rez .= '<tr><td class="user"><div style="position: relative"><img class="photo32" src="photo/'.$u['id'].'.jpg" alt="'.$un.'" title="'.$un.'">'.
                ( ($u['status'] == 1 ) ? '<img class="done icon icon-tick-circle" src="css/i/s.gif" />': "").
                '</div></td><td><b>'.$un.'</b>'.
                '<p class="gr">'.(
                    ($u['status'] == 1)
                    ? L\Completed.': '.date($date_format.' H:i', strtotime($u['time']))
                    : L\waitingForAction
                ).'</p></td></tr>';
                //<a class="bt" name="complete" uid="1" href="#">завершить</a>

            }
            $rez .= '</tbody></table></td></tr>';
        }

        if (!empty($d['files'])) {
            $rez .= '<tr><td class="k">'.L\Files.':</td><td><ul class="task_files">';
            foreach ($d['files'] as $f) {
                $rez .= '<li><a href="#" name="file" fid="'.$f['id'].
                    '" onclick="App.mainViewPort.fireEvent(\'fileopen\', {id:'.$f['id'].
                    '})" class="dib lh16 icon-padding file-unknown file-'.Files::getExtension($f['name']).'">'.
                    $f['name'].'</a></li>';
            }
            $rez .= '</ul></td></tr>';
        }

        if (!empty($d['reminds'])) {
            $rez .= '<tr><td class="k">'.L\Reminders.':</td><td><ul class="reminders">';
            $r = explode('-', $d['reminds']);
            foreach ($r as $rem) {
                $rem = explode('|', $rem);
                $units = '';
                switch ($rem[2]) {
                    case 1:
                        $units = L\ofMinutes;
                        break;
                    case 2:
                        $units = L\ofHours;
                        break;
                    case 3:
                        $units = L\ofDays;
                        break;
                    case 4:
                        $units = L\ofWeeks;
                        break;
                }
                $rez .= '<li><a name="rem_edit" rid="1" href="#">'.$rem[1].' '.$units.'</a></li>';
            }
            $rez .= '</ul></td></tr>';
        }
        $rez .= '</tbody></table></div>';

        return $rez;
    }

    /**
     * get a html block of active tasks under $pid object
     *
     * this block is used in preview of various objects
     *
     * @param  int     $pid
     * @return varchar html
     */
    public static function getActiveTasksBlockForPreview($pid)
    {
        $rez = array();
        $res = DB\dbQuery(
            'SELECT id
                ,name
                ,date_end
                ,DATEDIFF(`date_end`, UTC_DATE()) `days`
            FROM tree
            WHERE pid = $1
                AND `type` = 6
            ORDER BY date_end',
            $pid
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $rez[] = '<li class="icon-padding icon-task"><a class="task" href="#" nid="'.$r['id'].'">'.
                $r['name'].'</a>'.(empty($r['date_end']) ? '' : '<p class="cG">'.
                    Util\formatLeftDays($r['days']).'</p>');
        }
        $res->close();
        $rez = empty($rez) ? '' : '<ul class="obj-files">'.implode('', $rez).'</ul>';

        return $rez;
    }

    /**
     * set additional data for storing into solr
     * @param  reference $object_record
     * @return void
     */
    public static function getSolrData(&$object_record)
    {
        $res = DB\dbQuery(
            'SELECT
                title
                ,status
                ,category_id
                ,importance
                ,privacy
                ,responsible_user_ids
                ,autoclose
                ,description
                ,parent_ids
                ,child_ids
                ,missed
                ,DATE_FORMAT(completed, \'%Y-%m-%dT%H:%i:%sZ\') `completed`
                ,cid
            FROM tasks where id = $1',
            $object_record['id']
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $object_record['status'] = $r['status'];
            $object_record['importance'] = $r['importance'];
            $object_record['category_id'] = $r['category_id'];
            $object_record['completed'] = $r['completed'];
            $object_record['parent_ids'] = empty($r['parent_ids']) ? null : explode(',', $r['parent_ids']);
            if (!empty($r['responsible_user_ids'])) {
                $object_record['user_ids'] = explode(',', $r['responsible_user_ids']);
            }
            $object_record['content'] = $r['description'];
        }
        $res->close();
    }

    /**
     * set additional data for storing into solr for a set of records
     * @param  reference $object_records
     * @return void
     */

    public static function getBulkSolrData(&$object_records)
    {
        $process_object_ids = array();
        foreach ($object_records as $object_id => $object_record) {
            if (@$object_record['template_type'] == 'task') {
                $process_object_ids[] = $object_id;
            }
        }
        if (empty($process_object_ids)) {
            return;
        }

        $res = DB\dbQuery(
            'SELECT
                id
                ,title
                ,status
                ,category_id
                ,importance
                ,privacy
                ,responsible_user_ids
                ,autoclose
                ,description
                ,parent_ids
                ,child_ids
                ,missed
                ,DATE_FORMAT(completed, \'%Y-%m-%dT%H:%i:%sZ\') `completed`
                ,cid
            FROM tasks where id in ('.implode(',', $process_object_ids).')'
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $object_records[$r['id']]['status'] = $r['status'];
            $object_records[$r['id']]['importance'] = $r['importance'];
            $object_records[$r['id']]['category_id'] = $r['category_id'];
            $object_records[$r['id']]['completed'] = $r['completed'];
            $object_records[$r['id']]['parent_ids'] = empty($r['parent_ids']) ? null : explode(',', $r['parent_ids']);
            if (!empty($r['responsible_user_ids'])) {
                $object_records[$r['id']]['user_ids'] = explode(',', $r['responsible_user_ids']);
            }
            $object_records[$r['id']]['content'] = $r['description'];
        }
        $res->close();
    }
}
