<?php
namespace CB;

use CB\L;
use CB\Util;

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
                ,DATEDIFF(t.`date_end`, UTC_DATE()) `days`
                ,m.pid
                ,m.template_id
                ,m.cid
                ,m.cdate
                ,(SELECT reminds
                     FROM tasks_reminders
                     WHERE task_id = $1
                         AND user_id = $2) reminds
                ,(SELECT name FROM tree WHERE id = ti.case_id) `case`
                ,(SELECT name FROM tree WHERE id = t.object_id) `object`
                ,t.status
                ,t.completed `task_d_closed`
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
            $r['task_d_closed'] = Util\dateMysqlToISO($r['task_d_closed']);
            $r['path'] = explode(',', $r['path']);
            array_pop($r['path']);
            $r['path'] = implode('/', $r['path']);
            $c = explode('/', $r['path']);
            $r['create_in'] = array_pop($c);
            $rez = array('success' => true, 'data' => $r);
        } else {
            throw new \Exception(L\get('Object_not_found'));
        }
        $res->close();

        $rez['data']['files'] = $this->getTaskFiles($id);

        // get responsible users and their completion status
        $res = DB\dbQuery(
            'SELECT u.id
                ,ru.status
                ,ru.thesauri_response_id
                ,ru.`time`
            FROM tasks_responsible_users ru
            JOIN users_groups u ON ru.user_id = u.id
            WHERE ru.task_id = $1
            ORDER BY u.l' . Config::get('user_language_index'),
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
        $p['type'] = 0;

        //suppose that only notifications are changed
        $log_action_type = 25;

        $removed_responsible_users = array();

        if (!isset($p['id'])) {
            $p['id'] = null;
        }
        if (!Util\validId($p['id']) || Security::canManageTask($p['id'])) {
            /* update the task details only if is admin or owner of the task /**/

            // suppose adding new task
            $log_action_type = 21;
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

            if ($p['template_id'] !== Config::get('default_event_template')) {
                $p['date_end'] = empty($p['date_end']) ? null : Util\dateISOToMysql($p['date_end']);
            } else {
                $p['date_end'] = null;
            }

            if (empty($p['time'])) {
                $p['time'] = null;
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
                    //all parent tasks are completed
                    $status = 2;
                }
                $res->close();
            }
            /* end of estimating deadline status in dependance with parent tasks statuses */
            if (empty($p['id'])) {
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
                    ,allday = $15',
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

        // $logParams = array(
        //     'action_type' => $log_action_type
        //     ,'task_id' => $p['id']
        //     ,'to_user_ids' => $p['responsible_user_ids']
        //     ,'remind_users' => $remind_users
        //     ,'removed_users' => $removed_responsible_users
        //     ,'info' => 'title: '.$p['title']);
        // Log::add($logParams);

        $this->saveReminds($p);

        switch ($log_action_type) {
            case 21: //created
                // fireEvent('nodeDbCreate', $p);
                break;
            case 22: //updated
                // fireEvent('nodeDbUpdate', $p);
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
            throw new \Exception(L\get('Access_denied'), 1);
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

            DB\dbQuery(
                'UPDATE tree set `date` = $2, date_end = $3, updated = 1 WHERE id = $1',
                array($p['id'], $p['date_start'], $p['date_end'])
            ) or die(DB\dbQueryError());
        } else {
            $rez['success'] = false;
        }
        $res->close();
        Objects::updateCaseUpdateInfo($p['id']);

        $solr = new  Solr\Client();
        $solr->updateTree(array('id' => $p['id']));

        return $rez;
    }

    /**
     * save reminds for a task with deadlines
     * @param  array   $p               task properties
     * @param  integer $log_action_type
     * @return json    response
     */
    public static function saveReminds($p, $log_action_type = 25)
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
            throw new \Exception(L\get('Access_denied'));
        }
        if (!in_array($p['user_id'], $responsible_users)) {
            throw new \Exception(L\get('Wrong_id'));
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

        // log the action
        $o = new Objects\Task($p['id']);
        $o->load();

        $logParams = array(
            'type' => 'status_change'
            ,'new' => $o
        );

        Log::add($logParams);

        // Log::add(
        //     array(
        //         'action_type' => $action_type
        //         ,'task_id' => $p['id']
        //         ,'to_user_ids' => $p['user_id']
        //         ,'remind_users' => $task['cid'].','.$p['user_id']
        //         ,'autoclosed' => $autoclosed
        //         ,'info' => 'title: '.$task['title']
        //     )
        // ); // TO REVIEW

        DB\dbQuery('UPDATE tree set updated = 1 where id = $1', $p['id']) or die(DB\dbQueryError());

        Objects::updateCaseUpdateInfo($p['id']);

        $solr = new  Solr\Client();
        $solr->updateTree(array('id' => $p['id']));

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
            throw new \Exception(L\get('Access_denied'));
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
                throw new \Exception(L\get('Task_already_completed'));
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

        $this->checkAutocloseTask($p['id']);

        // log the action
        $o = new Objects\Task($p['id']);
        $o->load();

        $logParams = array(
            'type' => 'complete'
            ,'new' => $o
        );

        Log::add($logParams);

        // Log::add(
        //     array(
        //         'action_type' => 23
        //         ,'task_id' => $p['id']
        //         ,'remind_users' => $task['cid'].','.$task['responsible_user_ids']
        //         ,'autoclosed' => $this->checkAutocloseTask($p['id'])
        //         ,'info' => 'title: '.$task['title']
        //     )
        // );

        DB\dbQuery('UPDATE tree set updated = 1 where id = $1', $p['id']) or die(DB\dbQueryError());

        Objects::updateCaseUpdateInfo($p['id']);

        $solr = new  Solr\Client();
        $solr->updateTree(array('id' => $p['id']));

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
            'SELECT t.cid
                 , t.name
                 , tt.responsible_user_ids
            FROM tree t
            LEFT JOIN tasks tt on t.id = tt.id
            WHERE t.id = $1',
            $id
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $task = $r;
        }
        $res->close();
        if (($_SESSION['user']['id'] !== $task['cid']) && !Security::isAdmin()) {
            return  array('success' => false, 'msg' => L\get('No_access_to_close_task'));
        }
        DB\dbQuery(
            'UPDATE tasks SET status = 3 WHERE id = $1',
            $id
        ) or die(DB\dbQueryError());


        // log the action
        $o = new Objects\Task($id);
        $o->load();

        $logParams = array(
            'type' => 'close'
            ,'new' => $o
        );

        Log::add($logParams);

        /* log and notify all users about task closing */
        // Log::add(
        //     array(
        //         'action_type' => 27
        //         ,'task_id' => $id
        //         ,'remind_users' => $task['cid'].','.$task['responsible_user_ids']
        //         ,'info' => 'title: '.$task['name']
        //     )
        // );

        DB\dbQuery('UPDATE tree set updated = 1 where id = $1', $id) or die(DB\dbQueryError());

        $this->updateChildTasks($id);

        Objects::updateCaseUpdateInfo($id);

        $solr = new  Solr\Client();
        $solr->updateTree(array('id' => $id));

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
            'SELECT t.cid
                ,t.name
                ,tt.responsible_user_ids
            FROM tree t
            JOIN tasks tt
                ON t.id = tt.id
            WHERE t.id = $1',
            $id
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $task  = $r;
        }
        $res->close();
        if (($_SESSION['user']['id'] !== $task['cid']) && !Security::isAdmin()) {
            return  array('success' => false, 'msg' => L\get('No_access_for_this_action'));
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

        // log the action
        $o = new Objects\Task($id);
        $o->load();

        $logParams = array(
            'type' => 'reopen'
            ,'new' => $o
        );

        Log::add($logParams);
        /* log and notify all users about task closing */
        // Log::add(
        //     array(
        //         'action_type' => 31
        //         ,'task_id' => $id
        //         ,'remind_users' => $task['cid'].','.$task['responsible_user_ids']
        //         ,'info' => 'title: '.$task['name']
        //     )
        // );

        DB\dbQuery('UPDATE tree set updated = 1 where id = $1', $id) or die(DB\dbQueryError());

        $this->updateChildTasks($id);

        Objects::updateCaseUpdateInfo($id);

        $solr = new  Solr\Client();
        $solr->updateTree(array('id' => $id));

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
        if (!empty($task['task_d_closed'])) {
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
     * get task files
     * @param  int   $taskId
     * @return array
     */
    protected static function getTaskFiles($taskId, $html = false)
    {
        $files = array();

        $search = new Search();
        $rez = $search->query(
            array(
                'pid' => $taskId
                ,'fl' => 'id,name'
                // ,'template_types' => 'file'
                ,'fq' => array(
                    '(template_type:file) OR (target_type:file)'
                )
            )
        );

        foreach ($rez['data'] as $file) {
            $files[] = $file;
        }

        if ($html === false) {
            return $files;
        }

        $rez = '';
        $coreUrl = Config::get('core_url');


        if (!empty($files)) {
            $rez .= '<tr><td style="width: 1%; padding: 5px 15px 5px 0; color: #777; vertical-align:top">'.
                L\get('Files').':</td><td style="vertical-align:top"><ul style="list-style: none; padding:0;margin:0">';

            foreach ($files as $f) {
                $rez .= '<li style="margin:0;padding: 3px 0"><a href="' . $coreUrl . 'view/' . $f['id'] . '/" name="file" fid="'.$f['id'].
                    '" style="text-decoration: underline; color: #15C" taget="_blank"><img style="float:left;margin-right:5px" src="'.
                    $coreUrl.'css/i/ext/'.Files::getIconFileName($f['name']).'"> '.$f['name'].'</a></li>';
            }

            $rez .= '</ul></td></tr>';
        }

        return $rez;
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
        $coreUrl = Config::get('core_url');

        $user = array();

        if ($user_id == false) {
            $user = &$_SESSION['user'];
        } else {
            $user = User::getPreferences($user_id);
        }
        $res = DB\dbQuery(
            'SELECT
                `title`
                ,date_start
                ,date_end
                ,description
                ,status
                ,`type`
                ,allday
                ,cid
                ,ti.path `path_text`
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
            $datetime_period = ($r['allday'] == 1)
                ? Util\formatDatePeriod($r['date_start'], $r['date_end'])
                : Util\formatDateTimePeriod($r['date_start'], $r['date_end'], @$user['cfg']['timezone']);

            $created_date_text = Util\formatMysqlDate($r['cdate'], 'Y, F j H:i', @$user['cfg']['timezone']);

            $tickImage = 'data:image/png;base64,'.base64_encode(file_get_contents(DOC_ROOT . 'css/i/ico/tick-circle.png'));

            $users = array();
            $ures = DB\dbQuery(
                'SELECT u.id
                    ,u.`name`
                    ,first_name
                    ,last_name
                    ,u.photo
                    ,u.sex
                    ,ru.status
                    ,ru.time
                FROM users_groups u
                LEFT JOIN tasks_responsible_users ru ON u.id = ru.user_id
                    AND ru.task_id = $1
                WHERE u.id IN (0'.$r['responsible_user_ids'].')
                ORDER BY 1',
                $id
            ) or die(DB\dbQueryError());

            while ($ur = $ures->fetch_assoc()) {
                $name = User::getDisplayName($ur);

                $photoFile = User::getPhotoFilename($ur, true);
                $photo = 'data:image/png;base64,'.base64_encode(file_get_contents($photoFile));

                $users[] = "\n\r".'<tr><td style="width: 1% !important; padding:5px 5px 5px 0px; vertical-align:top; white-space: nowrap">'.
                "\n\r".'<img src="' . $photo . '" style="width:32px; height: 32px" alt="'.$name.'" title="'.$name.'"/>'.
                "\n\r".( ($ur['status'] == 1) ? '<img src="' . $tickImage . '" style="width:16px;height:16px; margin-left: -16px"/>': '').
                "\n\r".'</td><td style="padding: 5px 5px 5px 0; vertical-align:top"><b>'.$name.'</b>'.
                "\n\r".'<p style="color:#777;margin:0;padding:0">'.
                "\n\r".( ($ur['status'] == 1) ? L\get('Completed', $user['language_id']).': <span style="color: #777" title="'.$ur['time'].'">'.
                    Util\formatMysqlDate($ur['time'], 'Y, F j H:i', @$user['cfg']['timezone']).'</span>' : L\get('waitingForAction', $user['language_id']) ).
                "\n\r".'</p>'.
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

            $ownerName = User::getDisplayName($r['cid']);
            $ownerPhoto = 'data:image/png;base64,'.base64_encode(file_get_contents(User::getPhotoFilename($r['cid'], true)));

            // create files block
            $files_text = static::getTaskFiles($id, true);

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
                    ,'font-size: 1.5em; display: block;'.( ($r['status'] == 3 ) ? 'color: #555; text-decoration: line-through' : '')
                    ,'<a href="' . $coreUrl . 'view/' . $id . '/">' . Util\adjustTextForDisplay($r['title']) . '</a>'
                    ,$datetime_period
                    ,nl2br(Util\adjustTextForDisplay($r['description']))
                    ,L\get('Status', $user['language_id'])
                    ,'status-style'
                    ,L\get('taskStatus'.$r['status'], $user['language_id'])
                    ,L\get('Created', $user['language_id'])
                    ,$created_date_text
                    ,''
                    ,''
                    ,L\get('Category', $user['language_id'])
                    ,'category_style'
                    ,''
                    ,L\get('Path', $user['language_id'])
                    ,Util\adjustTextForDisplay($r['path_text'])
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

        static::setTaskActionFlags($d);

        $actions = array();

        if (!empty($d['can']['close'])) {
            $actions[] = '<a action="close" class="taskA click">'.L\get('Close').'</a>';
        }
        if (!empty($d['can']['reopen'])) {
            $actions[] = '<a action="reopen" class="taskA click">'.L\get('Reopen').'</a>';
        }
        if (empty($d['can']['close']) && !empty($d['can']['complete'])) {
            $actions[] = '<a action="complete" class="taskA click">'.L\get('Complete').'</a>';
        }

        //<h2 '.( ($d['status'] == 3) ? 'class=\'completed\'"' : '' ).'>{name}</h2>
        $rez = '<div class="taskview">
            <div class="datetime">{datetime_period}</div>
            <div class="info">{description}</div>
            <table class="props"><tbody>
            <tr><td class="k">'.L\get('Status').':</td><td><span class="status{status}">{status_text}</span></td></tr>
            <tr><td class="k">'.L\get('Importance').':</td><td>{importance_text}</td></tr>
            <tr><td class="k">'.L\get('Category').':</td><td>{category_text}</td></tr>
            <tr><td class="k">'.L\get('Path').':</td><td><a class="path" path="{path}" href="#">{path_text}</a></td></tr>
            <tr><td class="k">'.L\get('Owner').':</td><td><table class="people"><tbody>
                <tr><td class="user"><img class="photo32" src="photo/{cid}.jpg?32=' . User::getPhotoParam($d['cid']). '" style="width:32px; height: 32px" alt="{creator_name}" title="{creator_name}"></td><td><b>{creator_name}</b><p class="gr">'.L\get('Created').': '.
                '<span class="dttm" title="{full_created_date_text}">{create_date}</span></p></td></tr></tbody></table></td></tr>';

        $date_format = str_replace('%', '', $_SESSION['user']['cfg']['short_date_format']);

        $d['datetime_period'] = ($d['allday'] == 1)
            ? Util\formatDatePeriod($d['date_start'], $d['date_end'])
            : Util\formatDateTimePeriod($d['date_start'], $d['date_end'], @$_SESSION['user']['cfg']['timezone']);

        $params = array( '{name}' => Util\adjustTextForDisplay($d['title'])
            ,'{datetime_period}' => $d['datetime_period']
            ,'{description}' => nl2br(Util\adjustTextForDisplay($d['description']))
            ,'{status}' => $d['status']
            ,'{status_text}' => L\get('taskStatus'.$d['status'])
            ,'{importance_text}' => ''
            ,'{category_text}' => ''
            ,'{path}' => $d['path']
            ,'{path_text}' => Util\adjustTextForDisplay($d['pathtext'])
            ,'{cid}' => $d['cid']
            ,'{creator_name}' => User::getDisplayName($d['cid'])
            ,'{full_created_date_text}' => Util\formatDateTimePeriod($d['cdate'], null, @$_SESSION['user']['cfg']['timezone'])
            ,'{create_date}' => Util\formatDateTimePeriod($d['cdate'], null, @$_SESSION['user']['cfg']['timezone'])
            );
        $rez = str_replace(array_keys($params), array_values($params), $rez);

        if (!empty($d['users'])) {
            $rez .= '<tr><td class="k">'.L\get('TaskAssigned').':</td><td><table class="people"><tbody>';
            foreach ($d['users'] as $u) {
                $un = User::getDisplayName($u['id']);
                $rez .= '<tr><td class="user"><div style="position: relative">'.
                '<img class="photo32" src="photo/'.$u['id'].'.jpg?32=' . User::getPhotoParam($u['id']). '" style="width:32px; height: 32px" alt="'.$un.'" title="'.$un.'">'.
                ( ($u['status'] == 1 ) ? '<img class="done icon icon-tick-circle" src="/css/i/s.gif" />': "").
                '</div></td><td><b>'.$un.'</b>'.
                '<p class="gr">'.(
                    ($u['status'] == 1)
                    ? L\get('Completed').': '.date($date_format.' H:i', strtotime($u['time'])).
                        ( (!empty($d['can']['edit'])) ? '<a class="bt taskA click" action="markincomplete" uid="'.$u['id'].'">'.L\get('revoke').'</a>' : '')
                    : L\get('waitingForAction').
                        ((!empty($d['can']['edit'])) ? '<a class="bt taskA click" action="markcomplete" uid="'.$u['id'].'">'.L\get('complete').'</a>' : '' )
                ).'</p></td></tr>';
            }
            $rez .= '</tbody></table></td></tr>';
        }

        if (!empty($d['reminds'])) {
            $rez .= '<tr><td class="k">'.L\get('Reminders').':</td><td><ul class="reminders">';
            $r = explode('-', $d['reminds']);
            foreach ($r as $rem) {
                $rem = explode('|', $rem);
                $units = '';
                switch ($rem[2]) {
                    case 1:
                        $units = L\get('ofMinutes');
                        break;
                    case 2:
                        $units = L\get('ofHours');
                        break;
                    case 3:
                        $units = L\get('ofDays');
                        break;
                    case 4:
                        $units = L\get('ofWeeks');
                        break;
                }
                $rez .= '<li><a name="rem_edit" rid="1" href="#">'.$rem[1].' '.$units.'</a></li>';
            }
            $rez .= '</ul></td></tr>';
        }
        $rez .= '</tbody></table></div>';

        return array($rez);
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
     * @param  reference $objectRecord
     * @return void
     */
    public static function getSolrData(&$objectRecord)
    {
        //make standart analysis of task object
        Objects::getSolrData($objectRecord);

        //make custom analysis for tasks
        $obj = Objects::getCachedObject($objectRecord['id']);

        $objData = $obj->getData();
        $linearData = $obj->getLinearData();
        $template = $obj->getTemplate();

        $objectRecord['task_status'] = @$objData['status'];

        $user_ids = @$obj->getFieldValue('assigned', 0)['value'];
        if (!empty($user_ids)) {
            $user_ids = Util\toNumericArray($user_ids);
            $objectRecord['task_u_assignee'] = $user_ids;
        } else {
            $user_ids = array();
        }

        $user_ids[] = @Util\coalesce($objData['oid'], $objData['cid']);

        $objectRecord['task_u_all'] = array_unique($user_ids);

        $objectRecord['content'] = @$obj->getFieldValue('description', 0)['value'];

        if (!empty($objData['task_d_closed'])) {
            $objectRecord['task_d_closed'] = $objData['task_d_closed'];
        }

        //get users that didnt complete the task yet
        $objectRecord['task_u_ongoing'] = array();

        $res = DB\dbQuery(
            'SELECT user_id
            FROM tasks_responsible_users
            WHERE task_id = $1 and status = 0',
            $objectRecord['id']
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $objectRecord['task_u_ongoing'][] = $r['user_id'];
        }
        $res->close();

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
        if ($userId === false) {
            $userId = $_SESSION['user']['id'];
        }

        $isAdmin = \CB\Security::isAdmin();
        $taskTemplates = Templates::getIdsByType('task');
        $ta = array();
        foreach ($tasksDataArray as &$d) {
            if ((!in_array(@$d['template_id'], $taskTemplates)) ||
                empty($d['status'])
            ) {
                continue;
            }
            $ta[$d['id']] = &$d;

            $canEdit = ($d['status'] != 3) && ($isAdmin || ($d['cid'] == $userId));
            $d['can'] = array(
                'edit' => $canEdit
                ,'close' => $canEdit
                ,'reopen' => (($d['status'] == 3) && ($d['cid'] == $userId))
            );
        }

        if (empty($ta)) {
            return;
        }

        // select status of the user in tasks (completed or not)
        $res = DB\dbQuery(
            'SELECT task_id, status
            FROM `tasks_responsible_users`
            WHERE task_id in ('.implode(',', array_keys($ta)).')
                AND user_id = $1',
            $userId
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $userStatus = $r['status'];
            $d = &$ta[$r['task_id']];
            $canEdit = ($d['status'] != 3) && ($isAdmin || ($d['cid'] == $userId));
            $d['can']['complete'] = (($d['status'] != 3) && (empty($userStatus)));
        }
        $res->close();

    }
}
