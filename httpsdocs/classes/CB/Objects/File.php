<?php
namespace CB\Objects;

use CB\DB as DB;

/**
 * class for casebox files objects
 */
class File extends Object
{

    /**
     * internal function used by create method for creating custom data
     * @return void
     */
    protected function createCustomData()
    {
        parent::createCustomData();

        $res = DB\dbQuery(
            'INSERT INTO files
                (id
                ,content_id
                ,`date`
                ,`name`
                ,`cid`
                )
            VALUES (
                $1
                ,$2
                ,$3
                ,$4
                ,$5
            )',
            array(
                $this->id
                ,@$this->data['content_id']
                ,@$this->data['date']
                ,@$this->data['name']
                ,@$this->data['cid']
            )
        ) or die(DB\dbQueryError());

    }

    /**
     * load custom data for $this->id
     *
     * @return void
     */
    protected function loadCustomData()
    {

        parent::loadCustomData();

        /* load custom data from objects table */
        $res = DB\dbQuery(
            'SELECT f.content_id
                ,fc.size
                ,fc.pages
                ,fc.type
                ,fc.path `content_path`
                ,fc.md5
            FROM files f
            LEFT JOIN files_content fc ON f.content_id = fc.id
            WHERE f.id = $1',
            $this->id
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $this->data = array_merge($this->data, $r);
        }
        $res->close();

        /* get versions */

        $res = DB\dbQuery(
            'SELECT
                v.id
                ,v.`date`
                ,v.`name`
                ,v.cid
                ,v.uid
                ,v.cdate
                ,v.udate
                ,fc.size
                ,fc.pages
                ,fc.type
            FROM files_versions v
                LEFT JOIN files_content fc on fc.id = v.content_id
            WHERE v.file_id = $1
            ORDER BY v.cdate DESC',
            $this->id
        ) or die(DB\dbQueryError());
        while ($r = $res->fetch_assoc()) {
            $this->data['versions'][] = $r;
        }
        $res->close();
    }

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
