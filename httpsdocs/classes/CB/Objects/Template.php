<?php
namespace CB\Objects;

use CB\DB as DB;
use CB\Util as Util;
use CB\L as L;

/**
 * Template class
 */
class Template extends Object
{

    /**
     * available table fields in templates table
     * @var array
     */
    private $tableFields =  array(
        'id'
        ,'pid'
        ,'type'
        ,'name'
        ,'l1'
        ,'l2'
        ,'l3'
        ,'l4'
        ,'order'
        ,'visible'
        ,'iconCls'
        ,'default_field'
        ,'cfg'
        ,'title_template'
        ,'info_template'
    );

    /**
     * internal function used by create method for creating custom data
     * @return void
     */
    protected function createCustomData()
    {
        parent::createCustomData();

        $p = &$this->data;

        $saveFields = array();
        $saveValues = array();
        $params = array();
        $i = 1;
        foreach ($this->tableFields as $fieldName) {
            $field = null;
            if (!empty($this->template)) {
                $field = $this->template->getField($fieldName);
            }
            if (isset($p[$fieldName])) {
                $value = $p[$fieldName];
                $value = (is_scalar($value) || is_null($value))
                    ? $value
                    : json_encode($value, JSON_UNESCAPED_UNICODE);

                $saveFields[] = $fieldName;
                $saveValues[] = $value;
                $params[] = $i;
                $i++;
            } elseif (!empty($field)) {
                $value = $this->getFieldValue($fieldName);
                $value = (is_scalar($value) || is_null($value))
                    ? $value
                    : json_encode($value, JSON_UNESCAPED_UNICODE);

                $saveFields[] = $fieldName;
                $saveValues[] = $value;
                $params[] = $i;
                $i++;
            }
        }
        if (!empty($saveFields)) {
            $params = '$'.implode(',$', $params);
            DB\dbQuery(
                'INSERT INTO templates
                (`'.implode('`,`', $saveFields).'`)
                VALUES('.$params.')',
                $saveValues
            ) or die(DB\dbQueryError());
        }
        $this->saveFields();
    }

    /**
     * load template custom data
     */
    protected function loadCustomData()
    {
        parent::loadCustomData();

        $res = DB\dbQuery(
            'SELECT id
                ,is_folder
                ,`type`
                ,name
                ,l'.\CB\USER_LANGUAGE_INDEX.' `title`
                ,l1
                ,l2
                ,l3
                ,l4
                ,`order`
                ,`visible`
                ,iconCls
                ,default_field
                ,cfg
                ,title_template
                ,info_template
            FROM templates
            WHERE is_folder = 0 AND id = $1',
            $this->id
        ) or die(DB\dbQueryError());

        if ($r = $res->fetch_assoc()) {
            $r['cfg'] = Util\toJSONArray($r['cfg']);
            $this->data = array_merge($this->data, $r);
        } else {
            throw new \Exception("Template load error: no template found with id = ".$this->id);
        }
        $res->close();

        /* loading template fields */
        $this->data['fields'] = array();

        $res = DB\dbQuery(
            'SELECT
                id
                ,pid
                ,level
                ,name
                ,l'.\CB\USER_LANGUAGE_INDEX.' `title`
                ,l1
                ,l2
                ,l3
                ,l4
                ,`type`
                ,`order`
                ,cfg
                ,solr_column_name
            FROM templates_structure
            WHERE template_id = $1
            ORDER BY `order`',
            $this->id
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $r['cfg'] = Util\toJSONArray($r['cfg']);
            $this->data['fields'][$r['id']] = $r;
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

        $saveFields = array();
        $saveValues = array($this->id);
        $params = array();
        $i = 2;
        foreach ($this->tableFields as $fieldName) {
            $field = null;
            if (!empty($this->template)) {
                $field = $this->template->getField($fieldName);
            }
            if (isset($p[$fieldName]) && ($fieldName !== 'id')) {
                $value = $p[$fieldName];
                $value = (is_scalar($value) || is_null($value))
                    ? $value
                    : json_encode($value, JSON_UNESCAPED_UNICODE);

                $saveFields[] = $fieldName;
                $saveValues[] = $value;
                $params[] = "`$fieldName` = \$$i";
                $i++;
            } elseif (!empty($field)) {
                $value = $this->getFieldValue($fieldName);
                $value = (is_scalar($value) || is_null($value))
                    ? $value
                    : json_encode($value, JSON_UNESCAPED_UNICODE);

                $saveFields[] = $fieldName;
                $saveValues[] = $value;
                $params[] = "`$fieldName` = \$$i";
                $i++;
            }
        }
        if (!empty($saveFields)) {
            DB\dbQuery(
                'UPDATE templates
                SET '.implode(',', $params).'
                WHERE id = $1',
                $saveValues
            ) or die(DB\dbQueryError());
        }
        $this->saveFields();
    }

    /**
     * save fields property from this->data
     * @return void
     */
    protected function saveFields()
    {
        if (empty($this->data['fields'])) {
            return;
        }

        $tableFields = array(
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

        $keepFieldIds = array();
        foreach ($this->data['fields'] as $field) {
            $saveFields = array('template_id');
            $saveValues = array($this->id);
            $insertParams = array('$1');
            $updateParams = array();
            $i = 2;
            foreach ($tableFields as $fieldName) {
                $value = null;
                if (isset($field[$fieldName])) {
                    $value = (is_scalar($field[$fieldName]) || is_null($field[$fieldName]))
                        ? $field[$fieldName]
                        : json_encode($field[$fieldName], JSON_UNESCAPED_UNICODE);
                    $saveFields[] = $fieldName;
                    $saveValues[] = $value;
                    $insertParams[] = "\$$i";
                    if ($fieldName !== 'name') {
                        $updateParams[] = "`$fieldName` = \$$i";
                    }
                    $i++;
                }
            }
            if (!empty($saveFields)) {
                DB\dbQuery(
                    'INSERT INTO templates_structure
                        (`'.implode('`,`', $saveFields).'`)
                    VALUES ('.implode(',', $insertParams).')
                    ON DUPLICATE KEY UPDATE
                        '.implode(',', $updateParams),
                    $saveValues
                ) or die(DB\dbQueryError());
                $keepFieldIds[] = DB\dbLastInsertId();
            }
        }
    }

    /**
     * get field properties
     * @param  int | varchar $field field id or name
     * @return array
     */
    public function getField($field)
    {
        if (isset($this->data['fields'][$field])) {
            return $this->data['fields'][$field];
        }
        foreach ($this->data['fields'] as $fieldId => $fv) {
            if ($fv['name'] == $field) {
                return $fv;
            }
        }

        return null;
    }

    /**
     * formats a value for display according to it's field definition
     * @param  array | int $field array of field properties or field id
     * @param  variant     $value field value to be formated
     * @param  boolean     $html  default true - format for html, otherwise format for text display
     * @return varchar     formated value
     */
    public static function formatValueForDisplay($field, $value, $html = true)
    {
        if (is_numeric($field)) {
            $field = $this->data->fields[$field];
        }
        if (is_array($value) && isset($value['value'])) {
            $value = $value['value'];
        }

        switch ($field['type']) {
            case 'boolean':
            case 'checkbox':
                $value = empty($value) ? L\no : L\yes;
                break;

            case '_sex':
                switch ($value) {
                    case 'm':
                        $value = L\male;
                        break;
                    case 'f':
                        $value = L\female;
                        break;
                    default:
                        $value = '';
                }
                break;

            case '_language':
                @$value = $GLOBALS['language_settings'][$GLOBALS['languages'][$value -1]][0];
                break;

            case 'combo':
            case 'popuplist':
                if (!empty($value)) {
                    $value = Util\getThesauriTitles($value);
                }
                break;

            case '_case':
                if (empty($value)) {
                    $value = '';
                    break;
                }
                $a = Util\toNumericArray($value);
                if (empty($a)) {
                    $value = '';
                    break;
                }
                $res = DB\dbQuery(
                    'SELECT name FROM tree WHERE id IN ('.implode(',', $a).') ORDER BY 1'
                ) or die(DB\dbQueryError());
                $value = array();
                while ($r = $res->fetch_assoc()) {
                    $value[] = $r['name'];
                }
                $res->close();
                if (sizeof($value) == 1) {
                    $value = $value[0];
                }
                break;

            case '_case_object':
                if (empty($value)) {
                    $value = '';
                    break;
                }
                $a = Util\toNumericArray($value);
                if (empty($a)) {
                    $value = '';
                    break;
                }
                $res = DB\dbQuery(
                    'SELECT name FROM tree WHERE id IN ('.implode(',', $a).') ORDER BY 1'
                ) or die(DB\dbQueryError());
                $value = array();
                while ($r = $res->fetch_assoc()) {
                    $value[] = $r['name'];
                }
                $res->close();
                if (sizeof($value) == 1) {
                    $value = $value[0];
                }
                break;

            case '_objects':
                if (empty($value)) {
                    $value = '';
                    break;
                }
                $a = Util\toNumericArray($value);
                if (empty($a)) {
                    $value = '';
                    break;
                }
                $ids = implode(',', $a);

                switch (@$field['cfg']['source']) {
                    case 'tree':
                    case 'related':
                    case 'field':
                        $value = 'tree';
                        $sql = 'SELECT t.id
                                ,t.name
                                ,t.template_id
                                ,t.`type`
                                ,t.`subtype`
                                ,t.cfg
                                ,ti.pids `path`
                            FROM tree t
                            JOIN tree_info ti ON t.id = ti.id
                            WHERE t.id IN ('.$ids.')';
                        break;
                    case 'users':
                    case 'groups':
                    case 'usersgroups':
                        $value = 'users_groups';
                        $sql = 'SELECT id
                                ,name
                                ,trim( CONCAT(coalesce(first_name, \'\'), \' \', coalesce(last_name, \'\')) ) `title`
                                ,CASE WHEN (`type` = 1) THEN \'icon-users\' ELSE CONCAT(\'icon-user-\', coalesce(sex, \'\') ) END `iconCls`
                            FROM users_groups
                            WHERE id IN ('.$ids.')';
                        break;
                    default:
                        $value = 'thesauri';
                        $sql = 'SELECT id
                                ,l'.\CB\USER_LANGUAGE_INDEX.' `title`
                                ,iconCls
                            FROM tags
                            WHERE id IN ('.$ids.')
                            ORDER BY `order`';
                        break;
                }

                $res = DB\dbQuery($sql) or die(DB\dbQueryError());
                $value = array();
                while ($r = $res->fetch_assoc()) {
                    @$label = Util\coalesce($r['title'], $r['name']);
                    if (!empty($r['path'])) {
                        $path = explode(',', $r['path']);
                        array_pop($path);
                        $r['path'] = implode('/', $path);
                        $label = $html
                            ? '<a class="locate click" path="'.$r['path'].'" nid="'.$r['id'].'">'.$label.'</a>'
                            : $label;
                    }

                    switch (@$field['cfg']['renderer']) {
                        case 'listGreenIcons':
                            $value[] =  $html
                                ? '<li class="icon-padding icon-element">'.$label.'</li>'
                                : $label;
                            break;
                        case 'listObjIcons':
                            $r['cfg'] = Util\toJSONArray(@$r['cfg']);

                            $icon = '';
                            switch (@$field['cfg']['source']) {
                                case 'tree':
                                case 'related':
                                case 'field':
                                    $icon = \CB\Browser::getIcon($r);
                                    break;
                                default:
                                    $icon = Util\coalesce($r['iconCls'], 'icon-none');
                                    break;
                            }

                            $value[] = $html
                                ? '<li class="icon-padding '.$icon.'">'.$label.'</li>'
                                : $label;
                            break;
                        default:
                            $value[] = $html
                                ? '<li>'.$label.'</li>'
                                : $label;
                    }
                }
                $res->close();
                $value = $html
                    ? '<ul class="clean">'.implode('', $value).'</ul>'
                    : implode(', ', $value);
                break;

            case 'date':
                $value = Util\formatMysqlDate($value);
                break;

            case 'datetime':
                $value = Util\formatMysqlTime($value);
                break;

            case 'html':
                //$value = trim(strip_tags($value));
                //$value = nl2br($value);
                break;
            case 'text':
                $value = nl2br(htmlspecialchars($value));
                break;
            default:
                if (is_array($value)) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                }
        }

        return $value;
    }
}
