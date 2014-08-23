<?php
namespace CB;

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

    public static function getPath($id)
    {
        $rez = array('success' => false);
        if (!is_numeric($id)) {
            return $rez;
        }
        $res = DB\dbQuery(
            'SELECT pids FROM tree_info WHERE id = $1',
            $id
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $r['pids'] = str_replace(',', '/', $r['pids']);
            $rez = array('success' => true, 'id' => $id, 'path' => $r['pids']);
        }
        $res->close();

        return $rez;
    }

    public static function getPidPath($id)
    {
        $rez = array('success' => false);
        if (!is_numeric($id)) {
            return $rez;
        }
        $res = DB\dbQuery(
            'SELECT ti.pids
            FROM tree t
            JOIN tree_info ti ON t.id = ti.id
            WHERE t.id = $1',
            $id
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $r['pids'] = explode(',', $r['pids']);
            array_pop($r['pids']);
            $r['pids'] = implode('/', $r['pids']);
            $rez = array('success' => true, 'id' => $id, 'path' => $r['pids']);
        }
        $res->close();

        return $rez;
    }

    /**
     * return textual repsentation of an ids path
     * @param  varchar | array $p direct path string or an array containig 'path' index defined
     * @return varchar
     */
    public static function getPathText($p)
    {
        $path = '';
        if (is_array($p)) {
            if (isset($p['path'])) {
                $path = $p['path'];
            }
        } elseif (is_string($p)) {
            $path = $p;
        }

        if (empty($path)) {
            $path = '/';
        }

        while ($path[0] == '/') {
            $path = substr($path, 1);
        }

        $path = explode('/', $path);
        $ids = array_filter($path, 'is_numeric');
        $id = array_pop($ids);

        $res = DB\dbQuery('SELECT pids FROM tree_info WHERE id = $1', $id) or die(DB\dbQueryError());
        if ($r = $res->fetch_assoc()) {
            $path = explode(',', $r['pids']);
            if (!empty($path) && empty($path[0])) {
                array_shift($path);
            }
            array_shift($path);
            $ids = $path;
        }
        $res->close();

        if (empty($path)) {
            return '/';
        }
        if ($path[0] == Browser::getRootFolderId()) {
            array_shift($path);
        }
        if (empty($ids)) {
            return '/';
        }

        $names = array();
        $res = DB\dbQuery(
            'SELECT id
                ,name
            FROM tree
            WHERE id IN ('.implode(', ', $ids).')'
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $names[$r['id']] = htmlspecialchars($r['name'], ENT_COMPAT);
        }
        $res->close();
        $rez = array();
        for ($i=0; $i < sizeof($path); $i++) {
            if (isset($names[$path[$i]])) {
                $rez[] = L\getTranslationIfPseudoValue($names[$path[$i]]);
            } else {
                $rez[] = $path[$i];
            }
        }

        return '/'.implode('/', $rez);
    }

    /**
     * return generic properties for a path of ids
     * @param  varchar | array $p direct path string or an array containig 'path' index defined
     * @return varchar
     */
    public static function getPathProperties($p)
    {
        $path = '';
        if (is_array($p)) {
            if (isset($p['path'])) {
                $path = $p['path'];
            }
        } else {
            $path = $p;
        }

        if (empty($path)) {
            $path = '/';
        }
        while ($path[0] == '/') {
            $path = substr($path, 1);
        }
        $path = explode('/', $path);
        $ids = array_filter($path, 'is_numeric');
        if (empty($ids)) {
            $ids = array(Browser::getRootFolderId());
            $path = $ids;
        }

        $rez = array();
        $lastId = array_pop($ids);
        $res = DB\dbQuery(
            'SELECT t.id
                ,t.name
                ,t.`system`
                ,t.`type`
                ,ti.pids `path`
                ,ti.`case_id`
                ,t.`template_id`
                ,tt.`type` template_type
            FROM tree t
            JOIN tree_info ti on t.id = ti.id
            LEFT JOIN templates tt ON t.template_id = tt.id
            WHERE t.id = $1',
            $lastId
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $r['path'] = str_replace(',', '/', $r['path']);
            $rez = $r;
        }
        $res->close();

        return $rez;
    }

    /**
     * tree nodes can contain Translation variable in place of name like: [MyDocuments]
     * @param  vrchar  $path
     * @return varchar
     */
    public static function replaceCustomNames($path)
    {
        $path = explode('/', $path);
        for ($i=0; $i < sizeof($path); $i++) {
            $path[$i] = L\getTranslationIfPseudoValue($path[$i]);
        }
        $path = implode('/', $path);

        return $path;
    }

    /**
     * create node classes for given node configs
     * @param  array $nodeConfigs
     * @return array
     */
    public static function getNodeClasses($nodeConfigs)
    {
        $rez = array();

        foreach ($nodeConfigs as $p => $cfg) {
            $class = empty($cfg['class']) ? '\\CB\\TreeNode\\'.$p : $cfg['class'];
            $cfg['guid'] = static::getGUID($p);
            $cfg['class'] = $class;

            try {
                $class = new $class($cfg);
                $rez[$cfg['guid']] = $class;
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
            if (($el == $rootNodeCfg['id']) && (intval($el) == 0)) {
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
        $rez = null;
        $res = DB\dbQuery(
            'SELECT id FROM `casebox`.guids WHERE name = $1',
            $name
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $rez = $r['id'];
        } else {
            DB\dbQuery(
                'INSERT INTO `casebox`.guids
                (`name`)
                VALUES ($1)',
                $name
            ) or die(DB\dbQueryError());
            $rez = DB\dbLastInsertId();
        }
        $res->close();

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
        foreach ($treeNodeConfigs as $plugin => $cfg) {
            $class = empty($cfg['class']) ? '\\CB\\TreeNode\\'.$plugin : $cfg['class'];
            $cfg['guid'] = static::getGUID($plugin);
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
