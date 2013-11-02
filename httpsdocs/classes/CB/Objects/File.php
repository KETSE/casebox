<?php
namespace CB\Objects;

use CB\DB as DB;

/**
 * class for casebox files objects
 */
class File extends Object
{

    /**
     * copy costom files data to targetId
     * @param  int  $targetId
     * @return void
     */
    protected function copyCustomDataTo($targetId)
    {
        // - files data, but without versions. Should we copy versions also?

        // copy files data
        DB\dbQuery(
            'INSERT INTO `files`
                (`id`
                ,`content_id`
                ,`date`
                ,`name`
                ,`title`
                ,`old_id`
                ,`old_name`
                ,`cid`
                ,`uid`
                ,`cdate`
                ,`udate`)
            SELECT
                $2
                ,`content_id`
                ,`date`
                ,`name`
                ,`title`
                ,`old_id`
                ,`old_name`
                ,`cid`
                ,$3
                ,`cdate`
                ,CURRENT_TIMESTAMP
            FROM `files`
            WHERE id = $1',
            array(
                $this->id
                ,$targetId
                ,$_SESSION['user']['id']
            )
        ) or die(DB\dbQueryError());
    }
}
