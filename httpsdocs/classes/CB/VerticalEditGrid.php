<?php
namespace CB;

class VerticalEditGrid
{
    // users
    public static function getData($objectName, &$data)
    {
        if (empty($data['id'])) {
            return;
        }
        $id_field = VerticalEditGrid::getObjectIdField($objectName);
        /* get grid values */
        $gv = array();
        $res = DB\dbQuery('SELECT concat(\'f\', field_id, \'_\', duplicate_id) field, id, `value`, info FROM '.$objectName.'_data WHERE '.$id_field.' = $1', $data['id']) or die(DB\dbQueryError());
        while ($r = $res->fetch_assoc()) {
            $field = $r['field'];
            unset($r['field']);
            $gv[$field] = $r;
        }
        $res->close();
        $data['gridData']['values'] = $gv;
        /* end of get grid values */
        /* get duplicated field list */
        $res = DB\dbQuery('select id, pid, field_id from '.$objectName.'_duplicates where '.$id_field.' = $1 order by id', $data['id']) or die(DB\dbQueryError());
        while ($r = $res->fetch_row()) {
            $data['gridData']['duplicateFields'][$r[2]][$r[0]] = $r[1];
        }
        $res->close();
        /* end of get duplicated field list */
    }

    public static function saveData($objectName, &$data)
    {
        $id_field = VerticalEditGrid::getObjectIdField($objectName);
        /* save object duplicates from grid */
        $duplicate_ids = array(0 => 0);
        if (isset($data->gridData->duplicateFields)) {
            $sql = 'INSERT INTO  '.$objectName.'_duplicates (pid, '.$id_field.', field_id) VALUES ($1, $2, $3)';
            foreach ($data->gridData->duplicateFields as $field_id => $fv) {
                $i = 0;
                foreach ($fv as $duplicate_id => $duplicate_pid) {
                    if (!is_numeric($duplicate_id)) {
                        DB\dbQuery($sql, array($duplicate_ids[$duplicate_pid], $data->id, $field_id)) or die(DB\dbQueryError());
                        $duplicate_ids[$duplicate_id] = DB\dbLastInsertId();
                    } else {
                        $duplicate_ids[$duplicate_id] = $duplicate_id;
                    }
                    $fields[$field_id]['duplicates'][$i]['id'] = $duplicate_id;
                    $i++;
                }
            }
        }
        DB\dbQuery('delete from  '.$objectName.'_duplicates where '.$id_field.' = $1 and (id not in ('.implode(',', array_values($duplicate_ids)).') )', $data->id) or die(DB\dbQueryError());
        /* end of save object duplicates from grid */

        /* save object values from grid */
        $sql = 'INSERT INTO  '.$objectName.'_data ('.$id_field.', field_id, duplicate_id, `value`, info) VALUES ($1, $2, $3, $4, $5)
                ON DUPLICATE KEY UPDATE id = last_insert_id(id), '.$id_field.' = $1, field_id = $2, duplicate_id = $3, `value` = $4, info = $5';
        $ids = array(0);
        if (isset($data->gridData)) {
            foreach ($data->gridData->values as $f => $fv) { //$c => $cv
                if (!isset($fv->value)) {
                    $fv->value = null;
                }
                $f = explode('_', $f);
                $field_id = substr($f[0], 1);

                $duplicate_id = intval($duplicate_ids[$f[1]]);
                $duplicate_index = 0;
                if (isset($fields[$field_id])) {
                    foreach ($fields[$field_id]['duplicates'] as $k => $v) {
                        if (is_array($v['id'])) {
                            if ($v['id'] == $duplicate_id) {
                                $fields[$field_id]['duplicates'][$k]['index'] = $duplicate_index;
                            } else {
                                $duplicate_index++;
                            }
                        }
                    }
                }

                @$params = array($data->id, $field_id, $duplicate_id, $fv->value, $fv->info);
                DB\dbQuery($sql, $params) or die(DB\dbQueryError());
                array_push($ids, DB\dbLastInsertId());
            }
        }
        DB\dbQuery('delete from  '.$objectName.'_data where '.$id_field.' = $1 and (id not in ('.implode(',', $ids).') )', $data->id) or die(DB\dbQueryError());
    }
    public static function addFormData($objectName, &$data)
    {
        $id_field = VerticalEditGrid::getObjectIdField($objectName);

        $values = array();
        $i = 1;
        $res = DB\dbQuery('select id, name from templates_structure where template_id = $1', $data->template_id) or die(DB\dbQueryError());
        while ($r = $res->fetch_assoc()) {
            if (isset($data->{$r['name']})) {
                $values['('.$data->id.', 0, '.$r['id'].', $'.$i++.')'] = $data->{$r['name']};
            }
        }
        $res->close();
        if (!empty($values)) {
            DB\dbQuery(
                'INSERT INTO '.$objectName.'_DATA ('.$id_field.'
                                , duplicate_id
                                , field_id
                                , value)
                VALUES '.implode(',', array_keys($values)),
                array_values($values)
            ) or die(DB\dbQueryError());
        }

    }

    private static function getObjectIdField($objectName)
    {
        switch ($objectName) {
            case 'users_groups':
                return 'user_id';
                break;
            default:
                return substr($objectName, 0, strlen($objectName) -1).'_id';
        }
    }
}
