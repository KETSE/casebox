<?php
namespace CB;

class BrowserTree extends Browser
{
    public function getChildren($p)
    {
        $rez = parent::getChildren($p);
        foreach ($rez['data'] as &$node) {
            $node['loaded'] = empty($node['has_childs']);
        }

        return $rez['data'];
    }

    // private function getRootChildren()
    // {
    //     $data = array();

    //     $res = DB\dbQuery(
    //         'SELECT t.id `nid`
    //             , t.`system`
    //             , t.`type`
    //             , t.`subtype`
    //             , t.`name`
    //             , ti.acl_count
    //         FROM tree t
    //             JOIN tree_info ON t.id = ti.id
    //         WHERE ((t.user_id = $1)
    //                OR (t.user_id IS NULL))
    //                 AND (t.`system` = 1)
    //                 AND (t.pid IS NULL)
    //         ORDER BY user_id DESC, is_main',
    //         $_SESSION['user']['id']
    //     ) or die(DB\dbQueryError());

    //     while ($r = $res->fetch_assoc()) {
    //         $r['expanded'] = true;
    //         if (!empty($data)) {
    //             $r['cls'] = 'cb-group-padding';
    //         }
    //         $data[] = $r;
    //     }
    //     $res->close();

    //     return $data;
    // }

    // private function getDefaultControllerResults($path)
    // {
    //     $path = explode('/', $path);
    //     $a = array_filter($path, 'is_numeric');
    //     if (empty($a)) {
    //         return array();
    //     }
    //     $id = array_pop($path);

    //     $p = array(
    //         'pid' => $id
    //         ,'fl' => 'id,system,type,subtype,name,date,size,cid,cdate,uid,udate,template_id,acl_count'
    //     );

    //     if (!$this->showFoldersContent) {
    //         $p['templates'] = $GLOBALS['folder_templates'];
    //     }

    //     $s = new Search();
    //     $rez = $s->query($p);
    //     $rez = $rez['data'];

    //     return $rez;
    // }
}
