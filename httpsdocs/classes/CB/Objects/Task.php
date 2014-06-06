<?php
namespace CB\Objects;

use CB\DB;
use CB\Util;

class Task extends Object
{

    /**
     * create an object with specified params
     * @param  array $p object properties
     * @return int   created id
     */
    public function create($p = false)
    {
        if ($p === false) {
            $p = $this->data;
        }
        $this->data = $p;

        $this->setDateParamsFromData($p);

        return parent::create($p);
    }

    /**
     * internal function used by create method for creating custom data
     * @return void
     */
    protected function createCustomData()
    {
        parent::createCustomData();

        /* saving template data to templates and templates_structure tables */
        @$allday = $this->getFieldValue('allday', 0)['value'];
        @$dateStart = ($allday == 1)
            ? $this->getFieldValue('date_start', 0)['value']
            : $this->getFieldValue('datetime_start', 0)['value'];
        @$dateEnd = ($allday == 1)
            ? $this->getFieldValue('date_end', 0)['value']
            : $this->getFieldValue('datetime_end', 0)['value'];

        $dateStart = empty($dateStart) ? null : Util\dateISOToMysql($dateStart);
        $dateEnd = empty($dateEnd) ? null : Util\dateISOToMysql($dateEnd);

        $status = 2; // active
        if (!empty($dateEnd)) {
            if (strtotime($dateEnd) < strtotime('now')) {
                $status = 1;
            }

        }

        @$params = array(
            $this->id
            ,$this->getFieldValue('_title', 0)['value'].''
            ,$allday
            ,$dateStart
            ,$dateEnd
            ,$this->getFieldValue('importance', 0)['value']
            ,$this->getFieldValue('category', 0)['value']
            ,$this->getFieldValue('assigned', 0)['value'].''
            ,$this->getFieldValue('description', 0)['value']
            ,$status
            ,$_SESSION['user']['id']
        );

        DB\dbQuery(
            'INSERT into tasks
            (id, title, allday, date_start, date_end, importance, category_id, responsible_user_ids, description, status, cid)
            VALUES($1, $2, $3, $4, $5, $6, $7, $8, $9, $10,$11)',
            $params
        ) or die(DB\dbQueryError());

        /* adding to log that will create notification */
        $remind_users = implode(',', Util\toNumericArray($this->getFieldValue('assigned', 0)['value']));

        // $logParams = array(
        //     'action_type' => 21
        //     ,'task_id' => $this->id
        //     ,'to_user_ids' => $remind_users
        //     ,'remind_users' => $remind_users
        //     ,'info' => 'title: '.$this->data['name']
        // );

        // \CB\Log::add($logParams);
        /***/

        // save reminds
        $reminds = @$this->data['data']['reminders'];
        if (isset($reminds['childs'])) {
            $reminds = array($reminds);
        }

        $p = array();
        foreach ($reminds as $remind) {
            if (!empty($remind['childs']['count'])) {
                //1 - by mail
                @$p[] = '1|'.$remind['childs']['count'].'|'.$remind['childs']['units'];
            }
        }

        \CB\Tasks::saveReminds(
            array(
                'id' => $this->id
                ,'reminds' => implode('-', $p)
            )
        );

    }

    /**
     * load template custom data
     */
    protected function loadCustomData()
    {
        parent::loadCustomData();

        $d = &$this->data;

        if (empty($d['data'])) {
            $d['data'] = array();
        }
        $res = DB\dbQuery(
            'SELECT t.title `_title`
                ,t.date_start
                ,t.date_end
                ,t.allday
                ,t.importance
                ,t.category_id
                ,t.responsible_user_ids `assigned`
                ,t.description
                ,t.status
                ,(SELECT reminds FROM tasks_reminders WHERE task_id = $1 AND user_id = $2) reminds
                ,DATE_FORMAT(t.completed, \'%Y-%m-%dT%H:%i:%sZ\') `completed`
            FROM tasks t
            WHERE t.id = $1',
            array(
                $this->id
                ,$_SESSION['user']['id']
            )
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            if (!empty($r['status'])) {
                $d['status'] = $r['status'];
            }
            if (!empty($r['user_status'])) {
                $d['user_status'] = $r['user_status'];
            }
            if (!empty($r['completed'])) {
                $d['completed'] = $r['completed'];
            }
            unset($r['status']);
            unset($r['user_status']);
            unset($r['completed']);

            $r['allday'] = array(
                'value' => $r['allday']
            );
            if ($r['allday']['value'] == 1) {
                $r['allday']['childs']['date_start'] = Util\dateMysqlToISO($r['date_start']);
                $r['allday']['childs']['date_end'] = Util\dateMysqlToISO($r['date_end']);
            } else {
                $r['allday']['childs']['datetime_start'] = Util\dateMysqlToISO($r['date_start']);
                $r['allday']['childs']['datetime_end'] = Util\dateMysqlToISO($r['date_end']);
            }
            unset($r['date_start']);
            unset($r['date_end']);

            $re = explode('-', $r['reminds']); //1|10|1-1|10|2
            unset($r['reminds']);
            foreach ($re as $remind) {
                if (empty($remind)) {
                    continue;
                }
                $remind = explode('|', $remind);
                $r['reminders'][] = array(
                    'childs' => array(
                        'count' => $remind[1]
                        ,'units' => $remind[2]
                    )
                );
            }

            $d['data'] = array_merge($d['data'], $r);
        }

        /* add possible action flags*/
        \CB\Tasks::setTaskActionFlags($d);
    }

    /**
     * update object
     * @param  array   $p optional properties. If not specified then $this-data is used
     * @return boolean
     */
    public function update($p = false)
    {
        if ($p === false) {
            $p = $this->data;
        }
        $this->data = $p;

        $this->setDateParamsFromData($p);

        return parent::update($p);
    }

    /**
     * update objects custom data
     * @return boolean
     */
    protected function updateCustomData()
    {
        parent::updateCustomData();

        /* saving template data to templates and templates_structure tables */
        @$allday = $this->getFieldValue('allday', 0)['value'];
        @$dateStart = ($allday == 1)
            ? $this->getFieldValue('date_start', 0)['value']
            : $this->getFieldValue('datetime_start', 0)['value'];
        @$dateEnd = ($allday == 1)
            ? $this->getFieldValue('date_end', 0)['value']
            : $this->getFieldValue('datetime_end', 0)['value'];

        $dateStart = empty($dateStart) ? null : Util\dateISOToMysql($dateStart);
        $dateEnd = empty($dateEnd) ? null : Util\dateISOToMysql($dateEnd);

        if (empty($this->data['status']) || in_array($this->data['status'], array(1, 2))) {
            $this->data['status'] = 2; // active
            if (!empty($dateEnd)) {
                if (strtotime($dateEnd) < strtotime('now')) {
                    $this->data['status'] = 1;
                }
            }
        }
        /* get previous responsible users to notify the users that has been removed */
        $oldResponsibleUsers = array();
        $res = DB\dbQuery(
            'SELECT responsible_user_ids
            FROM tasks
            WHERE id = $1',
            $this->id
        ) or die(DB\dbQueryError());
        if ($r = $res->fetch_assoc()) {
            $oldResponsibleUsers = Util\toNumericArray($r['responsible_user_ids']);
        }
        $res->close();
        /* end of get previous responsible users to notify the users that has been removed */

        @$params = array(
            $this->id
            ,$this->getFieldValue('_title', 0)['value']
            ,$allday
            ,$dateStart
            ,$dateEnd
            ,$this->getFieldValue('importance', 0)['value']
            ,$this->getFieldValue('category', 0)['value']
            ,$this->getFieldValue('assigned', 0)['value']
            ,$this->getFieldValue('description', 0)['value']
            ,$this->data['status']
        );

        DB\dbQuery(
            'UPDATE tasks
            SET
                title = $2
                ,allday = $3
                ,date_start = $4
                ,date_end = $5
                ,importance = $6
                ,category_id = $7
                ,responsible_user_ids = $8
                ,description = $9
                ,status = $10
            WHERE id = $1',
            $params
        ) or die(DB\dbQueryError());

        /* adding to log that will create notification */
        $remindUsers = Util\toNumericArray($this->getFieldValue('assigned', 0)['value']);
        $toUserIds = implode(',', $remindUsers);
        if (!empty($oldResponsibleUsers)) {
            $remindUsers = array_merge($remindUsers, $oldResponsibleUsers);
            $remindUsers = array_unique($remindUsers);
        }
        $remindUsers = implode(',', $remindUsers);

        // $logParams = array(
        //     'action_type' => 22
        //     ,'task_id' => $this->id
        //     ,'to_user_ids' => $toUserIds
        //     ,'remind_users' => $remindUsers
        //     ,'info' => 'title: '.$this->data['name']
        // );

        // \CB\Log::add($logParams);
        /***/

        // save reminds
        $reminds = @$this->data['data']['reminders'];
        if (empty($reminds)) {
            $reminds = array();
        }

        if (isset($reminds['childs'])) {
            $reminds = array($reminds);
        }

        $p = array();
        foreach ($reminds as $remind) {
            if (!empty($remind['childs']['count'])) {
                // 1 - by mail
                @$p[] = '1|'.$remind['childs']['count'].'|'.$remind['childs']['units'];
            }
        }
        \CB\Tasks::saveReminds(
            array(
                'id' => $this->id
                ,'reminds' => implode('-', $p)
            )
        );
    }

    protected function copyCustomDataTo($targetId)
    {
        // - task with its responsible users and reminders

        // copy data from tasks table
        DB\dbQuery(
            'INSERT INTO `tasks`
                (`id`
                ,`title`
                ,`date_start`
                ,`date_end`
                ,`allday`
                ,`importance`
                ,`category_id`
                ,`privacy`
                ,`responsible_user_ids`
                ,`autoclose`
                ,`description`
                ,`parent_ids`
                ,`child_ids`
                ,`time`
                ,`reminds`
                ,`status`
                ,`missed`
                ,`completed`
                ,`cid`
                ,`cdate`
                ,`uid`
                ,`udate`)
            SELECT
                $2
                ,`title`
                ,`date_start`
                ,`date_end`
                ,`allday`
                ,`importance`
                ,`category_id`
                ,`privacy`
                ,`responsible_user_ids`
                ,`autoclose`
                ,`description`
                ,`parent_ids`
                ,`child_ids`
                ,`time`
                ,`reminds`
                ,`status`
                ,`missed`
                ,`completed`
                ,`cid`
                ,`cdate`
                ,$3
                ,CURRENT_TIMESTAMP
            FROM `tasks`
            WHERE id = $1',
            array(
                $this->id
                ,$targetId
                ,$_SESSION['user']['id']
            )
        ) or die(DB\dbQueryError());

        // copy data from tasks_responsible_users table
        DB\dbQuery(
            'INSERT INTO `tasks_responsible_users`
                (`task_id`
                ,`user_id`
                ,`status`
                ,`thesauri_response_id`
                ,`time`)
            SELECT
                $2
                ,`user_id`
                ,`status`
                ,`thesauri_response_id`
                ,`time`
            FROM `tasks_responsible_users`
            WHERE task_id = $1',
            array(
                $this->id
                ,$targetId
            )
        ) or die(DB\dbQueryError());

        // copy data from tasks_reminders table
        DB\dbQuery(
            'INSERT INTO `tasks_reminders`
                (`task_id`
                ,`user_id`
                ,`reminds`)
            SELECT
                $2
                ,`user_id`
                ,`reminds`
            FROM `tasks_reminders`
            WHERE task_id = $1',
            array(
                $this->id
                ,$targetId
            )
        ) or die(DB\dbQueryError());
    }

    protected function setDateParamsFromData(&$p)
    {
        /* analize if task dates are set */
        switch ($this->getFieldValue('allday', 0)['value']) {
            case 1:
                $p['date'] = substr($this->getFieldValue('date_start', 0)['value'], 0, 10);
                $p['date_end'] = substr($this->getFieldValue('date_end', 0)['value'], 0, 10);
                break;
            case -1:
                $p['date'] = $this->getFieldValue('datetime_start', 0)['value'];
                $p['date_end'] = $this->getFieldValue('datetime_end', 0)['value'];
                break;
            default:
                $p['date'] = null;
                $p['date_end'] = null;
        }
    }
}
