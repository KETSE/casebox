<?php
namespace CB\TreeNode;

use CB\DB;
use CB\Util;
use CB\L;

class RecycleBin extends Base
{

    protected function acceptedPath()
    {
        $p = &$this->path;

        // Tasks can't be a root folder
        if (sizeof($p) == 0) {
            return false;
        }

        //get the configured 'pid' property for this tree plugin
        //default is 0
        //thats the parent node id where this class shold start to give result nodes
        $ourPid = @$this->config['pid'];

        // ROOT NODE: check if last node is the one we should attach to
        if ($this->lastNode->id == (String)$ourPid) {
            return true;
        }

        // CHILDREN NODES: accept if last node is an instance of this class
        if (get_class($this->lastNode) == get_class($this)) {
            return true;
        }

        return false;
    }

    protected function createDefaultFilter()
    {
        $this->fq = array('did:'.$_SESSION['user']['id']);
    }

    public function getChildren(&$pathArray, $requestParams)
    {

        $this->path = $pathArray;
        $this->lastNode = @$pathArray[sizeof($pathArray) - 1];
        $this->requestParams = $requestParams;
        $this->rootId = \CB\Browser::getRootFolderId();

        if (!$this->acceptedPath()) {
            return;
        }

        $ourPid = @intval($this->config['pid']);

        $this->createDefaultFilter();

        if (empty($this->lastNode) ||
            (($this->lastNode->id == $ourPid) && (get_class($this->lastNode) != get_class($this)))
        ) {
            $rez = $this->getRootNodes();
        } else {
            $rez = $this->getContentItems();
        }

        return $rez;

    }

    public function getName($id = false)
    {
        if ($id === false) {
            $id = $this->id;
        }

        $rez = $id;

        switch ($id) {
            case 'recycleBin':
                return L\get('RecycleBin');
            default:
                if (!empty($id) && is_numeric($id)) {
                    $rez = \CB\Objects::getName($id);
                }
                break;
        }

        return $rez;
    }

    protected function getRootNodes()
    {
        return array(
            'data' => array(
                array(
                    'name' => $this->getName('recycleBin')
                    ,'id' => $this->getId('recycleBin')
                    ,'iconCls' => 'icon-trash'
                    ,'has_childs' => true
                )
            )
        );
    }

    public function getContentItems()
    {
        $p = &$this->requestParams;

        $folderTemplates = \CB\Config::get('folder_templates');

        $p['fl'] = 'id,system,path,name,case,date,date_end,size,cid,oid,cdate,uid,udate,template_id,acl_count,cls,status,task_status,dstatus';

        if (@$p['from'] == 'tree') {
            $p['templates'] = $folderTemplates;
        }

        if (is_numeric($this->lastNode->id)) {
            $p['pid'] = $pid;
        }

        $p['dstatus'] = 1;

        $p['fq'] = $this->fq;

        $s = new \CB\Search();
        $rez = $s->query($p);

        if (!empty($rez['data'])) {
            for ($i=0; $i < sizeof($rez['data']); $i++) {
                $d = &$rez['data'][$i];

                $res = DB\dbQuery(
                    'SELECT cfg
                      , (SELECT 1
                         FROM tree
                         WHERE pid = $1'.
                    ((@$p['from'] == 'tree')
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
        }

        return $rez;
    }
}
