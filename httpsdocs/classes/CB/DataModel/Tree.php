<?php

namespace CB\DataModel;

use CB\DB;
use CB\Util;
use CB\User;

class Tree extends Base
{
    /**
     * database table name
     * @var string
     */
    protected static $tableName = 'tree';

    /**
     * available table fields
     *
     * associative array of fieldName => type
     * that is also used for trivial validation of input values
     *
     * @var array
     */
    protected static $tableFields = array(
        'id' => 'int'
        ,'pid' => 'int'
        ,'user_id' => 'int'
        ,'system' => 'int'
        // ,'type' => 'int'  // obsolete
        ,'draft' => 'int'
        ,'draft_pid' => 'varchar'
        ,'template_id' => 'int'
        // ,'tag_id' => 'int' // obsolete
        ,'name' => 'varchar'
        ,'target_id' => 'int'
        ,'name' => 'varchar'
        ,'date' => 'datetime'
        ,'date_end' => 'datetime'
        ,'size' => 'int' //..
        ,'is_main' => 'int' //..
        ,'cfg' => 'text'
        ,'inherit_acl' => 'int'
        ,'cid' => 'int'
        ,'cdate' => 'datetime'
        ,'uid' => 'int'
        ,'udate' => 'datetime'
        ,'updated' => 'int'
        ,'oid' => 'int'
        ,'did' => 'int'
        ,'ddate' => 'datetime'
        ,'dstatus' => 'int'
    );

    protected static $decodeJsonFields = array('cfg');

    /**
     * delete a record by its id
     * @param  int | array $ids
     * @param  boolean     $persistent
     * @return void
     */
    public static function delete($ids, $persistent = false)
    {
        $ids = Util\toNumericArray($ids);
        $userId = User::getId();

        foreach ($ids as $id) {
            if ($persistent) {
                DB\dbQuery(
                    'CALL p_delete_tree_node($1)',
                    $id
                );

            } else {
                static::update(
                    array(
                        'id' => $id
                        ,'dstatus' => 1
                        ,'did' => $userId
                        ,'ddate' => 'CURRENT_TIMESTAMP'
                        ,'updated' => 1
                    )
                );

                DB\dbQuery(
                    'CALL p_mark_all_childs_as_deleted($1, $2)',
                    array(
                        $id
                        ,$userId
                    )
                );
            }
        }
    }

    /**
     * mark records as active (not deleted)
     * @param  int | array $ids
     * @return void
     */
    public static function restore($ids)
    {
        $ids = Util\toNumericArray($ids);
        // $userId = User::getId();

        foreach ($ids as $id) {
            static::update(
                array(
                    'id' => $id
                    ,'did' => null
                    ,'dstatus' => 0
                    ,'ddate' => null
                    ,'updated' => 1

                )
            );

            DB\dbQuery('CALL p_mark_all_childs_as_active($1)', $id);
        }

    }

    /**
     * get base properties for a givent record id
     * @param  int   $id
     * @return array
     */
    public static function getProperties($id)
    {
        $rez = array();

        $res = DB\dbQuery(
            'SELECT t.id `nid`
                ,t.`system`
                ,t.`type`
                ,t.`name`
                ,t.`cfg`
                ,ti.acl_count
            FROM tree t
            JOIN tree_info ti on t.id = ti.id
            WHERE t.id = $1',
            $id
        );

        if ($r = $res->fetch_assoc()) {
            $rez = $r;
            static::decodeJsonFields($r);
        }
        $res->close();

        return $rez;
    }

    /**
     * Get basic info
     * should be reviewed
     * @param  int   $id
     * @return array
     */
    public static function getBasicInfo($id)
    {
        $rez = array();
        $res = DB\dbQuery(
            'SELECT t.id
                ,t.name
                ,t.`system`
                ,t.`type`
                ,ti.pids
                ,ti.case_id
                ,t.`template_id`
                ,t.dstatus
                ,tt.`type` template_type
            FROM tree t
            JOIN tree_info ti on t.id = ti.id
            LEFT JOIN templates tt ON t.template_id = tt.id
            WHERE t.id = $1',
            $id
        );

        if ($r = $res->fetch_assoc()) {
            $rez = $r;
        }
        $res->close();

        return $rez;
    }

    /**
     * Get case id for a given item
     * backward compatibility function
     * should be reviewed
     * @param  int $id
     * @return int
     */
    public static function getCaseId($id)
    {
        $rez = null;

        $res = DB\dbQuery(
            'SELECT coalesce(ti.case_id, t.pid) `pid`
            FROM tree t
            JOIN tree_info ti ON t.id = ti.id
            WHERE t.id = $1',
            $id
        );

        if ($r = $res->fetch_assoc()) {
            $rez = $r['pid'];
        }
        $res->close();

        return $rez;
    }

    /**
     * Update owner for ginev ids
     * @param  int  $ids
     * @param  int  $ownerId
     * @return void
     */
    public static function updateOwner($ids, $ownerId)
    {
        $ids = Util\toNumericArray($ids);

        if (!empty($ids)) {
            DB\dbQuery(
                'UPDATE tree
                SET oid = $1
                    ,uid = $2
                    ,updated = 1
                WHERE id IN (' . implode(',', $ids) . ')
                    AND `system` = 0',
                array(
                    $ownerId
                    ,User::getId()
                )
            );
        }
    }

    public static function getRootId()
    {
        $rez = null;

        $res = DB\dbQuery(
            'SELECT id
            FROM tree
            WHERE pid IS NULL
                AND `system` = 1
                AND `is_main` = 1'
        );

        if ($r = $res->fetch_assoc()) {
            $rez = $r['id'];
        }
        $res->close();

        return $rez;
    }

    /**
     * get child record by name
     * @param  int     $pid
     * @param  varchar $name
     * @return array
     */
    public static function getChildByName($pid, $name)
    {
        $rez = null;
        $res = DB\dbQuery(
            'SELECT *
            FROM tree
            WHERE pid = $1
                AND name = $2
                AND dstatus = 0',
            array($pid, $name)
        );

        if ($r = $res->fetch_assoc()) {
            $rez = $r;
        }
        $res->close();

        return $rez;
    }

    /**
     * get child names under given pid that start with $name and have same extension as $ext
     * @param  int     $pid
     * @param  varchar $name
     * @param  varchar $ext
     * @return array
     */
    public static function getChildNames($pid, $name, $ext)
    {
        $rez = array();
        $res = DB\dbQuery(
            'SELECT name
            FROM tree
            WHERE pid = $1
                AND name like $2
                AND dstatus = 0',
            array(
                $pid
                ,$name . '%' . '.'.$ext
            )
        );

        while ($r = $res->fetch_assoc()) {
            $rez[] = $r['name'];
        }
        $res->close();

        return $rez;
    }

    /**
     * get children count for given item ids
     * @param  array $ids
     * @param  array $templateIds filter children by template ids
     * @return array associative array of children per id
     */
    public static function getChildCount($ids, $templateIds = false)
    {
        $rez = array();

        $ids = Util\toNumericArray($ids);

        if (empty($ids)) {
            return $rez;
        }

        if (empty($templateIds)) {
            $templateIds = '';
        } else {
            $templateIds = Util\toNumericArray($templateIds);
            if (!empty($templateIds)) {
                $templateIds = ' AND template_id in (' . implode(',', $templateIds) . ')';
            }
        }

        $sql = 'SELECT pid, count(*) `children`
            FROM tree
            WHERE pid in (' . implode(',', $ids) . ')
                AND dstatus = 0' . $templateIds . '
            GROUP BY pid';

        $res = DB\dbQuery($sql);

        while ($r = $res->fetch_assoc()) {
            $rez[$r['pid']] = $r['children'];
        }
        $res->close();

        return $rez;
    }

    /**
     * activate child drafts
     * @param  array $id
     * @return void
     */
    public static function activateChildDrafts($id)
    {
        DB\dbQuery(
            'call `p_mark_all_child_drafts_as_active`($1)',
            $id
        );
    }

    /**
     * assign draft children to real id
     * @param  varchar $draftId
     * @param  array   $id
     * @return void
     */
    public static function assignChildDrafts($draftId, $targetId)
    {
        $res = DB\dbQuery(
            'SELECT id
            FROM tree
            WHERE draft = 1
                AND draft_pid = $1
                AND cid = $2
                AND (cdate > (CURRENT_TIMESTAMP - INTERVAL 1 HOUR))',
            array(
                $draftId
                ,User::getId()
            )
        );

        while ($r = $res->fetch_assoc()) {
            $children[] = $r['id'];
        }
        $res->close();

        if (!empty($children)) {
            DB\dbQuery(
                'UPDATE tree
                SET draft = 0
                    ,draft_pid = null
                    ,pid = $1
                    ,updated = 1
                WHERE id in (' . implode(',', $children) . ')',
                $targetId
            );
        }
    }

    /**
     * copy a source record under given $pid
     * @param  array $sourceId
     * @param  array $pid
     * @return int   created record id
     */
    public static function copy($sourceId, $pid)
    {
        DB\dbQuery(
            'INSERT INTO `tree`
                (`id`
                ,`pid`
                ,`user_id`
                ,`system`
                ,`type`
                ,`template_id`
                ,`tag_id`
                ,`target_id`
                ,`name`
                ,`date`
                ,`date_end`
                ,`size`
                ,`is_main`
                ,`cfg`
                ,`inherit_acl`
                ,`cid`
                ,`cdate`
                ,`uid`
                ,`udate`
                ,`updated`
                ,`oid`
                ,`did`
                ,`ddate`
                ,`dstatus`)
            SELECT
                NULL
                ,$2
                ,`user_id`
                ,`system`
                ,`type`
                ,`template_id`
                ,`tag_id`
                ,`target_id`
                ,`name`
                ,`date`
                ,`date_end`
                ,`size`
                ,`is_main`
                ,`cfg`
                ,`inherit_acl`
                ,`cid`
                ,`cdate`
                ,$3
                ,CURRENT_TIMESTAMP
                ,1
                ,`oid`
                ,`did`
                ,`ddate`
                ,`dstatus`
            FROM `tree` t
            WHERE id = $1',
            array(
                $sourceId
                ,$pid
                ,User::getId()
            )
        );

        return DB\dbLastInsertId();
    }

    /**
     * move active (non deleted) children to other parent
     * @param  array $sourceId
     * @param  array $targetId
     * @return bool
     */
    public static function moveActiveChildren($sourceId, $targetId)
    {
        DB\dbQuery(
            'UPDATE tree
            SET updated = 1
                ,pid = $2
            WHERE pid = $1 AND
                dstatus = 0',
            array(
                $sourceId
                ,$targetId
            )
        );

        return (DB\dbAffectedRows() > 0);
    }
}
