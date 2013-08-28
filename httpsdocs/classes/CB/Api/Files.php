<?php
namespace CB\Api;

use CB\DB as DB;

class Files
{
    /**
     * get file properties and content
     * @param  int  $id file id
     * @return json array with a subarray "data" that list all file properties and existent versions
     */
    public function get($id)
    {
        echo "File id: ".$id;
        $rez = array('success' => true, 'data' => array());
        $sql = 'SELECT t.id
                ,t.pid
                ,f.name
                ,f.`date`
                ,f.title
                ,t.template_id
                ,t.cid
                ,t.uid
                ,t.did
                ,t.cdate
                ,t.udate
                ,t.ddate
                ,t.dstatus
                ,ti.pids
                ,ti.path
                ,ti.case_id
                ,ti.acl_count
                ,f.content_id
                ,fc.size
                ,fc.pages
                ,fc.type
                ,fc.path `content_path`
            FROM tree t
            JOIN tree_info ti on t.id = ti.id
            JOIN files f ON t.id = f.id
            LEFT JOIN files_content fc ON f.content_id = fc.id
            WHERE t.id = $1';
        $res = DB\dbQuery($sql, $id) or die(DB\dbQueryError());
        if ($r = $res->fetch_assoc()) {
            $r['content'] = file_get_contents(\CB\FILES_PATH.$r['content_path'].DIRECTORY_SEPARATOR.$r['content_id']);
            unset($r['content_id']);
            unset($r['content_path']);
            $rez['data'] = $r;
        }
        $res->close();

        /* get versions */

        $sql = 'SELECT
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
            ORDER BY v.cdate DESC';
        $res = DB\dbQuery($sql, $id) or die(DB\dbQueryError());
        while ($r = $res->fetch_assoc()) {
            $rez['data']['versions'][] = $r;
        }
        $res->close();
        /* end of get versions */

        \CB\VerticalEditGrid::getData('objects', $rez['data']);

        return $rez;
    }
}
