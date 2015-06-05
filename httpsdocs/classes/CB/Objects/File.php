<?php
namespace CB\Objects;

use CB\Config;
use CB\DB;

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
     * update objects custom data
     * @return void
     */
    protected function updateCustomData()
    {
        parent::updateCustomData();

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
            )
            ON DUPLICATE KEY UPDATE
            content_id = COALESCE($2, content_id)
            ,`date` = $3
            ,name = $4
            ,cid = $5
            ,uid = $6',
            array(
                $this->id
                ,@$this->data['content_id']
                ,@$this->data['date']
                ,@$this->data['name']
                ,@$this->data['cid']
                ,$_SESSION['user']['id']
            )
        ) or die(DB\dbQueryError());

    }

    /**
     * method to collect solr data from object data
     * according to template fields configuration
     * and store it in sys_data onder "solr" property
     * @return void
     */
    protected function collectSolrData()
    {
        parent::collectSolrData();

        $filesPath = Config::get('files_dir');

        $sd = &$this->data['sys_data']['solr'];

        $res = DB\dbQuery(
            'SELECT f.id
            ,c.type
            ,c.size
            ,c.pages
            ,c.path
            ,f.name
            ,f.title
            ,f.cid
            ,f.content_id
            ,(select count(*) from files_versions where file_id = f.id) `versions`
            FROM files f
            LEFT JOIN files_content c
                ON f.content_id = c.id
            WHERE f.id = $1',
            $this->id
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $sd['size'] = $r['size'];
            $sd['versions'] = intval($r['versions']);

            // $content = $filesPath.$r['path'].DIRECTORY_SEPARATOR.$r['content_id'].'.gz';
            // if (file_exists($content)) {
            //     $content = file_get_contents($content);
            //     $content = gzuncompress($content);
            // } else {
            //     $content = '';
            // }
            // $objectRecord['content'] =
            //     Util\coalesce($r['title'], '')."\n".
            //     Util\coalesce($r['type'], '')."\n".
            //     (empty($objectRecord['content']) ? '' : $objectRecord['content'] . "\n").
            //     Util\coalesce($content, '');
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
