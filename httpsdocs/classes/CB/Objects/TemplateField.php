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

        //update name to _title field if presend in data (actually it should be present)
        $title = $this->getFieldValue('_title');
        if (!empty($title)) {
            $p['name'] = $title;
        }

        $saveFields = array('template_id');
        $saveValues = array($this->detectParentTemplate());
        $params = array('1');
        $i = 2;
        foreach ($this->tableFields as $fieldName) {
            $field = null;
            if (!empty($this->template)) {
                $field = $this->template->getField($fieldName);
            }
            if (isset($p[$fieldName])) {
                $value = $p[$fieldName];
                $value = (is_scalar($value) || is_null($value))
                    ? $value
                    : json_encode($value);

                $saveFields[] = $fieldName;
                $saveValues[] = $p[$fieldName];
                $params[] = $i;
                $i++;
            } elseif (!empty($field)) {
                $value = $this->getFieldValue($fieldName);
                $value = (is_scalar($value) || is_null($value))
                    ? $value
                    : json_encode($value);

                $saveFields[] = $fieldName;
                $saveValues[] = $value;
                $params[] = $i;
                $i++;
            }
        }
        if (!empty($saveFields)) {
            $params = '$'.implode(',$', $params);
            DB\dbQuery(
                'INSERT INTO templates_structure
                (`'.implode('`,`', $saveFields).'`)
                VALUES('.$params.')',
                $saveValues
            ) or die(DB\dbQueryError());
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
                l'.\CB\USER_LANGUAGE_INDEX.' `title`
                ,`'.implode('`,`', $this->tableFields).'`
            FROM templates_structure
            WHERE id = $1',
            $this->id
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $r['cfg'] = Util\toJSONArray($r['cfg']);
            $this->data = array_merge($this->data, $r);
        } else {
            throw new \Exception("Template field load error: no field found with id = ".$this->id);
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
        foreach ($this->tableFields as $fieldName) {
            $field = null;
            if (!empty($this->template)) {
                $field = $this->template->getField($fieldName);
            }

            if (isset($p[$fieldName]) && ($p[$fieldName] !== 'id')) {
                $value = (is_scalar($p[$fieldName]) || is_null($p[$fieldName]))
                    ? $p[$fieldName]
                    : json_encode($p[$fieldName]);

                $saveFields[] = $fieldName;
                $saveValues[] = $value;
                $params[] = "`$fieldName` = \$$i";
                $i++;
            } elseif (!empty($field)) {
                $value = $this->getFieldValue($fieldName);
                $value = (is_scalar($value) || is_null($value))
                    ? $value
                    : json_encode($value);

                $saveFields[] = $fieldName;
                $saveValues[] = $value;
                $params[] = "`$fieldName` = \$$i";
                $i++;
            }
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

    protected function detectParentTemplate()
    {
        $rez = null;
        if (empty($this->data['pid'])) {
            return $rez;
        }

        $res = DB\dbQuery(
            'SELECT t.id, tt.type, COALESCE(ts.template_id, t.id) `template_id`
            FROM tree t
            JOIN templates tt
                ON t.template_id = tt.id
            LEFT JOIN templates_structure ts
                ON t.id = ts.id
            WHERE t.id = $1',
            $this->data['pid']
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            if ($r['type'] == 'template') {
                $rez = $r['id'];
            } elseif ($r['type'] = 'field') {
                $rez = $r['template_id'];
            }
        }

        return $rez;
    }
}
