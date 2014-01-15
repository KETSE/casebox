<?php
namespace CB;

class Templates
{
    public function getChildren($p)
    {
        $rez = array();
        $t = explode('/', $p['path']);
        $nodeId = intval(array_pop($t));

        $res = DB\dbQuery(
            'SELECT id nid
                ,l'.USER_LANGUAGE_INDEX.' `text`
                ,`type`
                ,is_folder
                ,`order`
                ,`visible`
                ,iconCls
                ,(SELECT count(*)
                    FROM templates
                    WHERE pid = t.id) `loaded`
            FROM templates t
            WHERE `type` <> \'template\' and pid'.(($nodeId > 0) ? '=$1' : ' is NULL').'
            ORDER BY `order`
                   ,`type`
                   ,2',
            $nodeId
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $r['loaded'] = empty($r['loaded']);
            if (empty($nodeId)) {
                $r['expanded'] = true;
            }
            array_push($rez, $r);
        }

        return $rez;
    }

    public function createTemplate($p)
    {
        if (!Security::canManage()) {
            throw new \Exception(L\Access_denied);
        }

        $rez = array(
            'pid' => is_numeric(@$p['pid']) ? $p['pid'] : null
            ,'type' => ''
            ,'iconCls' => 'icon-none'
        );
        $values_string = '$1, $2, $3';
        $on_duplicate = '';
        Util\getLanguagesParams($p, $rez, $values_string, $on_duplicate, $p['text']);

        DB\dbQuery(
            'INSERT INTO TEMPLATES ('.implode(',', array_keys($rez)).')
            VALUES ('.$values_string.')
            ON DUPLICATE KEY
            UPDATE '.$on_duplicate,
            array_values($rez)
        ) or die(DB\dbQueryError());

        $rez['nid'] = DB\dbLastInsertId();
        $rez['text'] = $p['text'];
        $rez['loaded'] = true;

        return array( 'success' => true, 'data' => $rez);
    }

    public function createFolder($p)
    {
        if (!Security::canManage()) {
            throw new \Exception(L\Access_denied);
        }
        $res = DB\dbQuery(
            'SELECT id
            FROM templates
            WHERE name = $1 AND pid '.(empty($p['pid']) ? ' is null' : '=$2'),
            array(
                $p['text'],
                @$p['pid']
            )
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            return array( 'success' => false, 'msg' => L\FolderExists);
        }
        $res->close();

        $rez = array(
            'pid' => is_numeric(@$p['pid']) ? $p['pid'] : null
            ,'name' => $p['text']
            ,'type' => ''
            ,'is_folder' => 1
            ,'iconCls' => 'icon-folder'
        );
        $values_string = '$1, $2, $3, $4, $5';
        $on_duplicate = '';
        Util\getLanguagesParams($p['$rez'], $values_string, $on_duplicate, $p['text']);

        DB\dbQuery(
            'INSERT INTO templates ('.implode(',', array_keys($rez)).')
            VALUES ('.$values_string.')
            ON DUPLICATE KEY
            UPDATE '.$on_duplicate,
            $rez
        ) or die(DB\dbQueryError());

        $rez['nid'] = DB\dbLastInsertId();
        $rez['text'] = $p['text'];
        $rez['loaded'] = true;

        return array( 'success' => true, 'data' => $rez);
    }

    public function renameFolder($p)
    {
        if (!Security::canManage()) {
            throw new \Exception(L\Access_denied);
        }
        $name = strip_tags($p['name']);
        DB\dbQuery(
            'UPDATE templates
            SET l'.USER_LANGUAGE_INDEX.' = $2
            WHERE id = $1',
            array(
                $p['id']
                ,$name
            )
        ) or die( DB\dbQueryError() );

        return array(
            'success' => true
            ,'data' => array(
                'id' => $p['id']
                ,'newName' => $name
            )
        );
    }

    public function deleteElement($id)
    {
        if (!Security::canManage()) {
            throw new \Exception(L\Access_denied);
        }
        DB\dbQuery(
            'DELETE FROM templates
            WHERE `type` <> \'template\'
                AND id = $1',
            $id
        ) or die(DB\dbQueryError());

        return array('success' => true, 'id' => $id);
    }

    public function moveElement($p)
    {
        if (!Security::canManage()) {
            throw new \Exception(L\Access_denied);
        }
        /* get old pid */
        $res = DB\dbQuery(
            'SELECT pid, `order`
            FROM templates
            WHERE id = $1',
            $p['id']
        ) or die(DB\dbQueryError());

        $old_pid = 0;
        $old_order = 0;
        if ($r = $res->fetch_assoc()) {
            $old_pid = $r['pid'];
            $old_order = $r['order'];
        }
        $res->close();
        /* end of get old pid */
        $p['target_id'] = is_numeric($p['target_id']) ? $p['target_id'] : null;
        $order = 1;
        switch ($p['point']) {
            case 'above':
                /* get relative node order and pid */
                $res = DB\dbQuery(
                    'SELECT pid, `order`
                    FROM templates
                    WHERE id = $1',
                    $p['target_id']
                ) or die(DB\dbQueryError());

                if ($r = $res->fetch_assoc()) {
                    $p['target_id'] = $r['pid'];
                    $order = $r['order'];
                }
                $res->close();
                DB\dbQuery(
                    'UPDATE templates
                    SET `order` = `order` + 1
                    WHERE pid = $1 AND `order` >= $2',
                    array(
                        $p['target_id']
                        ,$order
                    )
                ) or die(DB\dbQueryError());

                break;
            case 'below':
                /* get relative node order and pid */
                $res = DB\dbQuery(
                    'SELECT pid, `order`
                    FROM templates
                    WHERE id = $1',
                    $p['target_id']
                ) or die(DB\dbQueryError());

                if ($r = $res->fetch_assoc()) {
                    $p['target_id'] = $r['pid'];
                    $order = $r['order']+1;
                }
                $res->close();
                DB\dbQuery(
                    'UPDATE templates
                    SET `order` = `order` + 1
                    WHERE pid = $1 AND `order` >= $2',
                    array(
                        $p['target_id']
                        ,$order
                    )
                ) or die(DB\dbQueryError());

                break;
            default:
                $res = DB\dbQuery(
                    'SELECT max(`order`) `order`
                    FROM templates
                    WHERE pid = $1',
                    $p['target_id']
                ) or die(DB\dbQueryError());

                if ($r = $res->fetch_assoc()) {
                    $order = $r['order']+1;
                }
                $res->close();
        }
        DB\dbQuery(
            'UPDATE templates
            SET pid = $2
                ,`order` = $3
            WHERE id = $1',
            array(
                $p['id']
                ,$p['target_id']
                ,$order
            )
        ) or die(DB\dbQueryError());

        DB\dbQuery(
            'UPDATE templates
            SET `order` = `order` - 1
            WHERE pid = $1
                AND `order` > $2',
            array(
                $old_pid
                ,$old_order
            )
        ) or die(DB\dbQueryError());

        return array('success' => true);
    }
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
                ,t.l'.USER_LANGUAGE_INDEX.' `title`
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
            $data[] = $r;
        }
        $res->close();

        return array('success' => true, 'data' => $data);
    }

    /**
     * laod template data
     * @param  array $p params
     * @return json  responce
     */
    public function loadTemplate($p)
    {
        $id = $p['id'];
        if (!is_numeric($id)) {
            throw new \Exception(L\Wrong_input_data);
        }

        if (!Security::canManage()) {
            throw new \Exception(L\Access_denied);
        }

        /* get field names of template properties editing template */
        $template_fields = array();
        $res = DB\dbQuery(
            'SELECT ts.id
                 , ts.name
            FROM templates_structure ts
            JOIN templates t ON t.id = ts.template_id
            WHERE t.type = \'template\''
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $template_fields[$r['name']] = $r['id'];
        }
        $res->close();
        /* end of get field names of template properties editing template */

        $data = array();
        $res = DB\dbQuery(
            'SELECT id
                ,`type`
                ,name
                ,'.CONFIG\LANGUAGE_FIELDS.'
                ,visible
                ,iconCls
                ,default_field
                ,cfg
            FROM templates
            WHERE id = $1',
            $id
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $cfg = Util\toJSONArray($r['cfg']);
            foreach ($cfg as $k => $v) {
                $r[$k] = $v;
            }
            unset($r['cfg']);
            $data = $r;
        }
        $res->close();
        foreach ($data as $k => $v) {
            if (isset($template_fields[$k])) {
                $data['properties']['values']['f'.$template_fields[$k].'_0'] = array('value' => $v);
                if ($k !== 'iconCls') {
                    unset($data[$k]);
                }
            }
        }

        return array('success' => true, 'data'  => $data);
    }

    public function getTemplatesStructure()
    {
        $rez = array('success' => true, 'data' => array());
        $res = DB\dbQuery(
            'SELECT ts.id
                ,ts.pid
                ,t.id template_id
                ,ts.`level`
                ,ts.`name`
                ,ts.l'.USER_LANGUAGE_INDEX.' `title`
                ,ts.`type`
                ,ts.`order`
                ,ts.cfg
                ,(coalesce(t.title_template,\'\') <> \'\' ) `has_title_template`
            FROM templates t
            LEFT JOIN templates_structure ts
                ON t.id = ts.template_id
            ORDER BY template_id, `order`'
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $t = $r['template_id'];
            unset($r['template_id']);
            if (($r['type'] == '_auto_title') && ($r['has_title_template'] == 0)) {
                $r['type'] = 'varchar';
            }
            unset($r['has_title_template']);
            $data[$t][] = $r;
        }
        $res->close();

        return array('success' => true, 'data'  => $data);
    }

    public function saveTemplate($p)
    {
        if (!Security::canManage()) {
            throw new \Exception(L\Access_denied);
        }
        $d = json_decode($p['data'], true);

        /* get field names of template properties editing template */
        $template_fields = array();
        $res = DB\dbQuery(
            'SELECT ts.id
                 , ts.name
            FROM templates_structure ts
            JOIN templates t ON t.id = ts.template_id
            WHERE t.type =\'template\''
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $template_fields[$r['id']] = $r['name'];
        }
        $res->close();

        $cfgProperties = array('gridJsClass', 'files', 'main_file');
        $cfg = array();
        $params = array(
            'id' => empty($d['id']) ? null: $d['id']
        );
        $values_string = array('$1');
        $on_duplicate = array();//'`order` = $2, visible = $3, iconCls = $4, default_field = $5, cfg = $6';
        $i = 1;

        if (!empty($d['properties']['values'])) {
            foreach ($d['properties']['values'] as $f => $fv) {
                $id = explode('_', $f);
                $id = array_shift($id);
                $id = substr($id, 1);
                if (isset($template_fields[$id])) {
                    if (in_array($template_fields[$id], $cfgProperties)) {
                        $cfg[$template_fields[$id]] = $fv['value'];
                    } else {
                        $i++;
                        $params[$template_fields[$id]] = $fv['value'];
                        $values_string[] = '$'.$i;
                        $on_duplicate[] = '`'.$template_fields[$id].'` = $'.$i;
                        if ($template_fields[$id] == 'iconCls') {
                            $d['iconCls'] = $fv['value'];
                        }
                        if ($template_fields[$id] == 'visible') {
                            $d['visible'] = $fv['value'];
                        }
                    }
                }
            }
        }
        $i++;
        $cfg = json_encode($cfg, JSON_UNESCAPED_UNICODE);
        $params['cfg'] = $cfg;
        $values_string[] = '$'.$i;
        $on_duplicate[] = '`cfg` = $'.$i;
        $values_string = implode(', ', $values_string);
        $on_duplicate = implode(', ', $on_duplicate);

        Util\getLanguagesParams($p, $params, $values_string, $on_duplicate);

        DB\dbQuery(
            'INSERT INTO templates (`'.implode('`,`', array_keys($params)).
            '`) VALUES ('.$values_string.') ON DUPLICATE KEY UPDATE '.
            $on_duplicate,
            array_values($params)
        ) or die(DB\dbQueryError());

        if (!is_numeric($params['id'])) {
            $params['id'] = DB\dbLastInsertId();
        }

        return array('success' => true, 'data' => $d);
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
