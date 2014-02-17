<?php
namespace DisplayColumns;

use CB\DB;
use CB\Util;
use CB\Templates;

class DCSingleton
{
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

    /**
     * check if custom columns set for this node or any of its parents
     * @param  int   $nodeId
     * @return array
     */
    public static function getNodeCustomDisplayColumns($nodeId)
    {
        //verify if already have cached result
        $var_name = 'CDC['.$nodeId.']';
        if (\CB\Cache::exist($var_name)) {
            return \CB\Cache::get($var_name);
        }

        $rez = array();

        //get pids path
        $pids = '';
        $res = DB\dbQuery(
            'SELECT pids FROM tree_info WHERE id = $1',
            $nodeId
        ) or die(DB\dbQueryError());
        if ($r = $res->fetch_assoc()) {
            $pids = $r['pids'];
        }
        $res->close();

        if (empty($pids)) {
            return $rez;
        }

        //get properties for pids and detect closest DisplayColumns settings
        $properties = array();
        $res = DB\dbQuery(
            'SELECT t.id, t.cfg, uc.cfg `ucfg`
            FROM tree t
            LEFT JOIN tree_user_config uc
            ON t.id = uc.id AND uc.user_id = $1
            WHERE t.id IN ('.$pids.')',
            $_SESSION['user']['id']
        ) or die(DB\dbQueryError());
        while ($r = $res->fetch_assoc()) {
            $r['cfg'] = Util\toJSONArray($r['cfg']);
            $r['ucfg'] = Util\toJSONArray($r['ucfg']);
            $properties[$r['id']] = $r;
        }
        $res->close();

        // iterate node branch from the end to find the closes config
        $pids = explode(',', $pids);
        $sortedProperties = array();
        $i = sizeof($pids) - 1;
        while (empty($rez) && ($i >= 0)) {
            $p = &$properties[$pids[$i]];
            if (!empty($p['ucfg']['DC'])) {
                $rez = $p['ucfg']['DC'];
            } elseif (!empty($p['cfg']['DC'])) {
                $rez = $p['cfg']['DC'];
            }
            $i--;
        }

        \CB\Cache::set($var_name, $rez);

        return $rez;
    }

    /**
     * check if custom columns set for given template
     * @param  int   $templateId
     * @return array
     */
    public static function getTemplateCustomDisplayColumns($templateId)
    {
        $rez = array();
        $template = Templates\SingletonCollection::getInstance()->getTemplate($templateId);
        $data = $template->getData();
        if (!empty($data['cfg']['DC'])) {
            $rez = $data['cfg']['DC'];
        }

        return $rez;
    }

    /**
     * check if custom columns set for given template
     * @param  int   $templateId
     * @return array
     */
    public static function getCustomDisplayColumns($nodeId = false, $templateId = false)
    {
        $rez = array();
        if ($templateId !== false) {
            $rez = static::getTemplateCustomDisplayColumns($templateId);
        }
        if (empty($rez) && ($nodeId !== false)) {
            $rez = static::getNodeCustomDisplayColumns($nodeId);
        }

        return $rez;
    }

    /**
     * get solr columns for a node or for a search template
     * @param  boolean $nodeId
     * @param  boolean $templateId
     * @return array
     */
    public static function getSolrColumns($nodeId = false, $templateId = false)
    {
        $rez = array();
        $displayColumns = static::getCustomDisplayColumns($nodeId, $templateId);
        foreach ($displayColumns as $column) {
            if (is_array($column) && !empty($column['solr_column_name'])) {
                $rez[$column['solr_column_name']] = 1;
            }
        }

        if (!empty($rez)) {
            $rez = array_keys($rez);
        }

        return $rez;
    }
}
