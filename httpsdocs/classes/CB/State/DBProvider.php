<?php

namespace CB\State;

use CB\Config;
use CB\DB;
use CB\Path;
use CB\Util;
use CB\User;

/**
 * class for saving/reading interface state for the current user
 */
class DBProvider
{

    /**
     * read current user state
     * @return Ext.Direct responce
     */
    public function read()
    {
        return array(
            'success' => true
            ,'data' => User::getUserConfigParam('state', array())
        );

    }

    /**
     * set state
     * @param array $p
     */
    public function set($p)
    {

        if (User::isLoged()) {
            $rez = array('success' => true);

            $state = User::getUserConfigParam('state', array());

            if (!empty($p['value']) || isset($state[$p['name']])) {
                if (empty($p['value'])) {
                    unset($state[$p['name']]);
                } else {
                    $state[$p['name']] = $p['value'];
                }

                User::setUserConfigParam('state', $state);
            }
        } else {
            $rez = array('success' => false);
        }

        return $rez;
    }

    /**
     * save state for grid view of the browser
     * @return Ext.Direct responce
     */
    public function saveGridViewState($p)
    {
        $rez = array('success' => true);
        $guid = false;
        /* incomming params example
        p: {params:{id:251, view:grid, path:1/114/101/251, query:null, start:0},…}
            params: {id:251, view:grid, path:1/114/101/251, query:null, start:0}
                id: 251
                path: "1/114/101/251"
                query: null
                start: 0
                view: "grid"
            state: {columns:{nid:{id:0, width:80, hidden:true, sortable:true}, name:{id:1, width:160, sortable:true},…}}
                columns: {nid:{id:0, width:80, hidden:true, sortable:true}, name:{id:1, width:160, sortable:true},…}
                case: {id:3, width:150, sortable:true}
                cdate: {id:8, width:120, hidden:true, sortable:true}
                cid: {id:6, width:200, hidden:true, sortable:true}
                date: {id:4, width:120, sortable:true}
                name: {id:1, width:160, sortable:true}
                nid: {id:0, width:80, hidden:true, sortable:true}
                oid: {id:7, width:200, sortable:true}
                path: {id:2, width:150, hidden:true, sortable:true}
                size: {id:5, width:80, sortable:true}
                udate: {id:9, width:120, hidden:true, sortable:true}
         */

        if (!empty($p['params']['search']['template_id'])) {
            $guid = 'template_' . $p['params']['search']['template_id'];

        } elseif (!empty($p['params']['query'])) {
            $guid = 'search';

        } else {
            $path = empty($p['params']['path'])
                ? $p['params']['id']
                : $p['params']['path'];

            if (!empty($path)) {
                $treeNodeConfigs = Config::get('treeNodes', array('Dbnode' => array()));

                $treeNodeClasses = Path::getNodeClasses($treeNodeConfigs);
                $treeNodeGUIDConfigs = array();
                foreach ($treeNodeClasses as $nodeClass) {
                    $cfg = $nodeClass->getConfig();
                    $treeNodeGUIDConfigs[$cfg['guid']] = $cfg;
                }
                $nodesPath = Path::createNodesPath($path, $treeNodeGUIDConfigs);
                if (!empty($nodesPath)) {
                    $lastNode = array_pop($nodesPath);

                    $DCConfig = $lastNode->getDC();

                    $guid = empty($DCConfig['from'])
                        ? 'default'
                        : $DCConfig['from'];
                }
            }
        }

        if ($guid) {
            DB\dbQuery(
                'INSERT INTO tree_user_config
                (guid, user_id, cfg)
                VALUES($1, $2, $3)
                ON DUPLICATE KEY UPDATE cfg = $3',
                array(
                    $guid
                    ,User::getId()
                    ,Util\jsonEncode($p['state'])
                )
            );
        }

        return $rez;
    }

    public static function getGridViewState($guid)
    {
        $rez = array();

        $res = DB\dbQuery(
            'SELECT cfg
            FROM tree_user_config
            WHERE  user_id = $1 and guid = $2',
            array(
                User::getId()
                ,$guid
            )
        );

        if ($r = $res->fetch_assoc()) {
            $rez = Util\toJSONArray($r['cfg']);
        }
        $res->close();

        //backward compatibility to extjs3
        if (!empty($rez['sort']['field']) && empty($rez['sort']['property'])) {
            $rez['sort']['property'] = $rez['sort']['field'];
        }

        return $rez;
    }
}
