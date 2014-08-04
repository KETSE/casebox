<?php
namespace CB\Browser;

use CB\DB;
use CB\Util;

class CreateMenu
{
    /**
     * get the menu config for a given path or id
     * @param  varchar | int $path path string or node id
     * @return [type]        [description]
     */
    public static function getMenuForPath($path)
    {
        $rez = '';

        //get item path if id specified
        if (is_numeric($path)) {
            $tmp = \CB\Path::getPath($path);
            $path = $tmp['path'];
        }

        if (is_string($path)) {
            $path = explode('/', $path);
        }

        $path = array_reverse(array_filter($path, 'is_numeric'));
        $path = Util\toNumericArray($path);

        // get templates for each path elements
        $nodeTemplate = array();
        $res = DB\dbQuery(
            'SELECT id, template_id
            FROM tree
            WHERE id in (' . implode(',', $path) . ')'
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $nodeTemplate[$r['id']] = $r['template_id'];
        }
        $res->close();

        //get db menu into variable
        $menu = array();
        $res = DB\dbQuery(
            'SELECT
                node_ids `nids`
                ,node_template_ids `ntids`
                ,user_group_ids `ugids`
                ,menu
            FROM menu'
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $r['nids'] = Util\toNumericArray($r['nids']);
            $r['ntids'] = Util\toNumericArray($r['ntids']);
            $r['ugids'] = Util\toNumericArray($r['ugids']);
            $menu[] = $r;
        }
        $res->close();

        $ugids = $_SESSION['user']['groups'];
        $ugids[] = $_SESSION['user']['id'];

        // we have 3 main criterias for detecting needed menu:
        //  - user_group_ids - records for specific users or groups
        //  - node_ids
        //  - template_ids
        //
        // we'll iterate the path from the end and detect the menu

        // var_dump($path);
        // var_dump($menu);
        $lastWeight = 0;
        for ($i=0; $i < sizeof($path); $i++) {
            //firstly we'll check if we find a menu row with id or template of the node

            foreach ($menu as $m) {
                $weight = 0;

                if (in_array($path[$i], $m['nids'])) {
                    $weight += 50;
                } elseif (empty($m['nids'])) {
                    $weight += 1;
                } else {
                    //skip this record because it contain nids and not contain this node id
                    continue;
                }

                if (@in_array($nodeTemplate[$path[$i]], $m['ntids'])) {
                    $weight += 50;
                } elseif (empty($m['ntids'])) {
                    $weight += 1;
                } else {
                    //skip this record because it has ntids specified and not contain this node template id
                    continue;
                }

                if (empty($m['ugids'])) {
                    $weight += 1;
                } else {
                    $int = array_intersect($ugids, $m['ugids']);
                    if (empty($int)) {
                        continue;
                    } else {
                        $weight += 10;
                    }
                }

                if ($weight > $lastWeight) {
                    $lastWeight = $weight;
                    $rez = $m['menu'];
                }
            }
            //if nid matched or template matched then dont iterate further
            if ($lastWeight > 50) {
                return $rez;
            }
        }

        return $rez;
    }

    /**
     * update menu for a given tree node
     * @param int           $nodeId
     * @param varchar|array $menuConfig
     */
    public static function updateMenuForNode($nodeId, $menuConfig)
    {
        if (is_array($menuConfig)) {
            $menuConfig = implode(',', $menuConfig);
        }
        $menuId = null;
        $res = DB\dbQuery(
            'SELECT id
            FROM `menu`
            WHERE node_ids = $1',
            $nodeId
        ) or die(DB\dbQueryError());
        if ($r = $res->fetch_assoc()) {
            $menuId = $r['id'];
        }
        $res->close();
        CreateMenu::replaceMenuForNode($menuId, $nodeId, $menuConfig);
    }

    /**
     * update menu for a given template_id
     * @param int           $templateId
     * @param varchar|array $menuConfig
     */
    public static function updateMenuForTemplate($templateId, $menuConfig)
    {
        if (is_array($menuConfig)) {
            $menuConfig = implode(',', $menuConfig);
        }
        $menuId = null;
        $res = DB\dbQuery(
            'SELECT id
            FROM `menu`
            WHERE node_template_ids = $1',
            $templateId
        ) or die(DB\dbQueryError());
        if ($r = $res->fetch_assoc()) {
            $menuId = $r['id'];
        }
        $res->close();

        DB\dbQuery(
            'INSERT INTO menu (
                id
                ,node_template_ids
                ,menu)
            VALUES ($1, $2, $3)
            ON DUPLICATE KEY UPDATE
            menu = $3',
            array(
                $menuId
                ,$templateId
                ,$menuConfig
            )
        ) or die(DB\dbQueryError());
    }

    /**
     * add a template for a given tree node
     * @param int $nodeId
     * @param int $templateId
     */
    public static function addTemplateForNode($nodeId, $templateId)
    {
        $menuId = null;
        $menu = '';

        $res = DB\dbQuery(
            'SELECT id, menu
            FROM menu
            WHERE node_ids = $1',
            $nodeId
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $menuId = $r['id'];
            $menu = $r['menu'];
        }
        $res->close();

        if (strpos(','.$menu.',', ','.$templateId.',') === false) {
            if (!empty($menu)) {
                $menu .= ',';
            }
            $menu .= $templateId;
        }
        CreateMenu::replaceMenuForNode($menuId, $nodeId, $menu);
    }

    protected static function replaceMenuForNode($menuId, $nodeId, $menu)
    {
        DB\dbQuery(
            'INSERT INTO menu (
                id
                ,node_ids
                ,menu)
            VALUES ($1, $2, $3)
            ON DUPLICATE KEY UPDATE
            menu = $3',
            array(
                $menuId
                ,$nodeId
                ,$menu
            )
        ) or die(DB\dbQueryError());
    }
}
