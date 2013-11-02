<?php
namespace CB;

if (!Security::canManage()) {
    throw new \Exception(L\Access_denied);
}

class System
{
    public function tagsGetChildren($p)
    {
        $rez = array();
        $t = explode('/', $p['path']);
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
            WHERE pid'.( ($nodeId > 0) ? ' = $1' : ' IS NULL' ).'
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
        $res = DB\dbQuery(
            'SELECT f_get_tag_pids($1) `pids_path`',
            $id
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $rez['path'] = $r['pids_path'];
        }

        return $rez;
    }

    public function tagsSaveElement($p)
    {
        $rez = array(
            'id' => empty($p['id']) ? null: $p['id']
            ,'pid' => (empty($p['pid']) || (!is_numeric($p['pid']))) ? null: $p['pid']
            ,'type' => empty($p['type']) ? 0: 1
            ,'hidden' => empty($p['hidden']) ? null: 1
            ,'iconCls' => (empty($p['iconCls']) || ($p['iconCls'] == 'icon-tag-small')) ? null : $p['iconCls']
        );
        $values_string = '$1, $2, $3, $4, $5';
        $on_duplicate =  'hidden = $4, iconCls = $5';

        if (empty($p['id'])) {
            $rez['order'] = 0;
            $res = DB\dbQuery(
                'SELECT max(`order`) `order`
                FROM tags
                WHERE pid'.(
                    (empty($p['pid']) || (!is_numeric($p['pid'])))
                        ? ' is null '
                        : ' = '.$p['pid']
                )
            ) or die(DB\dbQueryError());
            if ($r = $res->fetch_assoc()) {
                $rez['order'] = intval($r['order']) + 1;
            }
            $res->close();
            $values_string .= ', $6';
            $on_duplicate .= ',`order` = $6';
        }

        Util\getLanguagesParams($p, $rez, $values_string, $on_duplicate);
        DB\dbQuery(
            'INSERT into tags (`'.implode('`,`', array_keys($rez)).'`)
            VALUES ('.$values_string.')
            ON DUPLICATE KEY UPDATE '.$on_duplicate,
            array_values($rez)
        ) or die(DB\dbQueryError());

        if (!is_numeric(@$p['id'])) {
            $rez['id'] = DB\dbLastInsertId();
        }
        $rez['loaded'] = true;

        return array( 'success' => true, 'data' => $rez);
    }

    public function tagsMoveElement($p)
    {
        /* get old pid */
        $res = DB\dbQuery(
            'SELECT pid
                 , `order`
            FROM tags
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
        $p['toId'] = is_numeric($p['toId']) ? $p['toId'] : null;
        $order = 1;
        switch ($p['point']) {
            case 'above':
                /* get relative node order and pid */
                $res = DB\dbQuery(
                    'SELECT pid
                         , `order`
                    FROM tags
                    WHERE id = $1',
                    $p['toId']
                ) or die(DB\dbQueryError());

                if ($r = $res->fetch_assoc()) {
                    $p['toId'] = $r['pid'];
                    $order = $r['order'];
                }
                $res->close();
                DB\dbQuery(
                    'UPDATE tags
                    SET `order` = `order` + 1
                    WHERE pid = $1
                        AND `order` >= $2',
                    array(
                        $p['toId']
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
                    $p['toId']
                ) or die(DB\dbQueryError());

                if ($r = $res->fetch_assoc()) {
                    $p['toId'] = $r['pid'];
                    $order = $r['order']+1;
                }
                $res->close();
                DB\dbQuery(
                    'UPDATE tags
                    SET `order` = `order` + 1
                    WHERE pid = $1
                        AND `order` >= $2',
                    array(
                        $p['toId']
                        ,$order
                    )
                ) or die(DB\dbQueryError());
                break;
            default:
                $res = DB\dbQuery(
                    'SELECT max(`order`) `order`
                    FROM tags
                    WHERE pid = $1',
                    $p['toId']
                ) or die(DB\dbQueryError());

                if ($r = $res->fetch_assoc()) {
                    $order = $r['order']+1;
                }
                $res->close();
        }
        DB\dbQuery(
            'UPDATE tags
            SET pid = $2
                    , `order` = $3
            WHERE id = $1',
            array(
                $p['id']
                ,$p['toId']
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

    public function tagsSortChilds($p)
    {
        $res = DB\dbQuery(
            'SELECT id
            FROM tags
            WHERE pid = $1
            ORDER BY l'.USER_LANGUAGE_INDEX.(($p['direction'] == 'desc') ? ' desc' : ''),
            $p['id']
        ) or die(DB\dbQueryError());

        $i = 1;
        while ($r = $res->fetch_assoc()) {
            DB\dbQuery(
                'UPDATE tags
                SET `order` = $1
                WHERE id = $2',
                array(
                    $i
                    ,$r['id']
                )
            ) or die(DB\dbQueryError());
            $i++;
        }
        $res->close();

        return array('success' => true);
    }

    /**
     * delete a tag or folder from tags tree
     * @param  int  $id element id
     * @return json response
     */
    public function tagsDeleteElement($id)
    {
        $res = DB\dbQuery(
            'SELECT pid
                 , `order`
            FROM tags
            WHERE id = $1',
            $id
        ) or die(DB\dbQueryError());

        $pid = 0;

        if ($r = $res->fetch_assoc()) {
            $pid = $r['pid'];
            try {
                DB\dbQuery(
                    'DELETE FROM tags WHERE id = $1',
                    $id
                ) or die(DB\dbQueryError());

            } catch (\Exception $e) {
                return array(
                    'success' => false,
                    'msg' => 'Cannot delete selected tag, it is used in the system.'
                );
            }
            DB\dbQuery(
                'UPDATE tags
                SET `order` = `order` - 1
                WHERE pid = $1
                    AND `order` > $2',
                array(
                    $pid
                    ,$r['order']
                )
            ) or die(DB\dbQueryError());
        }
        $res->close();

        return array('success' => true);
    }

    /**
     * get countries list with their phone codes
     *
     * this function returns an array of records for arrayReader
     *     first column is id
     *     second is name
     *     third is phone code
     * @return json response
     */
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

        while ($r = $res->fetch_assoc()) {
            $rez[] = array_values($r);
        }

        return array('success' => true, 'data' => $rez);
    }

    /**
     * get defined timezones
     *
     * returns an array of records for arrayReader
     * record contains two fields: caption, gmt offset
     * @return json response
     */
    public function getTimezones()
    {
        $rez = array();
        $res = DB\dbQuery(
            'SELECT caption, gmt_offset
            FROM casebox.zone
            ORDER BY gmt_offset, caption'
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $offsetHours = floor(abs($r['gmt_offset'])/3600);
            $offsetMinutes = round((abs($r['gmt_offset']) - $offsetHours * 3600) / 60);
            if ($offsetMinutes == 60) {
                $offsetHours++;
                $offsetMinutes = 0;
            }
            $r['gmt_offset'] = ( ($r['gmt_offset'] < 0) ? '-': '+' )
                . ($offsetHours < 10 ? '0' : '') . $offsetHours
                . ':'
                . ($offsetMinutes < 10 ? '0' : '') . $offsetMinutes;
            $rez[] = array_values($r);
        }

        return array('success' => true, 'data' => $rez);
    }
}
