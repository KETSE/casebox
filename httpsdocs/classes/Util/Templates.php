<?php
namespace Util;

use CB\DB as DB;

class Templates
{
    /**
     * get Template id for a specified config properties
     * @return int|null first template found with given properties
     */
    public static function getTemplateId($config)
    {
        if (empty($config)) {
            return null;
        }

        $rez = null;
        $paramValues = array_values($config);
        $params = array_keys($config);
        for ($i = 1; $i <= sizeof($params); $i++) {
            $params[$i-1] .= ' = $'.$i;
        }
        $res = DB\dbQuery(
            'SELECT id
            FROM templates
            WHERE ('.implode(') AND (', $params).') ',
            $paramValues
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $rez = $r['id'];
        }
        $res->close();

        return $rez;
    }

    /**
     * detect if a template with name and type exists in templates
     * and if it has a corresponding id in tree
     * @return int || null
     */
    public static function detectTemplate($name, $type)
    {
        // $rez = null;

        // $res = DB\dbQuery(
        //     'SELECT t.id
        //     FROM tree t
        //     JOIN templates tt
        //      ON t.`template_id` = tt.id
        //      AND tt.type = \'template\'
        //      AND tt.name = \'TemplatesTemplate\'
        //     WHERE t.template_id = t.id'
        // ) or die(DB\dbQueryError());

        // if ($r = $res->fetch_assoc()) {
        //     $rez = $r['id'];
        // }
        // $res->close();

        // return $rez;
    }

    /**
     * check if template exists under pid and create or update it
     * @return int template id
     */
    public static function createOrUpdateTemplate($pid, &$config)
    {
        $id = null;
        $res = DB\dbQuery(
            'SELECT id
            FROM templates
            WHERE name = $1
                AND type = $2
                AND pid = $3',
            array(
                $config['name']
                ,$config['type']
                ,$pid
            )
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $id = $r['id'];
        }
        $res->close();

        $obj = new \CB\Objects\Template();

        if (empty($id)) {
            $id = $obj->create($config);
            $config['id'] = $id;
        } else {
            $config['id'] = $id;
            $obj->update($config);
        }

        return $id;
    }
}
