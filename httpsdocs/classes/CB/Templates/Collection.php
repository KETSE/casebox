<?php
namespace CB\Templates;

use CB\DB as DB;
use CB\Util as Util;

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
     * load all templates from database
     *
     * @return void
     */
    public function loadAll()
    {
        $this->reset();
        /* collecting template_fields */
        $template_fields = array();
        $res = DB\dbQuery(
            'SELECT
                id
                ,pid
                ,template_id
                ,name
                ,l'.\CB\USER_LANGUAGE_INDEX.' `title`
                ,`type`
                ,cfg
                ,solr_column_name
            FROM templates_structure'
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

        $res = DB\dbQuery('SELECT id from templates where name = $1', $name) or die(DB\dbQueryError());
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
    }
}
