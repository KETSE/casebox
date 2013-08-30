<?php
namespace CB\Templates;

use CB\DB as DB;

/**
 * Templates collection class
 */
class Collection
{
    /**
     * array of \CB\Template classes
     * @var array
     */
    private $templates = array();

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
        $sql = 'SELECT
                id
                ,template_id
                ,name
                ,l'.\CB\USER_LANGUAGE_INDEX.' `title`
                ,`type`
                ,cfg
                ,solr_column_name
            FROM templates_structure';

        $res = DB\dbQuery($sql) or die(DB\dbQueryError());
        while ($r = $res->fetch_assoc()) {
            $template_id = $r['template_id'];
            unset($r['template_id']);
            $template_fields[$template_id][$r['id']] = $r;
        }
        $res->close();

        /* loading templates */
        $sql = 'SELECT id
                    ,pid
                    ,is_folder
                    ,`type`
                    ,name
                    ,l'.\CB\USER_LANGUAGE_INDEX.' `title`
                    ,`order`
                    ,`visible`
                    ,iconCls
                    ,default_field
                    ,cfg
                    ,title_template
                    ,info_template
                FROM templates
                WHERE is_folder = 0';

        $res = DB\dbQuery($sql) or die(DB\dbQueryError());
        while ($r = $res->fetch_assoc()) {
            $r['cfg'] = empty($r['cfg']) ? array(): json_decode($r['cfg']);

            $r['fields'] = empty($template_fields[$r['id']])
                ? array()
                : $template_fields[$r['id']];

            /* store template in collection */
            $this->templates[$r['id']] = new \CB\Template($r);
        }
        $res->close();
    }

    /**
     * get template object by template id
     *
     * @return \CB\Template
     */
    public function getTemplate($templateId)
    {
        if (!empty($this->templates[$templateId])) {
            return $this->templates[$templateId];
        }
        $template = new \CB\Template();
        $template->load($templateId);

        $this->templates[$templateId] = $template;

        return $template;
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
