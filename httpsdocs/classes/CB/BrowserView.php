<?php
namespace CB;

class BrowserView extends Browser
{
    public function getChildren($p)
    {
        $p['showFoldersContent'] = true;

        return parent::getChildren($p);
    }

    // /**
    //  * default method for displaying a node childs
    //  * @param  array $p params
    //  * @return array existing nodes
    //  */
    // private function getDefaultControllerResults($p)
    // {
    //     $pid = null;
    //     if (!empty($p['path'])) {
    //         $pid = Path::getId($p['path']);
    //     } elseif (!empty($p['pid'])) {
    //         $pid = is_numeric($p['pid']) ? $p['pid'] : Browser::getRootFolderId();
    //     }

    //     if (empty($p['descendants'])) {
    //         $p['pid'] = $pid;
    //     } else {
    //         $p['pids'] = $pid;
    //     }
    //     $s = new Search();
    //     $rez = $s->query($p);
    //     if (!empty($rez['data'])) {
    //         for ($i=0; $i < sizeof($rez['data']); $i++) {
    //             $d = &$rez['data'][$i];
    //             $d['nid'] = $d['id'];
    //             unset($d['id']);

    //             $res = DB\dbQuery(
    //                 'SELECT cfg
    //                   , (SELECT 1
    //                      FROM tree
    //                      WHERE pid = $1
    //                          AND dstatus = 0 LIMIT 1) has_childs
    //                 FROM tree
    //                 WHERE id = $1',
    //                 $d['nid']
    //             ) or die(DB\dbQueryError());

    //             if ($r = $res->fetch_assoc()) {
    //                 $d['cfg'] = Util\toJSONArray($r['cfg']);
    //                 $d['has_childs'] = !empty($r['has_childs']);
    //             }
    //             $res->close();
    //         }
    //     }

    //     return $rez;
    // }

    public function getSummaryData($p)
    {
        /* result columns order : id, name, type, iconCls, total, total2*/
        $rez = array(
            'success' => true
            ,'data' => array()
        );
        $path = '/';
        $default_filters = array(
            'activeTasks' => array(
                'sort' => 'status'
                ,'template_types' => 'task'
                ,'filters' => array(
                    'status' => array( array('mode' => 'OR', 'values' => array(1, 2) ) )
                    ,'user_ids' => array( array('mode' => 'OR', 'values' => array($_SESSION['user']['id']) ) )
                )
            )
            ,'completeTasks' => array(
                'sort' => 'status'
                ,'template_types' => 'task'
                ,'filters' => array(
                    'status' => array( array('mode' => 'OR', 'values' => array(1, 2) ) )
                    ,'user_ids' => array( array('mode' => 'OR', 'values' => array($_SESSION['user']['id']) ) )
                )
            )
            ,'actions' => array(
                'sort' => 'status'
                ,'template_types' => 'object'
                ,'folders' => false
                ,'filters' => array(
                    'status' => array( array('mode' => 'OR', 'values' => array(1, 2) ) )
                    ,'user_ids' => array( array('mode' => 'OR', 'values' => array($_SESSION['user']['id']) ) )
                )
            )
            ,'files' => array(
                'sort' => 'status'
                ,'template_types' => 'file'
                ,'filters' => array(
                    'status' => array( array('mode' => 'OR', 'values' => array(1, 2) ) )
                    ,'user_ids' => array( array('mode' => 'OR', 'values' => array($_SESSION['user']['id']) ) )
                )
            )
            ,'tasksUsers' => array(
                'sort' => 'status'
                ,'template_types' => 'task'
                ,'filters' => array(
                    'status' => array( array('mode' => 'OR', 'values' => array(1, 2) ) )
                    ,'user_ids' => array( array('mode' => 'OR', 'values' => array($_SESSION['user']['id']) ) )
                )
            )
        );
        $search = new Search();
        foreach ($p as $k => $v) {
            if (empty($default_filters[$k])) {
                continue;
            }
            $params = Util\coalesce(@$p[$k], $default_filters[$k]);
            if (!empty($v['path'])) {
                $path = $v['path'];
                if (empty($v['descendants'])) {
                    $params['pid'] = Path::getId($path);
                } else {
                    $params['pids'] = Path::getId($path);
                }
            }
            $sr = $search->query($params);
            $d = array();
            switch ($k) {
                case 'tasksUsers':
                    foreach ($sr['facets'] as $f) {
                        @$d[] = array(
                            $f->id
                            ,null
                            ,null
                            ,null
                            ,null
                            ,null
                            ,$f->total
                            ,$f->total2
                        );
                    }
                    break;
                default:
                    if (!empty($sr['data'])) {
                        foreach ($sr['data'] as $r) {
                            @$d[] = array(
                                $r['id']
                                ,$r['name']
                                ,$r['type']
                                ,$r['status']
                                ,$r['template_id']
                            );
                        }
                    }
            }
            $rez['data'][$k] = $d;
            $rez['params'][$k] = $search->params;
        }

        $rez['pathtext'] = Path::getPathText($path);
        $rez['folderProperties'] = Path::getPathProperties($path);

        return $rez;
    }
}
