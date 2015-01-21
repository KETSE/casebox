<?php
namespace CB\TreeNode;

use CB\DB;
use CB\Util;
use CB\Browser;
use CB\Search;

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

        $p['fl'] = 'id,pid,system,path,name,case,date,date_end,size,cid,oid,cdate,uid,udate,template_id,acl_count,cls,status,task_status,versions';

        if (empty($p['showFoldersContent'])) {
            $p['templates'] = $folderTemplates;
        }

        if (empty($p['descendants'])) {
            $p['pid'] = $pid;
        } else {
            $p['pids'] = $pid;
        }

        if (empty($p['userViewChange'])) {

            if (!empty($this->config['view'])) {
                $p['from'] = $this->config['view'];
            } elseif (empty($p['from']) || ($p['from'] !== 'tree')) {
                $p['from'] = 'grid';
            }
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
                        ? ' AND `template_id` IN (0'.implode(',', $folderTemplates).')'
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
            \CB\Tasks::setTasksActionFlags($rez['data']);
        }

        //set view if set in config
        if (!empty($this->config['view'])) {
            $rez['view'] = $this->config['view'];
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
        $rez = '';

        if ($id === false) {
            $id = $this->id;
        }
        if (!empty($id) && is_numeric($id)) {
            $rez = @Search::getObjectNames($id)[$id];
        }

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
     * @return varchar menu config string
     */
    public function getCreateMenu()
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

        $sql = 'SELECT t.cfg, t.template_id, tt.cfg `templateCfg`
            FROM tree t
            LEFT JOIN templates tt
                ON (t.template_id = tt.id)
                    OR ((tt.id = $2) AND (t.template_id IS NULL))
            WHERE t.id = $1';

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
        }
        $res->close();

        if ($rez === false) {
            return parent::getNodeParam($param);
        }

        return array(
            'from' => $from
            ,'data' => $rez
        );
    }
}
