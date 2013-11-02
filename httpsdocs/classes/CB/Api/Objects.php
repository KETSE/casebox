<?php
namespace CB\Api;

use CB\DB as DB;

/**
 * Objects processing api class
 *
 *
 */

class Objects
{
    /**
     * create an object
     * @param  array $p object properties (see save method description)
     * @return json  responce
     */
    public function create($p)
    {
        $p['id'] = null;

        return $this->save($p);
    }

    /**
     * save an object
     * @param array $p{ object properties
     *       @type int $id id of the object to be updated or null for crating a new object
     *       @type int $pid parent id
     *       @type int $template_id
     *       @type int $oid owner id
     *       @type array $data {
     *             <field_id | field_name> => <scalar value> | array( //single field
     *                 'value' => 'field value'
     *                 ,'info' => 'field info'
     *                 ,'childs' => array(
     *                     <field_id | field_name> =>
     *                     ...
     *                 )
     *             )
     *             ,<field_id | field_name> => <scalar value> | array( // multiplied field
     *                 <scalar value> | array(
     *                     'value' => 'field value'
     *                     ,'info' => 'field info'
     *                     ,'childs' => array(
     *
     *                     )
     *                 )
     *                 ,<scalar value> | array(
     *                     'value' => 'field value'
     *                     ,'info' => 'field info'
     *                     ,'childs' => array(
     *
     *                     )
     *                 )
     *             )
     *       }
     * }
     * @return json responce
     */
    public function save($p)
    {
        /*check params validity */
        $params_validation = $this->validateInputParamsForCreate($p);
        if ($params_validation !== true) {
            throw new \Exception("Params validation failed: ".$params_validation, 1);

        }
        /* end of check params validity */

        $_SESSION['user'] = array('id' => $p['oid']);

        $data = array();

        $data['id'] = empty($p['id']) ? null : $p['id'];
        $data['pid'] = $p['pid'];
        $data['template_id'] = $p['template_id'];
        $data['oid'] = $p['oid'];

        //define template_id for internal use in this class
        $this->template_id = $p['template_id'];

        /* transforming grid data */
        if (empty($p['data'])) {
            if (!empty($p['tmplData'])) {
                $p['data'] = &$p['tmplData'];
            } else {
                $p['data'] = array();
            }
        }
        $data['gridData'] = $this->transformGridData($p['data']);

        $data = json_encode($data);

        $objects = new \CB\Objects();
        $rez = $objects->save(array('data' => $data));

        return $rez;
    }

    /**
     * transform grid data
     * @param  $data
     * @return array
     */
    private function transformGridData(&$data)
    {
        $this->duplicationCounter = 0;
        $this->gridData = array(
            'values' => array()
            // ,'duplicateFields' => array()
        );
        $this->transformFields($data);

        return $this->gridData;
    }

    /**
     * iterate fields array and transform them to needed structure for saving
     * @param  array   $fieldsArray
     * @param  integer $duplication_id
     * @return void
     */
    private function transformFields(&$fieldsArray, $duplication_id = 0)
    {
        foreach ($fieldsArray as $field_id => $valuesArray) {
            if (!is_numeric($field_id)) {
                $field_id = $this->fieldNameToId($field_id);
            }

            if (is_scalar($valuesArray)) {
                $valuesArray = array(
                    'value' => $valuesArray
                    ,'info' => null
                );
            }

            // transform field with direct value to a generic form (array of values)
            if (isset($valuesArray['value'])) {
                $valuesArray = array($valuesArray);
            }

            foreach ($valuesArray as $i => $fieldData) {
                if ($i > 0) {
                    $duplication_id++;
                    $this->gridData['duplicateFields'][$field_id]['d'.$duplication_id] =
                        ($duplication_id == 1) ? 0 : 'd'.($duplication_id-1);
                }
                $this->transformField($field_id, $fieldData, $duplication_id);
            }
        }

    }

    /**
     * transform field data to suitable structure for Object.save method
     * @param  int  $id
     * @param       $data
     * @param  int  $duplication_id
     * @return void
     */
    private function transformField($id, $data, $duplication_id)
    {
        if (is_scalar($data)) {
            $data = array(
                'value' => $data
                ,'info' => null
            );
        }
        $field_name = 'f'.$id.'_'.(empty($duplication_id) ? 0 : 'd'.$duplication_id);

        $this->gridData['values'][$field_name] = array(
            'value' => $data['value']
            ,'info' => @$data['info']
        );
        if (!empty($data['childs'])) {
            $this->transformFields($data['childs'], $duplication_id);
        }
    }

    /**
     * get fied id for a specified field name
     * @param  varchar $field_name name of the field
     * @return int     field id
     */
    private function fieldNameToId($field_name)
    {
        if (!isset($this->template_field_names[$field_name])) {
            $res = DB\dbQuery(
                'SELECT id
                FROM templates_structure
                WHERE template_id = $1
                    AND name = $2',
                array(
                    $this->template_id
                    ,$field_name
                )
            ) or die(DB\dbQueryError());

            if ($r = $res->fetch_assoc()) {
                $this->template_field_names[$field_name] = $r['id'];
            } else {
                throw new \Exception("template field not found: ".$fieldNameToId, 1);
            }
            $res->close();
        }

        return $this->template_field_names[$field_name];
    }

    /**
     * validate input params for create method
     * @param  array        $p object properties
     * @return varchar|true Return error message or boolean true
     */
    private function validateInputParamsForCreate(&$p)
    {
        if (empty($p['template_id']) && !empty($p['tmplId'])) {
            $p['template_id'] = $p['tmplId'];
        }

        if (!isset($p['template_id'])) {
            return 'template_id not specified';
        }

        if (!is_numeric($p['template_id'])) {
            return 'template_id not valid';
        }

        if (!isset($p['pid'])) {
            return 'pid not specified';
        }

        if (!is_numeric($p['pid'])) {
            return 'pid not valid';
        }

        if (!isset($p['oid'])) {
            if (!isset($p['owner'])) {
                return 'owner not specified';
            }

            $sql = 'SELECT id
                FROM users_groups
                WHERE `type` = 2
                    and id = $1';
            if (!is_numeric($p['owner'])) {
                $sql = 'SELECT id
                    FROM users_groups
                    WHERE `type` = 2
                        and name = $1';
            }

            $res = DB\dbQuery($sql, $p['owner']) or die(DB\dbQueryError());
            if ($r = $res->fetch_assoc()) {
                $p['oid'] = $r['id'];
            }
            $res->close();
        }

        if (!is_numeric($p['oid'])) {
            return 'invalid owner specified';
        }

        return true;
    }
}
