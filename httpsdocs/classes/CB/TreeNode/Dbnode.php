<?php
namespace CB\TreeNode;

use CB\DB;
use CB\Util;

class Dbnode extends Base
{
    public function getChildren(&$pathArray, $requestParams)
    {
        $pid = null;
        /* should start with path check and see if child request is for a real db node*/
        if (empty($pathArray)) {
            if (empty($requestParams['query'])) {
                return;
            }
        } else {
            $lastNode = @$pathArray[sizeof($pathArray)-1];
            if ($lastNode instanceof Dbnode) {
                $pid = $lastNode->id;
            } else {
                //we are under another node type
                $cfg = $lastNode->getConfig();
                if (!empty($cfg['realNodeId'])) {
                    $pid = $cfg['realNodeId'];
                } else {
                    return array();
                }
            }
        }
        /* end of check */

        $p = &$requestParams;

        $p['fl'] = 'id,system,path,name,case,date,date_end,size,cid,oid,cdate,uid,udate,template_id,acl_count,cls,status';

        if (empty($p['showFoldersContent'])) {
            $p['templates'] = $GLOBALS['folder_templates'];
        }

        if (empty($p['descendants'])) {
            $p['pid'] = $pid;
        } else {
            $p['pids'] = $pid;
        }

        $s = new \CB\Search();
        $rez = $s->query($p);
        if (!empty($rez['data'])) {
            for ($i=0; $i < sizeof($rez['data']); $i++) {
                $d = &$rez['data'][$i];

                $res = DB\dbQuery(
                    'SELECT cfg
                      , (SELECT 1
                         FROM tree
                         WHERE pid = $1
                             AND dstatus = 0'.
                    ( empty($p['showFoldersContent'])
                        ? ' AND `template_id` IN (0'.implode(',', $GLOBALS['folder_templates']).')'
                        : ''
                    )
                    .' LIMIT 1) has_childs
                    FROM tree
                    WHERE id = $1',
                    $d['id']
                ) or die(DB\dbQueryError());

                if ($r = $res->fetch_assoc()) {
                    $d['cfg'] = Util\toJSONArray($r['cfg']);
                    $d['has_childs'] = !empty($r['has_childs']);
                }
                $res->close();
            }
        }

        return $rez;
    }

    public function getId($id = null)
    {
        if (is_null($id)) {
            $id = $this->id;
        }

        return $id;
    }

    public function getName($id = false)
    {
        $rez = 'no name';
        if ($id === false) {
            $id = $this->id;
        }
        $res = DB\dbQuery(
            'SELECT name FROM tree WHERE id = $1',
            $id
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $rez = $r['name'];
        }
        $res->close();

        return $rez;
    }

    public function getData()
    {
        $rez = array();
        $res = DB\dbQuery(
            'SELECT t.id
                ,t.name
                ,t.`system`
                ,ti.`case_id`
                ,t.`template_id`
                ,tt.`type` template_type
            FROM tree t
            JOIN tree_info ti on t.id = ti.id
            LEFT JOIN templates tt ON t.template_id = tt.id
            WHERE t.id = $1',
            $this->id
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $rez = $r;
        }
        $res->close();

        return $rez;
    }
}
