<?php
namespace CB\Objects;

use CB\DataModel as DM;
use CB\Util;
use CB\User;
use CB\UsersGroups;
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
    protected $tableFields =  array(
        'id'
        ,'pid'
        ,'type'
        ,'name'
        // ,'l1'
        // ,'l2'
        // ,'l3'
        // ,'l4'
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

    protected function collectCustomModelData()
    {
        $p = &$this->data;
        $rez = parent::collectCustomModelData();

        //set type from data
        if (isset($rez['type']) && !empty($p['data']['type'])) {
            $rez['type'] = $p['data']['type'];
        }

        return $rez;
    }

    /**
     * internal function used by create method for creating custom data
     * @return void
     */
    protected function createCustomData()
    {
        parent::createCustomData();

        $p = &$this->data;

        if (empty($p['visible'])) {
            $p['visible'] = 0;
        }

        $data = $this->collectCustomModelData();

        if (!empty($data)) {
            DM\Templates::create($data);
        }
    }

    /**
     * load template custom data
     */
    protected function loadCustomData()
    {
        parent::loadCustomData();

        $r = DM\Templates::read($this->id);

        if (!empty($r)) {
            //dont override name from tree with name from templates table
            unset($r['name']);

            $this->data = array_merge($this->data, $r);

        } else {
            throw new \Exception("Template load error: no template found with id = " . $this->id);
        }

        /* loading template fields */
        $this->data['fields'] = array();
        $this->data['fieldsByIndex'] = array();
        $this->fieldsOrder = array();

        $recs = DM\TemplatesStructure::getFields($this->id);

        foreach ($recs as &$r) {
            $this->data['fields'][$r['id']] = &$r;
            $this->data['fieldsByIndex'][] = &$r;

            $this->fieldsOrder[$r['name']] = intval($r['order']);
            unset($r);
        }
    }

    /**
     * update objects custom data
     * @return boolean
     */
    protected function updateCustomData()
    {
        parent::updateCustomData();

        /* saving template data to templates and templates_structure tables */
        $data = $this->collectCustomModelData();

        unset($data['id']);

        if (!empty($data)) {
            $data['id'] = $this->id;

            DM\Templates::update($data);
        }
    }

    protected function copyCustomDataTo($targetId)
    {
        DM\Templates::copy($this->id, $targetId);
    }

    public function getType()
    {
        $data = $this->getData();

        $rez = empty($data['type'])
            ? null
            : $data['type'];

        return $rez;
    }

    /**
     * get fields
     * @return array
     */
    public function getFields()
    {
        $rez = array();

        if (isset($this->data['fields'])) {
            $rez = $this->data['fields'];
        }

        return $rez;
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
        foreach ($this->data['fields'] as $fv) {
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
        //collect fields order if empty
        //this can happen when templates loaded in bulk maner
        if (empty($this->fieldsOrder)) {
            foreach ($this->data['fields'] as &$f) {
                $this->fieldsOrder[$f['name']] = intval($f['order']);
            }
        }

        if (isset($this->fieldsOrder[$fieldName])) {
            return $this->fieldsOrder[$fieldName];
        }

        return 0;
    }

    public function getActionFlags($userId = false)
    {
        $d = &$this->data;
        $sd = &$d['sys_data'];

        return array(
            'updateSolrData' => !empty($sd['solrConfigUpdated'])
        );
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
        foreach ($this->data['fieldsByIndex'] as $fv) {
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
            $fid = empty($field['id'])
                ? $field['name']
                : $field['id'];

            $cacheVarName = 'dv' . $html . '_'. $fid . '_' . $value;

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
                    $value = empty($value)
                        ? ''
                        : (($value) < 0
                            ? L\get('no')
                            : L\get('yes')
                        );

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
                    $ids = Util\toNumericArray($value);
                    if (empty($ids)) {
                        if (empty($field['cfg']['source']) || !is_array($field['cfg']['source'])) {
                            $value = '';
                        }
                        break;
                    }

                    $value = array();

                    if (in_array(
                        @$field['cfg']['source'],
                        array(
                            'users'
                            ,'groups'
                            ,'usersgroups'
                        )
                    )) {

                        $udp = UsersGroups::getDisplayData($ids);

                        foreach ($ids as $id) {
                            if (empty($udp[$id])) {
                                continue;
                            }
                            $r = &$udp[$id];

                            $label = @htmlspecialchars(Util\coalesce($r['title'], $r['name']), ENT_COMPAT);

                            if ($html) {
                                switch (@$field['cfg']['renderer']) {
                                    case 'listGreenIcons':
                                        $label = '<li class="icon-padding icon-element">' . $label . '</li>';
                                        break;

                                    // case 'listObjIcons':
                                    default:
                                        $icon = empty($r['iconCls'])
                                            ? 'icon-none'
                                            : $r['iconCls'];

                                        $label = '<li class="icon-padding '.$icon.'">'.$label.'</li>';
                                        break;
                                }
                            }

                            $value[] = $label;
                        }

                    } else {
                        $objects = \CB\Objects::getCachedObjects($ids);
                        foreach ($ids as $id) {
                            if (empty($objects[$id])) {
                                continue;
                            }

                            $obj = &$objects[$id];

                            $d = $obj->getData();
                            $label = $obj->getHtmlSafeName();

                            $pids = $d['pids'];

                            if ($html && !empty($pids)) {
                                $pids = str_replace(',', '/', $pids);
                                $linkType = empty($field['cfg']['linkType'])
                                    ? ''
                                    : 'link-type-' . $field['cfg']['linkType'];

                                $label = '<a class="click ' . $linkType . '" template_id="'.$d['template_id'].'" path="'.$pids.'" nid="'.$id.'">'.$label.'</a>';
                            }

                            switch (@$field['cfg']['renderer']) {
                                case 'listGreenIcons':
                                    $value[] =  $html
                                        ? '<li class="icon-padding icon-element">'.$label.'</li>'
                                        : $label;
                                    break;
                                // case 'listObjIcons':
                                default:
                                    $icon = \CB\Browser::getIcon($d);

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
                    $value = Util\formatMysqlDate(Util\dateISOToMysql($value));
                    break;

                case 'datetime':
                    $value = Util\UTCTimeToUserTimezone($value);

                    break;

                case 'time':
                    if (empty($value)) {
                        continue;
                    }

                    $format = empty($field['format'])
                        ? 'H:i'
                        : $field['format'];

                    if (is_numeric($value)) {
                        $s = $value % 60;
                        $value = floor($value / 60);
                        $m = $value % 60;
                        $value = floor($value / 60);
                        if (strlen($value) < 2) {
                            $value = '0' . $value;
                        }
                        if (strlen($m) < 2) {
                            $m = '0' . $m;
                        }
                        $value .= ':' . $m;
                        if (!empty($s)) {
                            if (strlen($s) < 2) {
                                $s = '0' . $s;
                            }
                            $value .= ':' . $s;
                        }

                    } else {
                        $date = \DateTime::createFromFormat($format, $value);

                        if (is_object($date)) {
                            $value = $date->format($format);
                        }
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
                        $value = Util\jsonEncode($value);
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
