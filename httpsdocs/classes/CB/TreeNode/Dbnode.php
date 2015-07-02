<?php
namespace CB\TreeNode;

use CB\DB;
use CB\Util;
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

                $res = DB\dbQuery(
                    'SELECT cfg
                    FROM tree
                    WHERE id = $1',
                    $d['id']
                ) or die(DB\dbQueryError());

                if ($r = $res->fetch_assoc()) {
                    $d['cfg'] = Util\toJSONArray($r['cfg']);

                    if (!empty($d['cfg']) && ($p['from'] == 'tree')) {
                        if (isset($d['cfg']['loaded'])) {
                            $d['loaded'] = $d['cfg']['loaded'];
                        }
                        if (isset($d['cfg']['expanded'])) {
                            $d['expanded'] = $d['cfg']['expanded'];
                        }
                        if (isset($d['cfg']['leaf'])) {
                            $d['leaf'] = $d['cfg']['leaf'];
                        }
                    }
                }
                $res->close();
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

        $rez = Objects::getName($id);

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
                ,t.`dstatus`
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

        //select configs from tree and template of the current node
        $sql = 'SELECT t.cfg, t.template_id, tt.cfg `templateCfg`
            FROM tree t
            LEFT JOIN templates tt
                ON (t.template_id = tt.id)
                    OR ((tt.id = $2) AND (t.template_id IS NULL))
            WHERE t.id = $1';

        //if template_id specified in config then select directly from templates table
        if (empty($from) && !empty($this->config['template_id'])) {
            $from = 'template_' . $this->config['template_id'];

            $sql = 'SELECT null `cfg`, t.id template_id, t.cfg `templateCfg`
                FROM templates t
                WHERE t.id = $2';
        }

        //check node configuration and/or its template for facets definitions
        $res = DB\dbQuery(
            $sql,
            array(
                $this->id
                ,@$this->config['template_id']
            )
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $cfg = Util\toJSONArray($r['cfg']);

            if (isset($cfg[$param])) {
                $rez = $cfg[$param];
            } else {
                $cfg = Util\toJSONArray($r['templateCfg']);
                if (isset($cfg[$param])) {
                    $rez = $cfg[$param];
                    $from = 'template_'.$r['template_id'];
                }
            }

            //add grouping param for DC
            if (($param == 'DC') && ($rez !== false)) {
                if (!empty($cfg['view']['group'])) {
                    $rez['group'] = $cfg['view']['group'];

                } elseif (!empty($cfg['group'])) {
                    $rez['group'] = $cfg['group'];
                }
            }
        }
        $res->close();

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
