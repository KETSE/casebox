<?php
namespace CB;

use CB\Util;

class Templates
{
    /**
     * return templates list
     * @param  array $p
     * @return json  response
     */
    public function readAll($p)
    {
        $data = array();
        $res = DB\dbQuery(
            'SELECT t.id
                ,t.pid
                ,t.type
                ,t.l' . Config::get('user_language_index') . ' `title`
                ,t.iconCls
                ,t.cfg
                ,t.info_template
                ,`visible`
            FROM templates t
            WHERE is_folder = 0
            ORDER BY 3, `order`, 4'
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $r['cfg'] = Util\toJSONArray($r['cfg']);
            if (!empty($r['cfg']['source']['fn'])) {
                unset($r['cfg']['source']['fn']);
            }
            $data[] = $r;
        }
        $res->close();

        return array('success' => true, 'data' => $data);
    }

    public function getTemplatesStructure()
    {
        $rez = array('success' => true, 'data' => array());
        $res = DB\dbQuery(
            'SELECT ts.id
                ,ts.pid
                ,t.id template_id
                ,t.type template_type
                ,ts.`name`
                ,ts.l' . Config::get('user_language_index') . ' `title`
                ,ts.`type`
                ,ts.`order`
                ,ts.cfg
                ,(coalesce(t.title_template,\'\') <> \'\' ) `has_title_template`
            FROM templates t
                LEFT JOIN templates_structure ts
                    ON t.id = ts.template_id
                ,tree tr
            WHERE ts.id = tr.id and tr.dstatus = 0
            ORDER BY ts.template_id, ts.`order`'
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $t = $r['template_id'];
            unset($r['template_id']);

            if (($r['type'] == '_auto_title') && ($r['has_title_template'] == 0)) {
                $r['type'] = 'varchar';
            }
            unset($r['has_title_template']);

            if ($r['pid'] == $t) {
                $r['pid'] = null;
            }

            $r['cfg'] = Util\toJSONArray($r['cfg']);

            if (!empty($r['cfg']['source']['fn'])) {
                unset($r['cfg']['source']['fn']);
            }

            if (($r['template_type'] == 'search') && empty($r['cfg']['cond'])) {
                $r['cfg']['cond'] = '=';
            }
            unset($r['template_type']);

            $data[$t][] = $r;
        }
        $res->close();

        return array('success' => true, 'data'  => $data);
    }

    /**
     * get template ids by template type
     * @param  varchar $type
     * @return array
     */
    public static function getIdsByType($type)
    {
        $rez = array();
        $res = DB\dbQuery(
            'SELECT id
            FROM templates
            WHERE `type` = $1',
            $type
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $rez[] = $r['id'];
        }
        $res->close();

        return $rez;
    }
}
