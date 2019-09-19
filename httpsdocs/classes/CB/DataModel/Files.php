<?php
namespace CB\DataModel;

use CB\DB;
use CB\Util;
use CB\User;

class Files extends Base
{
    /**
     * database table name
     * @var string
     */
    protected static $tableName = 'files';

    protected static $tableFields = array(
        'id' => 'int'
        ,'content_id' => 'int'
        ,'date' => 'date'
        ,'name' => 'varchar'
        ,'title' => 'varchar'
        ,'cid' => 'int'
        ,'cdate' => 'datetime'
        ,'uid' => 'int'
        ,'udate' => 'datetime'
    );

    /**
     * get content data
     * @param  array $id
     * @return array
     */
    public static function getContentData($id)
    {
        $rez = array();

        $res = DB\dbQuery(
            'SELECT fc.*
            FROM files f
            LEFT JOIN files_content fc ON f.content_id = fc.id
            WHERE f.id = $1',
            $id
        );

        if ($r = $res->fetch_assoc()) {
            $rez = $r;
        }
        $res->close();

        return $rez;
    }

    /**
     * get tipes for given file ids
     * @param  array $ids
     * @return array associative array (id => type)
     */
    public static function getTypes($ids)
    {
        $rez = array();
        $ids = Util\toNumericArray($ids);

        if (!empty($ids)) {
            $res = DB\dbQuery(
                'SELECT f.id, c.`type`
                FROM files f
                JOIN files_content c
                    ON f.content_id = c.id
                WHERE f.id in (' . implode(',', $ids) . ')'
            );

            while ($r = $res->fetch_assoc()) {
                $rez[$r['id']] = $r['type'];
            }
            $res->close();
        }

        return $rez;
    }

    /**
     * get content ids for given file ids
     * @param  array $ids
     * @return array associative array (id => content_id)
     */
    public static function getContentIds($ids)
    {
        $rez = array();
        $ids = Util\toNumericArray($ids);

        if (!empty($ids)) {
            $sql = 'SELECT id, content_id
                FROM files
                WHERE id in (' . implode(',', $ids) .')';

            $res = DB\dbQuery($sql);
            while ($r = $res->fetch_assoc()) {
                $rez[$r['id']] = $r['content_id'];
            }
            $res->close();
        }

        return $rez;
    }

    /**
     * get relative content paths for given file ids
     * path is relative to casebox files directory
     * @param  array $ids
     * @return array associative array (id => relative_content_path)
     */
    public static function getContentPaths($ids)
    {
        $rez = array();
        $ids = Util\toNumericArray($ids);

        if (!empty($ids)) {
            $sql = 'SELECT f.id, c.`path`, f.content_id
                FROM files f
                JOIN files_content c
                    ON f.content_id = c.id
                WHERE f.id in (' . implode(',', $ids) .')';

            $res = DB\dbQuery($sql);
            while ($r = $res->fetch_assoc()) {
                $rez[$r['id']] = $r['path'] . DIRECTORY_SEPARATOR . $r['content_id'];
            }
            $res->close();
        }

        return $rez;
    }

    /**
     * get file ids that reffer to a given contentId
     * @return array
     */
    public static function getContentIdReferences($contentId)
    {
        $rez = array();

        $sql = 'SELECT id
            FROM files
            WHERE content_id = $1
            ORDER BY id';

        $res = DB\dbQuery($sql, $contentId);
        while ($r = $res->fetch_assoc()) {
            $rez[] = $r['id'];
        }
        $res->close();

        return $rez;
    }

    /**
     * get duplicates files (with same content_id) for a given file id
     * @param  int   $id
     * @return array
     */
    public static function getDuplicates($id)
    {
        $rez = array();

        if (!is_numeric($id)) {
            return $rez;
        }

        $res = DB\dbQuery(
            'SELECT
                 fd.id
                ,fd.cid
                ,fd.cdate
                ,case when(fd.name = f.name) THEN "" ELSE fd.name END `name`
                ,ti.pids `path`
                ,ti.path `pathtext`
            FROM files f
            JOIN files fd
                ON f.content_id = fd.content_id
                AND fd.id <> $1
            JOIN tree t
                ON fd.id = t.id
                and t.dstatus = 0
            JOIN tree_info ti
                ON t.id = ti.id
            WHERE f.id = $1',
            $id
        );

        while ($r = $res->fetch_assoc()) {
            $r['path'] = str_replace(',', '/', $r['path']);
            $rez[] = $r;
        }
        $res->close();

        return $rez;
    }

    /**
     * get file solr data
     * @param  int   $id
     * @return array
     */
    public static function getSolrData($id)
    {
        $rez = array();

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
            $id
        );

        if ($r = $res->fetch_assoc()) {
            $rez = $r;
        }

        $res->close();

        return $rez;
    }

    /**
     * copy file data, but without versions. Should we copy versions also?
     * @param  int  $sourceId
     * @param  int  $targetId
     * @return void
     */
    public static function copy($sourceId, $targetId)
    {
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
                $sourceId
                ,$targetId
                ,User::getId()
            )
        );
    }
}
