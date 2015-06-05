<?php

namespace CB\DataModel;

use CB\DB;
use CB\Util;

class Files extends Base
{
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

            $res = DB\dbQuery($sql) or die(DB\dbQueryError());
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

            $res = DB\dbQuery($sql) or die(DB\dbQueryError());
            while ($r = $res->fetch_assoc()) {
                $rez[$r['id']] = $r['path'] . DIRECTORY_SEPARATOR . $r['content_id'];
            }
            $res->close();
        }

        return $rez;
    }
}
