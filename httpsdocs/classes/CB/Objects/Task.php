<?php
namespace CB\Objects;

use CB\DB;
use CB\Util;

class Task extends Object
{

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
        );

        DB\dbQuery(
            'INSERT into tasks
            (id, title, allday, date_start, date_end, importance, category_id, responsible_user_ids, description)
            VALUES($1, $2, $3, $4, $5, $6, $7, $8, $9)',
            $params
        ) or die(DB\dbQueryError());

        // save reminds
        $reminds = @$this->data['data']['reminders'];
        if (isset($reminds['childs'])) {
            $reminds = array($reminds);
        }

        $p = array();
        foreach ($reminds as $remind) {
            if (!empty($remind['childs']['count'])) {
                @$p[] = $_SESSION['user']['id'].'|'.$remind['childs']['count'].'|'.$remind['childs']['units'];
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

        if (empty($this->data['data'])) {
            $this->data['data'] = array();
        }
        $res = DB\dbQuery(
            'SELECT title `_title`
                ,date_start
                ,date_end
                ,allday
                ,importance
                ,category_id
                ,responsible_user_ids `assigned`
                ,description
                ,(SELECT reminds FROM tasks_reminders WHERE task_id = $1 AND user_id = $2) reminds
            FROM tasks t
            WHERE id = $1',
            array(
                $this->id
                ,$_SESSION['user']['id']
            )
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
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

            $this->data['data'] = array_merge($this->data['data'], $r);
        }
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
            WHERE id = $1',
            $params
        ) or die(DB\dbQueryError());

        // save reminds
        $reminds = @$this->data['data']['reminders'];
        if (isset($reminds['childs'])) {
            $reminds = array($reminds);
        }

        $p = array();
        foreach ($reminds as $remind) {
            if (!empty($remind['childs']['count'])) {
                $p[] = @$_SESSION['user']['id'].'|'.$remind['childs']['count'].'|'.$remind['childs']['units'];
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
}
