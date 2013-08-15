<?php
namespace CB;

if (!Security::canManage()) {
    throw new \Exception(L\Access_denied);
}

class System
{
    public function tagsGetChildren($params)
    {
        $rez = array();
        $t = explode('/', $params->path);
        $nodeId = intval(array_pop($t));
        $res = DB\dbQuery(
            'SELECT id
                    ,'.CONFIG\LANGUAGE_FIELDS.'
                    ,`type`
                    ,`order`
                    ,`hidden`
                      ,(SELECT count(*)
                        FROM tags
                        WHERE pid = t.id) `hasChildren`
                    , iconCls
                FROM tags t
                WHERE pid'.( ($nodeId > 0) ? '=$1' : ' IS NULL' ).'
                    AND user_id IS NULL
                    AND group_id IS NULL
                ORDER BY `type`
                       ,`order`
                       ,l'.USER_LANGUAGE_INDEX,
            $nodeId
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            if ($r['type'] == 1) {
            } else {
                unset($r['iconCls']);
                if (empty($nodeId)) {
                    $r['expanded'] = true;
                }
            }
            $r['loaded'] = empty($r['hasChildren']);
            unset($r['hasChildren']);
            array_push($rez, $r);
        }

        return $rez;
    }

    public function getTagPath($id)
    {
        $id = explode('-', $id);
        $id = array_pop($id);
        $rez = array('success' => true, 'path' => null);
        $res = DB\dbQuery('SELECT f_get_tag_pids($1)', $id) or die(DB\dbQueryError());
        if ($r = $res->fetch_row()) {
            $rez['path'] = $r[0];
        }

        return $rez;
    }

    public function tagsSaveElement($params)
    {
        $p = array(
            'id' => empty($params->id) ? null: $params->id
            ,'pid' => (empty($params->pid) || (!is_numeric($params->pid))) ? null: $params->pid
            ,'type' => empty($params->type) ? 0: 1
            ,'hidden' => empty($params->hidden) ? null: 1
            ,'iconCls' => (empty($params->iconCls) || ($params->iconCls == 'icon-tag-small')) ? null: $params->iconCls
        );
        $values_string = '$1, $2, $3, $4, $5';
        $on_duplicate =  'hidden = $4, iconCls = $5';

        if (empty($params->id)) {
            $p['order'] = 0;
            $res = DB\dbQuery(
                'SELECT max(`order`) from tags where pid'.( (empty($params->pid) || (!is_numeric($params->pid))) ? ' is null ' : ' = '.$params->pid)
            ) or die(DB\dbQueryError());
            if ($r = $res->fetch_row()) {
                $p['order'] = intval($r[0]) + 1;
            }
            $res->close();
            $values_string .= ', $6';
            $on_duplicate .= ',`order` = $6';
        }

        Util\getLanguagesParams($params, $p, $values_string, $on_duplicate);
        DB\dbQuery(
            'INSERT into tags (`'.implode('`,`', array_keys($p)).'`) VALUES ('.$values_string.
            ') ON DUPLICATE KEY UPDATE '.$on_duplicate,
            array_values($p)
        ) or die(DB\dbQueryError());

        if (!is_numeric(@$params->id)) {
            $p['id'] = DB\dbLastInsertId();
        }
        $p['loaded'] = true;

        return array( 'success' => true, 'data' => $p);
    }

    public function tagsMoveElement($params)
    {
        /* get old pid */
        $res = DB\dbQuery(
            'SELECT pid
                 , `order`
            FROM tags
            WHERE id = $1',
            $params->id
        ) or die(DB\dbQueryError());

        $old_pid = 0;
        $old_order = 0;
        if ($r = $res->fetch_row()) {
            $old_pid = $r[0];
            $old_order = $r[1];
        }
        $res->close();
        /* end of get old pid */
        $params->toId = is_numeric($params->toId) ? $params->toId : null;
        $order = 1;
        switch ($params->point) {
            case 'above':
                /* get relative node order and pid */
                $res = DB\dbQuery(
                    'SELECT pid
                         , `order`
                    FROM tags
                    WHERE id = $1',
                    $params->toId
                ) or die(DB\dbQueryError());

                if ($r = $res->fetch_row()) {
                    $params->toId = $r[0];
                    $order = $r[1];
                }
                $res->close();
                DB\dbQuery(
                    'UPDATE tags
                    SET `order` = `order` + 1
                    WHERE pid = $1
                        AND `order` >= $2',
                    array(
                        $params->toId
                        ,$order
                    )
                ) or die(DB\dbQueryError());
                break;
            case 'below':
                /* get relative node order and pid */
                $res = DB\dbQuery(
                    'SELECT pid
                         , `order`
                    FROM tags
                    WHERE id = $1',
                    $params->toId
                ) or die(DB\dbQueryError());

                if ($r = $res->fetch_row()) {
                    $params->toId = $r[0];
                    $order = $r[1]+1;
                }
                $res->close();
                DB\dbQuery(
                    'UPDATE tags
                    SET `order` = `order` + 1
                    WHERE pid = $1
                        AND `order` >= $2',
                    array(
                        $params->toId
                        ,$order
                    )
                ) or die(DB\dbQueryError());
                break;
            default:
                $res = DB\dbQuery(
                    'SELECT max(`order`)
                    FROM tags
                    WHERE pid = $1',
                    $params->toId
                ) or die(DB\dbQueryError());

                if ($r = $res->fetch_row()) {
                    $order = $r[0]+1;
                }
                $res->close();
        }
        DB\dbQuery(
            'UPDATE tags
            SET pid = $2
                    , `order` = $3
            WHERE id = $1',
            array(
                $params->id
                ,$params->toId
                ,$order
            )
        ) or die(DB\dbQueryError());

        DB\dbQuery(
            'UPDATE tags
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

    public function tagsSortChilds($params)
    {
        $res = DB\dbQuery(
            'SELECT id
            FROM tags
            WHERE pid = $1
            ORDER BY l'.USER_LANGUAGE_INDEX.(($params->direction == 'desc') ? ' desc' : ''),
            $params->id
        ) or die(DB\dbQueryError());

        $i = 1;
        while ($r = $res->fetch_row()) {
            DB\dbQuery(
                'UPDATE tags
                SET `order` = $1
                WHERE id = $2',
                array(
                    $i
                    ,$r[0]
                )
            ) or die(DB\dbQueryError());
            $i++;
        }
        $res->close();

        return array('success' => true);
    }

    public function tagsDeleteElement($id) //tag or folder
    {
        $res = DB\dbQuery(
            'SELECT pid
                 , `order`
            FROM tags
            WHERE id = $1',
            $id
        ) or die(DB\dbQueryError());

        $pid = 0;

        if ($r = $res->fetch_row()) {
            $pid = $r[0];
            try {
                DB\dbQuery('delete from tags where id = $1', $id) or die(DB\dbQueryError());

            } catch (\Exception $e) {
                return array('success' => false, 'msg' => 'Cannot delete selected tag, it is used in the system.');
            }
            DB\dbQuery(
                'UPDATE tags
                SET `order` = `order` - 1
                WHERE pid = $1
                    AND `order` > $2',
                array(
                    $pid
                    ,$r[1]
                    )
            ) or die(DB\dbQueryError());
        }
        $res->close();

        return array('success' => true);
    }

    public function getCountries()
    {
        $rez = array();
        $res = DB\dbQuery(
            'SELECT id
                 , name
                 , phone_codes
            FROM casebox.country_phone_codes
            ORDER BY name'
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_row()) {
            $rez[] = $r;
        }

        return array('success' => true, 'data' => $rez);
    }
    public function getTimezones()
    {
        $rez = array();
        $res = DB\dbQuery('SELECT caption, gmt_offset FROM casebox.zone ORDER BY gmt_offset, caption') or die(DB\dbQueryError());
        while ($r = $res->fetch_row()) {
            $offsetHours = floor(abs($r[1])/3600);
            $offsetMinutes = round((abs($r[1]) - $offsetHours * 3600) / 60);
            if ($offsetMinutes == 60) {
                $offsetHours++;
                $offsetMinutes = 0;
            }
            $r[1] = ( ($r[1] < 0) ? '-': '+' )
                . ($offsetHours < 10 ? '0' : '') . $offsetHours
                . ':'
                . ($offsetMinutes < 10 ? '0' : '') . $offsetMinutes;
            $rez[] = $r;
        }

        return array('success' => true, 'data' => $rez);
    }
}
