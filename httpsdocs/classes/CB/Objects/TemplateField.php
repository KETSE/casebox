<?php
namespace CB\Objects;

use CB\DB as DB;
use CB\Util as Util;
use CB\L as L;

/**
 * Template class
 */
class TemplateField extends Object
{

    /**
     * available table fields in templates table
     * @var array
     */
    private $tableFields =  array(
        'id'
        ,'pid'
        //,'template_id'
        ,'name'
        ,'l1'
        ,'l2'
        ,'l3'
        ,'l4'
        ,'type'
        ,'order'
        ,'cfg'
        ,'solr_column_name'
    );

    /**
     * internal function used by create method for creating custom data
     * @return void
     */
    protected function createCustomData()
    {
        parent::createCustomData();

        $p = &$this->data;

        $saveFields = array('template_id');
        $saveValues = array($this->detectParentTemplate());

        $params = array('1');
        $i = 2;

        $dataParams = $this->getParamsFromData();

        foreach ($dataParams as $k => $v) {
            $saveFields[] = $k;
            $saveValues[] = $v;
            $params[] = $i;
            $i++;
        }

        if (!empty($saveFields)) {
            $params = '$'.implode(',$', $params);

            $sql = 'INSERT INTO templates_structure
                (`'.implode('`,`', $saveFields).'`)
                VALUES('.$params.')';

            DB\dbQuery($sql, $saveValues) or die(DB\dbQueryError());
        }
    }

    /**
     * load template custom data
     */
    protected function loadCustomData()
    {
        parent::loadCustomData();

        $res = DB\dbQuery(
            'SELECT
                l' . \CB\Config::get('user_language_index').' `title`
                ,`'.implode('`,`', $this->tableFields).'`
            FROM templates_structure
            WHERE id = $1',
            $this->id
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $r['cfg'] = Util\toJSONArray($r['cfg']);
            $this->data = array_merge($this->data, $r);
        } else {
            \CB\debug("Template field load error: no field found with id = ".$this->id);
            // throw new \Exception("Template field load error: no field found with id = ".$this->id);
        }
        $res->close();
    }

    /**
     * update objects custom data
     * @return boolean
     */
    protected function updateCustomData()
    {
        parent::updateCustomData();

        /* saving template data to templates and templates_structure tables */
        $p = &$this->data;

        $saveFields = array('template_id');
        $saveValues = array($this->id, $this->detectParentTemplate());
        $params = array('template_id = $2');
        $i = 3;

        $dataParams = $this->getParamsFromData();
        \CB\debug(\CB\Config::get('languages'), $dataParams);
        foreach ($dataParams as $k => $v) {
            $saveFields[] = $k;
            $saveValues[] = $v;
            $params[] = "`$k` = \$$i";
            $i++;
        }

        if (!empty($saveFields)) {
            DB\dbQuery(
                'UPDATE templates_structure
                SET '.implode(',', $params).'
                WHERE id = $1',
                $saveValues
            ) or die(DB\dbQueryError());
        }
    }

    /**
     * get associative params array according to tableFields from object data
     * @return array
     */
    protected function getParamsFromData()
    {
        $rez = array();
        $p = &$this->data;

        foreach ($this->tableFields as $fieldName) {
            $field = null;
            $addField = false;
            $value = null;

            if (!empty($this->template)) {
                $field = $this->template->getField($fieldName);
            }

            if (empty($field) && $fieldName == 'name') {
                //update name to _title field if present in data (actually it should be present)
                $value = @$this->getFieldValue('_title', 0)['value'];
                if (empty($value)) {
                    $value = $p['name'];
                }
                $addField = true;

            } elseif (!empty($field)) {
                //template field exists so it should be in data
                $addField = true;
                $value = @$this->getFieldValue($fieldName, 0)['value'];

            } elseif (isset($p[$fieldName]) && ($p[$fieldName] !== 'id')) {
                $addField = true;
                $value = $p[$fieldName];
            }

            /** if empty and is language field - check if language field is defined as language abreviation */
            // this if should be removed after complete migration to language abreviation titles
            if (!$addField && in_array($fieldName, array('l1', 'l2', 'l3', 'l4'))) {
                $addField = true;
                $lang = @\CB\Config::get('languages')[$fieldName[1]-1];
                $value = empty($lang)
                    ? null
                    : @$this->getFieldValue($lang, 0)['value'];
            }

            if ($addField) {
                $value = (is_scalar($value) || is_null($value))
                    ? $value
                    : json_encode($value, JSON_UNESCAPED_UNICODE);

                $rez[$fieldName] = $value;
            }
        }

        return $rez;
    }

    protected function detectParentTemplate($targetPid = false)
    {
        $rez = ($targetPid === false)
            ? $this->data['pid']
            : $targetPid;

        if (empty($rez)) {
            return null;
        }

        $res = DB\dbQuery(
            'SELECT `template_id`
            FROM templates_structure
            WHERE id = $1',
            $rez
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $rez = $r['template_id'];
        }

        return $rez;
    }

    protected function copyCustomDataTo($targetId)
    {
        // copy data from templates structure table
        DB\dbQuery(
            'INSERT INTO `templates_structure`
                (`id`
                ,`pid`
                ,`template_id`
                ,`name`
                ,`l1`
                ,`l2`
                ,`l3`
                ,`l4`
                ,`type`
                ,`order`
                ,`cfg`
                ,`solr_column_name`
                )
            SELECT
                t.id
                ,t.pid
                ,$3
                ,ts.name
                ,ts.l1
                ,ts.l2
                ,ts.l3
                ,ts.l4
                ,ts.type
                ,ts.order
                ,ts.cfg
                ,ts.solr_column_name
            FROM `tree` t
                ,templates_structure ts
            WHERE t.id = $2
                AND ts.id = $1
            ON DUPLICATE KEY UPDATE
                pid = t.pid
                ,template_id = $3
                ,name = ts.name
                ,l1 = ts.l1
                ,l2 = ts.l2
                ,l3 = ts.l3
                ,l4 = ts.l4
                ,`type` = ts.type
                ,`order` = ts.order
                ,`cfg` = ts.cfg
                ,solr_column_name = ts.solr_column_name',
            array(
                $this->id
                ,$targetId
                ,$this->detectParentTemplate($this->data['pid'])
            )
        ) or die(DB\dbQueryError());
    }

    protected function moveCustomDataTo($targetId)
    {
        DB\dbQuery(
            'UPDATE templates_structure
            SET pid = $2
            WHERE id = $1',
            array(
                $this->id
                ,$targetId
            )
        ) or die(DB\dbQueryError());
    }
}
