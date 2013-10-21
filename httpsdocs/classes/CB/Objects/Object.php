<?php
namespace CB\Objects;

use CB\L as L;
use CB\DB as DB;

/**
 * class for generic casebox objects
 */

class Object
{
    /**
     * object id
     * @var int
     */
    private $id = null;

    /**
     * object template
     * @var CB\Template object
     */
    private $template = null;

    /**
     * object data
     * @var array
     */
    private $data = array();

    public function __construct($id = null)
    {
        if (is_numeric($id)) {
            $this->id = $id;
        }
    }

    /**
     * load object data into $this->data
     * @param  int     $id
     * @return boolean
     */
    public function load($id = null)
    {
        if (!is_numeric($id)) {
            if (!is_numeric($this->id)) {
                throw new Exception("No object id specified for load", 1);
            }
            $id = $this->id;
        } else {
            $this->id = $id;
        }

        $sql = 'SELECT * FROM tree WHERE id = $1';
        $res = DB\dbQuery($sql, $id) or die(DB\dbQueryError());
        if ($r = $res->fetch_assoc()) {
            $this->data = $r;
        } else {
            // throw new Exception(L\Object_not_found, 1);
            return false;
        }
        $res->close();

        $this->template = \CB\Templates\SingletonCollection::getInstance()->getTemplate($this->data['template_id']);
        $this->loadCustomData();

        return true;
    }

    /**
     * load custom data for $this->id
     * @return void
     */
    private function loadCustomData()
    {
    }

    /**
     * copy an object to $pid or over $targetId
     *
     * better way to copy an object over another one is to delete the target,
     * but this could be very dangerous. We could delete required/important data
     * so i suggest to just mark overwriten object with dstatus = 3.
     * But in this situation appears another problem with child nodes.
     * Childs should be moved to new parent.
     *
     * @param  int $pid      if not specified then will be set to pid of targetId
     * @param  int $targetId
     * @return int the id of copied object
     */
    public function copyTo($pid = false, $targetId = false)
    {
        // check input params
        if (!is_numeric($this->id) ||
            (!is_numeric($pid) && !is_numeric($targetId))
        ) {
            return false;
        }

        /* security check */
        if (!\CB\Security::canRead($this->id)) {
            return false;
        }
        /* end of security check */

        if (is_numeric($targetId)) {
            /* target security check */
            if (!\CB\Security::canWrite($targetId)) {
                return false;
            }
            /* end of target security check */
            // marking overwriten object with dstatus = 3
            DB\dbQuery(
                'UPDATE tree SET updated = 1, dstatus = 3, did = $2 WHERE id = $1',
                array($targetId, $_SESSION['user']['id'])
            ) or die(DB\dbQueryError());

            //get pid from target if not specified
            $res = DB\dbQuery(
                'SELECT pid FROM tree WHERE id = $1',
                $targetId
            ) or die(DB\dbQueryError());

            if ($r = $res->fetch_assoc()) {
                $pid = $r['pid'];
            }
            $res->close();
        } else {
            /* pid security check */
            if (!\CB\Security::canWrite($pid)) {
                return false;
            }
            /* end of pid security check */
        }

        /* check again if we have pid set
            It can be unset when not existent $targetId is specified
        */
        if (!is_numeric($pid)) {
            return false;
        }

        // copying the object to $pid

        DB\dbQuery(
            'INSERT INTO `tree`
                (`id`,
                `old_id`,
                `pid`,
                `user_id`,
                `system`,
                `type`,
                `subtype`,
                `template_id`,
                `tag_id`,
                `target_id`,
                `name`,
                `date`,
                `date_end`,
                `size`,
                `is_main`,
                `cfg`,
                `inherit_acl`,
                `acl_count`,
                `cid`,
                `cdate`,
                `uid`,
                `udate`,
                `updated`,
                `oid`,
                `did`,
                `ddate`,
                `dstatus`)
            SELECT
                    NULL,
                    `old_id`,
                    $2,
                    `user_id`,
                    `system`,
                    `type`,
                    `subtype`,
                    `template_id`,
                    `tag_id`,
                    `target_id`,
                    `name`,
                    `date`,
                    `date_end`,
                    `size`,
                    `is_main`,
                    `cfg`,
                    `inherit_acl`,
                    0,
                    `cid`,
                    `cdate`,
                    $3,
                    CURRENT_TIMESTAMP,
                    1,
                    `oid`,
                    `did`,
                    `ddate`,
                    `dstatus`
            FROM `tree` t
            WHERE id = $1',
            array(
                $this->id
                ,$pid
                ,$_SESSION['user']['id']
            )
        ) or die(DB\dbQueryError());

        $objectId = DB\dbLastInsertId();

        /* we have now object created, so we star copy all its possible data:
            - tree_info is filled automaticly by trigger
            - custom security rules from tree_acl
            - custom object data
        */

        // copy node custom security rules if set
        DB\dbQuery(
            'INSERT INTO `tree_acl`
            (`node_id`,
             `user_group_id`,
             `allow`,
             `deny`,
             `cid`,
             `cdate`,
             `uid`,
             `udate`)
            SELECT
              $2,
              `user_group_id`,
              `allow`,
              `deny`,
              `cid`,
              `cdate`,
              `uid`,
              `udate`
            FROM `tree_acl`
            WHERE node_id = $1',
            array(
                $this->id
                ,$objectId
            )
        ) or die(DB\dbQueryError());

        $this->copyCustomDataTo($objectId);

        // move childs from overwriten targetId (which has been marked with dstatus = 3)
        // to newly copied object
        if (is_numeric($targetId)) {
            DB\dbQuery(
                'UPDATE tree SET updated = 1, pid = $2 WHERE pid = $1 AND dstatus = 0',
                array($targetId, $this->id)
            ) or die(DB\dbQueryError());
        }

        return $objectId;
    }

    private function copyCustomDataTo($targetId)
    {
        // - objects, objects_data and objects_duplicates

        // copy data from objects table
        DB\dbQuery(
            'INSERT INTO `objects`
            (`id`,
             `old_id`,
             `title`,
             `custom_title`,
             `date_start`,
             `date_end`,
             `author`,
             `iconCls`,
             `details`,
             `private_for_user`,
             `cid`,
             `cdate`,
             `uid`,
             `udate`)
            SELECT
              $2,
              `old_id`,
              `title`,
              `custom_title`,
              `date_start`,
              `date_end`,
              `author`,
              `iconCls`,
              `details`,
              `private_for_user`,
              `cid`,
              `cdate`,
              $3,
              CURRENT_TIMESTAMP
            FROM `objects`
            WHERE id = $1',
            array(
                $this->id
                ,$targetId
                ,$_SESSION['user']['id']
            )
        ) or die(DB\dbQueryError());

        /* copy data from objects_duplicates table
         We have to copy records from objects_duplicates firstly
         because duplicate ids will change */
        $oldDuplicates = array();
        $newDuplicateIds = array( '0' => 0);

        $res = DB\dbQuery(
            'SELECT
              `id`,
              `pid`,
              `object_id`,
              `field_id`
            FROM `objects_duplicates`
            WHERE object_id = $1 ORDER BY id',
            $this->id
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $oldDuplicates[$r['id']] = $r;
        }
        $res->close();
        foreach ($oldDuplicates as $duplicate_id => $values) {
            DB\dbQuery(
                'INSERT INTO `objects_duplicates`
                (`pid`,
                 `object_id`,
                 `field_id`)
                VALUES ($1, $2, $3)',
                array(
                    $values['pid']
                    ,$targetId
                    ,$values['field_id']
                )
            ) or die(DB\dbQueryError());
            $newDuplicateIds[$duplicate_id] = DB\dbLastInsertId();
        }

        // now update all old pids for duplicates to new generated ids
        foreach ($oldDuplicates as $duplicate_id => $values) {
            if ($values['pid'] == 0) {
                continue;
            }
            DB\dbQuery(
                'UPDATE objects_duplicates
                SET pid = $2
                WHERE id = $1',
                array(
                    $newDuplicateIds[$duplicate_id]
                    ,$newDuplicateIds[$values['pid']]
                )
            ) or die(DB\dbQueryError());
        }
        /* end of copy data from objects_duplicates table */

        // copy data from objects_data table.
        $objectData = array();
        $res = DB\dbQuery(
            'SELECT
              `field_id`,
              `duplicate_id`,
              `value`,
              `info`,
              `files`,
              `private_for_user`
            FROM `objects_data`
            WHERE object_id = $1',
            $this->id
        ) or die(DB\dbQueryError());
        while ($r = $res->fetch_assoc()) {
            $objectData[] = $r;
        }
        $res->close();

        foreach ($objectData as $r) {
            DB\dbQuery(
                'INSERT INTO `objects_data`
                (`object_id`,
                 `field_id`,
                 `duplicate_id`,
                 `value`,
                 `info`,
                 `files`,
                 `private_for_user`)
                VALUES($1, $2, $3, $4, $5, $6, $7)',
                array(
                    $targetId
                    ,$r['field_id']
                    ,'0'.@$newDuplicateIds[$r['duplicate_id']]
                    ,$r['value']
                    ,$r['info']
                    ,$r['files']
                    ,$r['private_for_user']
                )
            ) or die(DB\dbQueryError());
        }
    }

    /**
     * move an object to $pid or over $targetId
     *
     * we'll use the same principle as for copy
     *
     * @param  int $pid      if not specified then will be set to pid of targetId
     * @param  int $targetId
     * @return int the id of moved object or false
     */
    public function moveTo($pid = false, $targetId = false)
    {
        // check input params
        if (!is_numeric($this->id) ||
            (!is_numeric($pid) && !is_numeric($targetId))
        ) {
            return false;
        }

        /* security check */
        if (!\CB\Security::canDelete($this->id)) {
            return false;
        }
        /* end of security check */

        if (is_numeric($targetId)) {
            /* target security check */
            if (!\CB\Security::canWrite($targetId)) {
                return false;
            }
            /* end of target security check */
            // marking overwriten object with dstatus = 3
            DB\dbQuery(
                'UPDATE tree SET updated = 1, dstatus = 3, did = $2 WHERE id = $1',
                array($targetId, $_SESSION['user']['id'])
            ) or die(DB\dbQueryError());

            //get pid from target if not specified
            $res = DB\dbQuery(
                'SELECT pid FROM tree WHERE id = $1',
                $targetId
            ) or die(DB\dbQueryError());

            if ($r = $res->fetch_assoc()) {
                $pid = $r['pid'];
            }
            $res->close();
        } else {
            /* pid security check */
            if (!\CB\Security::canWrite($pid)) {
                return false;
            }
            /* end of pid security check */
        }

        /* check again if we have pid set
            It can be unset when not existent $targetId is specified
        */
        if (!is_numeric($pid)) {
            return false;
        }

        // moving the object to $pid

        DB\dbQuery(
            'UPDATE tree set updated = 1, pid = $2 where id = $1',
            array($this->id, $pid)
        ) or die(DB\dbQueryError());

        // move childs from overwriten targetId (which has been marked with dstatus = 3)
        // to newly copied object
        if (is_numeric($targetId)) {
            DB\dbQuery(
                'UPDATE tree SET updated = 1, pid = $2 WHERE pid = $1 AND dstatus = 0',
                array($targetId, $this->id)
            ) or die(DB\dbQueryError());
        }

        return $this->id;
    }
}
