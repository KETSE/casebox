<?php
namespace CB\TreeNode;

use CB\DB;
use CB\DataModel as DM;
use CB\Browser;
use CB\Objects;
use CB\Search;
use CB\Cache;

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

            if (($lastNode instanceof Dbnode) || (get_class($lastNode) == 'CB\\TreeNode\\Base')) {
                $pid = $lastNode->id;
            } else {
                //we are under another node type
                $cfg = $lastNode->getConfig();
                if (!empty($cfg['realNodeId']) && ($lastNode instanceof RealSubnode)) {
                    $pid = $cfg['realNodeId'];
                } else {
                    return array();
                }
            }
        }

        if (empty($pid)) {
            return array();
        }
        /* end of check */

        $p = &$requestParams;

        $folderTemplates = \CB\Config::get('folder_templates');

        $p['fl'] = 'id,pid,system,path,name,case_id,date,date_end,size,cid,'.
            'oid,cdate,uid,udate,template_id,acl_count,cls,status,task_status,versions,'.
            'comment_user_id,comment_date';

        if (empty($p['showFoldersContent'])) {
            $p['templates'] = $folderTemplates;
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

                $r = DM\Tree::read($d['id']);
                if (!empty($r['cfg']) && ($p['from'] == 'tree')) {
                    if (isset($r['cfg']['loaded'])) {
                        $d['loaded'] = $r['cfg']['loaded'];
                    }
                    if (isset($r['cfg']['expanded'])) {
                        $d['expanded'] = $r['cfg']['expanded'];
                    }
                    if (isset($r['cfg']['leaf'])) {
                        $d['leaf'] = $r['cfg']['leaf'];
                    }
                }
            }
            \CB\Tasks::setTasksActionFlags($rez['data']);
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
        if ($id == false) {
            $id = $this->id;
        }

        $rez = Objects::getName($id, true);

        return $rez;
    }

    public function getData()
    {
        return DM\Tree::getBasicInfo($this->id);
    }

    /**
     * get create menu for current node
     * @param  array   $rp request params
     * @return varchar menu config string
     */
    public function getCreateMenu(&$rp)
    {
        return Browser\CreateMenu::getMenuForPath($this->id);
    }

    /**
     * get param for this node
     *
     * @param  varchar $param for now using to get 'facets' or 'DC'
     * @return array
     */
    public function getNodeParam($param = 'facets')
    {
        $rez = false;

        $from = $this->getId();

        //check if cached
        $cacheParam = 'nodeParam_' . $param . '_' . $from;
        if (Cache::exist($cacheParam)) {
            return Cache::get($cacheParam);
        }

        $cfg = array();

        $templateId = null;

        $tplCfg = array();

        if (!empty($this->id) && is_numeric($this->id)) {
            $r = DM\Tree::read($this->id);
        }

        if (!empty($r)) {
            $cfg = $r['cfg'];
            $templateId = $r['template_id'];
        }

        if (!empty($this->config['template_id'])) {
            $templateId = $this->config['template_id'];
        }

        if (!empty($templateId)) {
            $r = DM\Templates::read($templateId);
            if (!empty($r)) {
                $tplCfg = $r['cfg'];
            }
        }

        if (isset($cfg[$param])) {
            $rez = $cfg[$param];

        } elseif (isset($tplCfg[$param])) {
            $cfg = $tplCfg;
            $rez = $cfg[$param];
            $from = 'template_' . $templateId;
        }

        //add grouping param for DC
        if (($param == 'DC') && ($rez !== false)) {
            if (!empty($cfg['view']['group'])) {
                $rez['group'] = $cfg['view']['group'];

            } elseif (!empty($cfg['group'])) {
                $rez['group'] = $cfg['group'];
            }
        }

        if ($rez === false) {
            $rez = parent::getNodeParam($param);

        } else {
            $rez = array(
                'from' => $from
                ,'data' => $rez
            );
        }

        Cache::set($cacheParam, $rez);

        return $rez;
    }
}
