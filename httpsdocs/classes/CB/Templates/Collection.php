<?php
namespace CB\Templates;

use CB\DB;
use CB\Util;

/**
 * Templates collection class
 */
class Collection
{
    /**
     * array of \CB\Template classes
     * @var array
     */
    public $templates = array();

    /**
     * flag to store if loadAll was allready called
     * @var bool
     */
    protected $loadedAll = false;

    /**
     * load all templates from database
     * @param  boolean $reload reload even if already all loaded
     * @return void
     */
    public function loadAll($reload = false)
    {
        //skip loading if already loaded and reload not true
        if ($this->loadedAll && !$reload) {
            return;
        }

        $this->reset();
        /* collecting template_fields */
        $template_fields = array();
        $res = DB\dbQuery(
            'SELECT
                ts.id
                ,ts.pid
                ,ts.template_id
                ,ts.name
                ,ts.l' . \CB\Config::get('user_language_index') . ' `title`
                ,ts.`type`
                ,ts.cfg
                ,ts.order
                ,ts.solr_column_name
            FROM templates_structure ts
            JOIN tree t on ts.id = t.id AND t.dstatus = 0'
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $template_id = $r['template_id'];
            unset($r['template_id']);
            $r['cfg'] = Util\toJSONArray($r['cfg']);
            $template_fields[$template_id][$r['id']] = $r;
        }
        $res->close();

        /* loading templates */
        $res = DB\dbQuery(
            'SELECT t.id
                ,t.pid
                ,t.is_folder
                ,t.`type`
                ,t.name
                ,t.`order`
                ,t.`visible`
                ,t.iconCls
                ,t.default_field
                ,t.cfg
                ,t.title_template
                ,t.info_template
                ,o.data
            FROM templates t
            LEFT JOIN objects o
                ON t.id = o.id
            WHERE t.is_folder = 0'
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $r['cfg'] = Util\toJSONArray($r['cfg']);
            $r['data'] = Util\toJSONArray($r['data']);

            $r['fields'] = empty($template_fields[$r['id']])
                ? array()
                : $template_fields[$r['id']];

            /* store template in collection */
            $this->templates[$r['id']] = new \CB\Objects\Template($r['id'], false);
            $this->templates[$r['id']]->setData($r);
        }
        $res->close();

        $this->loadedAll = true;
    }

    /**
     * get template object by template id
     *
     * @return \CB\Objects\Template
     */
    public function getTemplate($templateId)
    {
        if (!empty($this->templates[$templateId])) {
            return $this->templates[$templateId];
        }
        $template = new \CB\Objects\Template($templateId, false);
        $template->load();

        $this->templates[$templateId] = $template;

        return $template;
    }

    /**
     * get template object by its name
     *
     * @return \CB\Objects\Template
     */
    public function getTemplateByName($name)
    {
        foreach ($this->templates as $template) {
            $data = $template->getData();
            if ($data['name'] == $name) {
                return $template;
            }
        }

        $res = DB\dbQuery('SELECT id FROM templates WHERE name = $1', $name) or die(DB\dbQueryError());
        if ($r = $res->fetch_assoc()) {
            return $this->getTemplate($r['id']);
        }
        $res->close();

        return null;
    }

    /**
     * get template type by its id
     * @param  int     $templateId
     * @return varchar
     */
    public function getType($templateId)
    {
        if (!is_numeric($templateId)) {
            return null;
        }

        // check if template has been loaded
        if (!empty($this->templates[$templateId])) {
            return $this->templates[$templateId]->getData()['type'];
        }

        $var_name = 'template_type'.$templateId;

        if (!\CB\Cache::exist($var_name)) {
            $res = DB\dbQuery(
                'SELECT `type`
                FROM templates
                WHERE id = $1',
                $templateId
            ) or die(DB\dbQueryError());
            if ($r = $res->fetch_assoc()) {
                \CB\Cache::set($var_name, $r['type']);
            }
            $res->close();
        }

        return \CB\Cache::get($var_name);
    }

    /**
     * get templates count from collection
     *
     * @return int
     */
    public function getCount()
    {
        return sizeof($this->templates);
    }

    /**
     * reset this collection
     *
     * @return void
     */
    private function reset()
    {
        $this->templates = array();
        $this->loadedAll = false;
    }
}
