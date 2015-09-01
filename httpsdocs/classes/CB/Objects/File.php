<?php
namespace CB\Objects;

use CB\DB;
use CB\Config;
use CB\Objects;
use CB\User;
use CB\Util;
use CB\Log;

/**
 * class for casebox files objects
 */
class File extends Object
{

    /**
     * create method
     * @return void
     */
    public function create($p = false)
    {
        //disable default log from parent Object class
        //we'll set comments add as comment action for parent

        $disableActivityLogStatus = \CB\Config::getFlag('disableActivityLog');

        Config::setFlag('disableActivityLog', true);

        $rez = parent::create($p);

        Config::setFlag('disableActivityLog', $disableActivityLogStatus);

        $p = &$this->data;

        $this->parentObj = Objects::getCachedObject($p['pid']);

        $this->updateParentFollowers();

        $this->logAction(
            'file_upload',
            array(
                'file' => array(
                    'id' => $p['id'],
                    'name' => $p['name']
                )
            )
        );

        return $rez;
    }

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
                ,fc.type `content_type`
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
                ,fc.type content_type
            FROM files_versions v
                LEFT JOIN files_content fc on fc.id = v.content_id
            WHERE v.file_id = $1
            ORDER BY COALESCE(v.udate, v.cdate) DESC',
            $this->id
        ) or die(DB\dbQueryError());
        while ($r = $res->fetch_assoc()) {
            $this->data['versions'][] = $r;
        }
        $res->close();
    }

    /**
     * update file
     * @param  array   $p optional properties. If not specified then $this-data is used
     * @return boolean
     */
    public function update($p = false)
    {
        //disable default log from parent Object class
        Config::setFlag('disableActivityLog', true);

        $rez = parent::update($p);

        Config::setFlag('disableActivityLog', false);

        $p = &$this->data;

        $this->logAction(
            'file_update',
            array(
                'file' => array(
                    'id' => $p['id'],
                    'name' => $p['name']
                )
            )
        );

        return $rez;

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

        $sd = &$this->data['sys_data']['solr'];

        $res = DB\dbQuery(
            'SELECT c.size
            ,(SELECT count(*)
                FROM files_versions
                WHERE file_id = f.id
            ) `versions`
            FROM files f
            LEFT JOIN files_content c
                ON f.content_id = c.id
            WHERE f.id = $1',
            $this->id
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $sd['size'] = $r['size'];
            $sd['versions'] = intval($r['versions']);
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

    /**
     * function to update parent followers when uploading a file
     * with this user
     * @return void
     */
    protected function updateParentFollowers()
    {
        $p = &$this->data;

        $posd = $this->parentObj->getSysData();

        $newUserIds = array();

        $fu = empty($posd['fu'])
            ? array()
            : $posd['fu'];
        $uid = User::getId();

        if (!in_array($uid, $fu)) {
            $newUserIds[] = intval($uid);
        }

        //update only if new users added
        if (!empty($newUserIds)) {
            $fu = array_merge($fu, $newUserIds);
            $fu = Util\toNumericArray($fu);

            $posd['fu'] = array_unique($fu);

            $this->parentObj->updateSysData($posd);
        }
    }
}
