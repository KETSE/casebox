<?php
namespace CB\Browser;

use CB\DB as DB;

class CreateMenu
{
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
            'SELECT id FROM `menu` WHERE node_ids = $1',
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
            'SELECT id FROM `menu` WHERE node_template_ids = $1',
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
