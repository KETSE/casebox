<?php
namespace CB\Api;

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
        $data['data'] = $p['data'];

        $objects = new \CB\Objects();
        $rez = $objects->save(array('data' => $data));

        return $rez;
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
            $p['oid'] = \CB\User::exists($p['owner']);
        }

        if (!is_numeric($p['oid'])) {
            return 'invalid owner specified';
        }

        return true;
    }
}
