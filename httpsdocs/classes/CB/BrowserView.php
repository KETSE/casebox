<?php
namespace CB;

class BrowserView extends Browser
{
    public function getChildren($p)
    {
        $p['showFoldersContent'] = true;
        if (@$p['from'] == 'calendar') {
            $p['fl'] = 'id,category_id,cid,date,date_end,status,template_id,name,cls';
        }

        $rez = parent::getChildren($p);

        return $rez;
    }

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
