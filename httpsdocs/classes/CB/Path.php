<?php
namespace CB;

use CB\DataModel as DM;
use CB\Util;

class Path
{
    /* get last element id from a path or return root folder id if no int element is found */
    public static function getId($path = '')
    {
        $path = trim($path);
        while (!empty($path) && (substr($path, -1) == '/')) {
            $path = substr($path, 0, strlen($path)-1);
        }
        $id = explode('/', $path);
        $id = array_pop($id);
        $id = is_numeric($id) ? $id : Browser::getRootFolderId();

        return $id;
    }

    public static function getPath($id, $excludeItself = false)
    {
        $rez = array('success' => false);
        if (!is_numeric($id)) {
            return $rez;
        }

        $r = DM\Tree::getBasicInfo($id);

        if (!empty($r)) {
            $p = explode(',', $r['pids']);
            if ($excludeItself) {
                array_pop($r['pids']);
            }
            $p = implode('/', $p);

            $rez = array(
                'success' => true,
                'id' => $id,
                'name' => $r['name'],
                'path' => $p
            );
        }

        return $rez;
    }

    public static function getPidPath($id)
    {
        return static::getPath($id, true);
    }

    /**
     * create node classes for given node configs
     * @param  array $nodeConfigs
     * @return array
     */
    public static function getNodeClasses($nodeConfigs)
    {
        $rez = array();

        $guids = static::getGUIDs(array_keys($nodeConfigs));
        foreach ($nodeConfigs as $p => $cfg) {
            $class = empty($cfg['class']) ? '\\CB\\TreeNode\\'.$p : $cfg['class'];
            $cfg['guid'] = $guids[$p]; //static::getGUID($p);
            $cfg['class'] = $class;

            try {
                if (class_exists($class)) {
                    $class = new $class($cfg);
                    $rez[$cfg['guid']] = $class;
                }
            } catch (\Exception $e) {
                debug('error creating class '.$class);
            }
        }

        return $rez;
    }

    /**
     * create an array of node classes for given path and nodeConfigs
     * @param  varchar $path
     * @param  array   $treeNodeGUIDConfigs
     * @return array
     */
    public static function createNodesPath($path, $treeNodeGUIDConfigs)
    {

        $rez = array();
        $path = explode('/', $path);

        $rootNodeCfg = Util\toJSONArray(Config::get('rootNode'));

        while (!empty($path)) {
            $npid = null;
            $nodeId = null;

            $el = array_shift($path);
            if (strlen($el) < 1) {
                continue;
            }

            //analize virtual root node
            if (!empty($rootNodeCfg) && ($el == $rootNodeCfg['id']) && (intval($el) == 0)) {
                $rootNodeCfg['class'] = 'CB\\TreeNode\\Base';
                $rootNodeCfg['guid'] = 0;
                $class = new \CB\TreeNode\Base($rootNodeCfg, $el);
                array_push(
                    $rez,
                    $class
                );
                continue;
            }

            $el = explode('-', $el);
            if (sizeof($el) > 1) {
                $npid = array_shift($el);
                $nodeId = implode('-', $el);
            } else {
                $npid = static::getGUID('Dbnode');
                $nodeId = $el[0];
            }

            $cfg = empty($treeNodeGUIDConfigs[$npid])
                ? array( 'class' => 'CB\TreeNode\\Dbnode', 'guid' => $npid)
                : $treeNodeGUIDConfigs[$npid];

            $class = new $cfg['class']($cfg, $nodeId);
            //set parent node
            if (!empty($rez)) {
                $class->parent = $rez[sizeof($rez) - 1];
            }

            array_push(
                $rez,
                $class
            );
        }

        return $rez;
    }

    /**
     * get GUID for a given virtual tree node name
     * @param  varchar $name
     * @return int
     */
    public static function getGUID($name)
    {
        $rez = static::getGUIDs(array($name));

        $rez = empty($rez[$name])
            ? null
            : $rez[$name];

        return $rez;
    }

    /**
     * get GUIDs virtual tree node names array
     * @param  array $names
     * @return int
     */
    public static function getGUIDs($names)
    {
        $rez = array();
        $guids = Cache::get('GUIDS', array());

        if (!empty($guids)) {
            foreach ($names as $name) {
                if (!empty($guids[$name])) {
                    $rez[$name] = $guids[$name];
                }
            }

            //remove names retreived from cache
            $names = array_diff($names, array_keys($rez));
        }

        //get remained names from db
        if (!empty($names)) {
            $dbNames = DM\GUID::readNames($names);
            $guids = array_merge($guids, $dbNames);
            $rez = array_merge($rez, $dbNames);

            //remove names retreived from db
            $names = array_diff($names, array_keys($rez));
        }

        //create guids for remained names
        foreach ($names as $name) {
            $rez[$name] = DM\GUID::create(
                array(
                    'name' => $name
                )
            );

            $guids[$name] = $rez[$name];
        }

        //update cache variable
        Cache::set('GUIDS', $guids);

        return $rez;
    }

    //------------------------------------------------------------------------

    /**
     * try to detect real target id from a given path/path element
     * $p  path or path element
     * @return int | null
     */
    public static function detectRealTargetId($p)
    {
        $rootId = Browser::getRootFolderId();
        $rez = $rootId;
        if (empty($p)) {
            return $rez;
        }

        $treeNodeConfigs = Config::get('treeNodes', array('Dbnode' => array()));
        $GUIDConfigs = array();
        $guids = static::getGUIDs(array_keys($treeNodeConfigs));
        foreach ($treeNodeConfigs as $plugin => $cfg) {
            $class = empty($cfg['class']) ? '\\CB\\TreeNode\\'.$plugin : $cfg['class'];
            $cfg['guid'] = $guids[$plugin]; //static::getGUID($plugin);
            $cfg['class'] = $class;
            $GUIDConfigs[$cfg['guid']] = $cfg;
        }

        $path = explode('/', @$p);
        while (!empty($path) && empty($path[0])) {
            array_shift($path);
        }
        while (!empty($path) && empty($path[sizeof($path)-1])) {
            array_pop($path);
        }
        if (empty($path)) {
            return $rez;
        }

        $rez = null;
        while (is_null($rez) && !empty($path)) {
            $el = array_pop($path);
            if (is_numeric($el)) { //it's a real node id
                $rez = $el;
            } else {
                list($guid, $el) = explode('-', $el);
                if (!empty($GUIDConfigs[$guid]['realNodeId'])) {
                    $rez = $GUIDConfigs[$guid]['realNodeId'];
                }
            }
        }

        if (empty($rez) || ($rez == 'root')) {
            $rez = $rootId;
        }

        return $rez;
    }
}
