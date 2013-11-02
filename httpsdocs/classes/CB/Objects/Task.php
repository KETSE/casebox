<?php
namespace CB\Objects;

use CB\DB as DB;

class Task extends Object
{

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
