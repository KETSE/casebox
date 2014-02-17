<?php
namespace CB;

class Tree
{

    protected static $path = [];
    protected static $treeNodeConfigs = array();
    protected static $treeNodeGUIDConfigs = array();
    protected static $treeNodeClasses = array();

    protected static $_instance = null;

    /**
    * Prevent direct object creation
    */
    final private function __construct()
    {
    }

    /**
    * Prevent object cloning
    */
    final private function __clone()
    {
    }

    public static function initNodeClasses()
    {
        static::$treeNodeClasses = array();
        foreach (static::$treeNodeConfigs as $p => $cfg) {
            $class = empty($cfg['class']) ? '\\CB\\TreeNode\\'.$p : $cfg['class'];
            $cfg['guid'] = static::getGUID($p);
            $cfg['class'] = $class;

            try {
                $class = new $class($cfg);
                static::$treeNodeGUIDConfigs[$cfg['guid']] = $cfg;
                static::$treeNodeClasses[$cfg['guid']] = $class;
            } catch (\Exception $e) {
                debug('error creating class '.$class);
            }
        }
    }

    protected function createNodesPath()
    {

        static::$path = array();
        $path = explode('/', @static::$requestParams['path']); ///?????????????????????????????????
        while (!empty($path)) {
            $npid = null;
            $nodeId = null;

            $el = array_shift($path);
            if (empty($el)) {
                continue;
            }

            $el = explode('-', $el);
            if (sizeof($el) > 1) {
                $npid = $el[0];
                $nodeId = $el[1];
            } else {
                $npid = static::getGUID('Dbnode');
                $nodeId = $el[0];
            }

            $cfg = empty(static::$treeNodeGUIDConfigs[$npid])
                ? array( 'class' => 'CB\TreeNode\\Dbnode', 'guid' => $npid)
                : static::$treeNodeGUIDConfigs[$npid];

            $class = new $cfg['class']($cfg, $nodeId);
            //set parent node
            if (!empty(static::$path)) {
                $class->parent = static::$path[sizeof(static::$path) - 1];
            }

            array_push(
                static::$path,
                $class
            );
        };
    }

    protected function getPathText()
    {
        $rez = array();
        if (empty(static::$path)) {
            return '/';
        }

        foreach (static::$path as $n) {
            $rez[] = $n->getName();
        }

        return implode('/', $rez);
    }

    protected function getPathProperties()
    {
        $rez = array();
        if (empty(static::$path)) {
            $rez['path'] = '/';
        } else {
            $rez = static::$path[sizeof(static::$path) - 1]->getData();

            $idsPath = array();
            foreach (static::$path as $n) {
                $idsPath[] = $n->getId();
            }

            $rez['path'] = '/'.implode('/', $idsPath);
        }

        return $rez;
    }

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

    public static function getNodeConfigs()
    {
        return Config::get('treeNodes', array('Dbnode' => array()));
    }
    /**
    * Returns new or existing Singleton instance
    * @return Singleton
    */
    final public static function getInstance()
    {
        if (null !== static::$_instance) {
            return static::$_instance;
        }
        static::$_instance = new static();

        return static::$_instance;
    }
}
