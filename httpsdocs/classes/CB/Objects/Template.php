<?php
namespace CB\Objects;

use CB\DB;
use CB\Util;
use CB\User;
use CB\L;
use CB\Search;
use CB\Cache;

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
     * table for quick accessing fields order (to avoid additional iterations)
     * @var array
     */
    private $fieldsOrder = array();

    private static $fieldTypeNames =  array(
        '_auto_title' => 'ftAutoTitle'
        ,'checkbox' => 'ftCheckbox'
        ,'combo' => 'ftCombo'
        ,'date' => 'ftDate'
        ,'datetime' => 'ftDatetime'
        ,'float' => 'ftFloat'
        ,'G' => 'ftGroup'
        ,'H' => 'ftHeader'
        ,'html' => 'ftHtml'
        ,'iconcombo' => 'ftIconcombo'
        ,'int' => 'ftInt'
        ,'_language' => 'ftLanguage'
        ,'memo' => 'ftMemo'
        ,'_objects' => 'ftObjects'
        ,'_sex' => 'ftSex'
        ,'_short_date_format' => 'ftShortDateFormat'
        ,'_fieldTypesCombo' => 'ftFieldTypesCombo'
        ,'_templateTypesCombo' => 'ftTemplateTypesCombo'
        ,'text' => 'ftText'
        ,'time' => 'ftTime'
        ,'timeunits' => 'ftTimeunits'
        ,'varchar' => 'ftVarchar'
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

        if (empty($p['visible'])) {
            $p['visible'] = 0;
        }

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
                $value = @$this->getFieldValue($fieldName, 0)['value'];

                // this if should be removed after complete migration to language abreviation titles
                if (empty($value) && in_array($fieldName, array('l1', 'l2', 'l3', 'l4'))) {
                    $lang = @\CB\Config::get('languages')[$fieldName[1]-1];
                    if (!empty($lang)) {
                        $value = @$this->getFieldValue($lang, 0)['value'];
                    }
                }

                $value = (is_scalar($value) || is_null($value))
                    ? $value
                    : json_encode($value, JSON_UNESCAPED_UNICODE);

                $saveFields[] = $fieldName;
                $saveValues[] = $value;
                $params[] = $i;
                $i++;
            } else {
                // this if should be removed after complete migration to language abreviation titles
                if (in_array($fieldName, array('l1', 'l2', 'l3', 'l4'))) {
                    $lang = @\CB\Config::get('languages')[$fieldName[1]-1];
                    if (!empty($lang)) {
                        $value = @$this->getFieldValue($lang, 0)['value'];

                        $saveFields[] = $fieldName;
                        $saveValues[] = $value;
                        $params[] = "$i";
                        $i++;
                    }
                }
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

        $userLanguageIndex = \CB\Config::get('user_language_index');

        $res = DB\dbQuery(
            'SELECT id
                ,is_folder
                ,`type`
                ,name
                ,l' . $userLanguageIndex . ' `title`
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
                ts.id
                ,ts.pid
                ,ts.name
                ,ts.l' . $userLanguageIndex . ' `title`
                ,ts.l1
                ,ts.l2
                ,ts.l3
                ,ts.l4
                ,ts.`type`
                ,ts.`level`
                ,ts.`order`
                ,ts.cfg
                ,ts.solr_column_name
            FROM templates_structure ts
                JOIN tree t on ts.id = t.id AND t.dstatus = 0
            WHERE ts.template_id = $1
            ORDER BY ts.`order`',
            $this->id
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $r['cfg'] = Util\toJSONArray($r['cfg']);
            $this->data['fields'][$r['id']] = $r;

            $this->fieldsOrder[$r['name']] = intval($r['order']);
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
            if (!empty($field)) {
                $value = @$this->getFieldValue($fieldName, 0)['value'];
                $value = (is_scalar($value) || is_null($value))
                    ? $value
                    : json_encode($value, JSON_UNESCAPED_UNICODE);

                $saveFields[] = $fieldName;
                $saveValues[] = $value;
                $params[] = "`$fieldName` = \$$i";
                $i++;
            } elseif (isset($p[$fieldName]) && ($fieldName !== 'id')) {
                $value = $p[$fieldName];
                $value = (is_scalar($value) || is_null($value))
                    ? $value
                    : json_encode($value, JSON_UNESCAPED_UNICODE);

                $saveFields[] = $fieldName;
                $saveValues[] = $value;
                $params[] = "`$fieldName` = \$$i";
                $i++;
            } else {
                // this if should be removed after complete migration to language abreviation titles
                if (in_array($fieldName, array('l1', 'l2', 'l3', 'l4'))) {
                    $lang = @\CB\Config::get('languages')[$fieldName[1]-1];
                    if (!empty($lang)) {
                        $value = @$this->getFieldValue($lang, 0)['value'];

                        $saveFields[] = $fieldName;
                        $saveValues[] = $value;
                        $params[] = "`$fieldName` = \$$i";
                        $i++;
                    }
                }
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

    protected function copyCustomDataTo($targetId)
    {
        DB\dbQuery(
            'INSERT INTO `templates`
                (id,
                pid,
                `is_folder`,
                `type`,
                `name`,
                `l1`,
                `l2`,
                `l3`,
                `l4`,
                `order`,
                `visible`,
                `iconCls`,
                `default_field`,
                `cfg`,
                `title_template`,
                `info_template`)
            SELECT
                $2,
                $3,
                `is_folder`,
                `type`,
                `name`,
                `l1`,
                `l2`,
                `l3`,
                `l4`,
                `order`,
                `visible`,
                `iconCls`,
                `default_field`,
                `cfg`,
                `title_template`,
                `info_template`
            FROM `templates`
            WHERE id = $1',
            array(
                $this->id
                ,$targetId
                ,$this->data['pid']
            )
        ) or die(DB\dbQueryError());
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
     * get field order
     * @param  varchar $fieldName
     * @return int
     */
    public function getFieldOrder($fieldName)
    {
        if (isset($this->fieldsOrder[$fieldName])) {
            return $this->fieldsOrder[$fieldName];
        }

        return 0;
    }

    /**
     * get header field properties
     * @param  int | varchar $field field id or name
     * @return array
     */
    public function getHeaderField($field)
    {
        $rez = null;
        $field = $this->getField($field);
        if (empty($field)) {
            return $rez;
        }
        $rez = $this->getField($field['pid']);
        if (!empty($rez) && ($rez['type'] == 'H')) {
            return $rez;
        }

        // get closest top header
        $rez = null;
        foreach ($this->data['fields'] as $fid => $fv) {
            if (($fv['id'] == $field['id'])) {
                return $rez;
            }
            if (($fv['pid'] == $field['pid']) && ($fv['type'] == 'H')) {
                $rez = $fv;
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
        $cacheVarName = '';

        if (is_numeric($field)) {
            $field = $this->data->fields[$field];
        }

        //condition is specified for values from search templates
        $condition = null;
        if (is_array($value)) {
            if (isset($value['cond'])) {
                $condition = Template::formatConditionForDisplay($field, $value['cond'], $html).' ';
            }

            if (isset($value['value'])) {
                $value = $value['value'];

            } else {
                $value = null;
            }
        }

        //we'll cache scalar by default, but will exclude textual fields
        $cacheValue = is_scalar($value);
        if ($cacheValue) {
            $cacheVarName = 'dv' . $html . '_'. $field['id'] . '_' . $value;

            //check if value is in cache and return
            if (Cache::exist($cacheVarName)) {
                return Cache::get($cacheVarName);
            }
        }

        /*check if field is not rezerved field for usernames (cid, oid, uid, did)*/
        if (!empty($field['name']) && in_array($field['name'], array('cid', 'oid', 'uid', 'did'))) {
            $value = Util\toNumericArray($value);
            for ($i=0; $i < sizeof($value); $i++) {
                $value[$i] = User::getDisplayName($value[$i]);
            }
            $value = implode(', ', $value);

        } else {
            switch ($field['type']) {
                case 'boolean':
                case 'checkbox':
                    $value = empty($value) ? L\get('no') : L\get('yes');
                    break;

                case '_sex':
                    switch ($value) {
                        case 'm':
                            $value = L\get('male');
                            break;
                        case 'f':
                            $value = L\get('female');
                            break;
                        default:
                            $value = '';
                    }
                    break;

                case '_language':
                    @$value = @\CB\Config::get('language_settings')[\CB\Config::get('languages')[$value -1]][0];
                    break;

                case 'combo':

                case '_objects':
                    if (empty($value)) {
                        $value = '';
                        break;
                    }
                    $a = Util\toNumericArray($value);
                    if (empty($a)) {
                        if (empty($field['cfg']['source']) || !is_array($field['cfg']['source'])) {
                            $value = '';
                        }
                        break;
                    }

                    $ids = implode(',', $a);
                    $value = array();

                    if (in_array(
                        @$field['cfg']['source'],
                        array(
                            'users'
                            ,'groups'
                            ,'usersgroups'
                        )
                    )) {
                        $sql = 'SELECT id
                                ,name
                                ,trim( CONCAT(coalesce(first_name, \'\'), \' \', coalesce(last_name, \'\')) ) `title`
                                ,CASE WHEN (`type` = 1) THEN \'icon-users\' ELSE CONCAT(\'icon-user-\', coalesce(sex, \'\') ) END `iconCls`
                            FROM users_groups
                            WHERE id IN ('.$ids.')';

                        $res = DB\dbQuery($sql) or die(DB\dbQueryError());
                        while ($r = $res->fetch_assoc()) {
                            @$label = htmlspecialchars(Util\coalesce($r['title'], $r['name']), ENT_COMPAT);

                            switch (@$field['cfg']['renderer']) {
                                case 'listGreenIcons':
                                    $value[] =  $html
                                        ? '<li class="icon-padding icon-element">'.$label.'</li>'
                                        : $label;
                                    break;
                                // case 'listObjIcons':
                                default:
                                    $r['cfg'] = Util\toJSONArray(@$r['cfg']);

                                    $icon = empty($r['iconCls'])
                                        ? \CB\Browser::getIcon($r)
                                        : $r['iconCls'];

                                    if (empty($icon)) {
                                        $icon = 'icon-none';
                                    }

                                    $value[] = $html
                                        ? '<li class="icon-padding '.$icon.'">'.$label.'</li>'
                                        : $label;
                                    break;
                            }
                        }
                        $res->close();
                    } else {
                        $objects = Search::getObjects($ids, 'id,name,template_id,pids');
                        foreach ($objects as $r) {
                            @$label = $r['name'];
                            if ($html && !empty($r['pids'])) {
                                $r['pids'] = implode('/', $r['pids']);
                                $label = '<a class="locate click" template_id="'.$r['template_id'].'" path="'.$r['pids'].'" nid="'.$r['id'].'">'.$label.'</a>';
                            }

                            switch (@$field['cfg']['renderer']) {
                                case 'listGreenIcons':
                                    $value[] =  $html
                                        ? '<li class="icon-padding icon-element">'.$label.'</li>'
                                        : $label;
                                    break;
                                // case 'listObjIcons':
                                default:
                                    $icon = \CB\Browser::getIcon($r);

                                    if (empty($icon)) {
                                        $icon = 'icon-none';
                                    }

                                    $value[] = $html
                                        ? '<li class="icon-padding '.$icon.'">'.$label.'</li>'
                                        : $label;
                                    break;
                            }
                        }
                    }

                    $value = $html
                        ? '<ul class="clean">'.implode('', $value).'</ul>'
                        : implode(', ', $value);
                    break;

                case '_fieldTypesCombo':
                    $value = L\get(@static::$fieldTypeNames[$value]);
                    break;

                case 'date':
                    $value = Util\formatMysqlDate($value);
                    break;

                case 'datetime':
                    // $value = Util\formatMysqlTime($value);
                    $value = Util\formatMysqlDate(
                        $value,
                        \CB\getOption('short_date_format'). ' ' . \CB\getOption('time_format')
                    );

                    break;

                case 'time':
                    if (empty($value)) {
                        continue;
                    }

                    $format = empty($field['format'])
                        ? 'H:i'
                        : $field['format'];

                    $date = \DateTime::createFromFormat('H:i:s', $value);

                    if (is_object($date)) {
                        $value = $date->format($format);
                    }

                    break;

                case 'html':
                    $cacheValue = false;
                    // $value = trim(strip_tags($value));
                    // $value = nl2br($value);
                    break;
                case 'varchar':
                case 'memo':
                case 'text':
                    $cacheValue = false;

                    $renderers = '';
                    if (!empty($field['cfg']['linkRenderers'])) {
                        $renderers = $field['cfg']['linkRenderers'];
                    } elseif (!empty($field['cfg']['text_renderer'])) {
                        $renderers = $field['cfg']['text_renderer'];
                    }

                    $value = empty($renderers)
                        ? nl2br(htmlspecialchars($value, ENT_COMPAT))
                        : nl2br(Comment::processAndFormatMessage($value), $renderers);
                    break;

                default:
                    if (is_array($value)) {
                        $cacheValue = false;
                        $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                    } else {
                        $value = htmlspecialchars($value, ENT_COMPAT);
                    }
            }
        }

        if ($cacheValue) {
            Cache::set($cacheVarName, $condition.$value);
        }

        return $condition.$value;
    }

    public static function formatConditionForDisplay(&$field, $condition, $html = true)
    {
        $rez = '';
        if (empty($condition)) {
            return $rez;
        }

        switch ($field['type']) {
            case 'int':
            case 'float':
            case 'date':
            case 'datetime':
                $rez = $condition;
                break;

            case '_objects':
            case 'combo':
            case 'iconcombo':
            case 'timeunits':
            case '_sex':
                switch ($condition) {
                    case '<':
                        $rez = 'contains any';
                        break;
                    case '>':
                        $rez = 'contains all';
                        break;
                    case '=':
                        $rez = 'equal';
                        break;
                    case '!=':
                        $rez = 'not equal';
                        break;
                }
                break;

            case '_auto_title':
            case 'varchar':
            case 'text':
            case 'memo':
            case 'html':
                switch ($condition) {
                    case 'contain':
                        $rez = 'contain';
                        break;
                    case 'start':
                        $rez = 'start with';
                        break;
                    case 'end':
                        $rez = 'end with';
                        break;
                    case 'not':
                        $rez = 'does not contain';
                        break;
                    case '=':
                        $rez = 'equal';
                        break;
                    case '!=':
                        $rez = 'not equal';
                        break;
                }
                break;

            case 'checkbox':
                switch ($condition) {
                    case '=':
                        $rez = 'is';
                        break;
                    case '!=':
                        $rez = 'is not';
                        break;
                }
                break;
        }

        if ($html) {
            $rez = htmlspecialchars($rez);
        }

        return $rez;
    }
}
