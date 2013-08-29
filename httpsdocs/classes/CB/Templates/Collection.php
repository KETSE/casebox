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

            /* loading template fields */
            $r['fields'] = array();
            $sql = 'SELECT
                    id
                    ,name
                    ,l'.\CB\USER_LANGUAGE_INDEX.' `title`
                    ,`type`
                    ,cfg
                    ,solr_column_name
                FROM templates_structure
                WHERE template_id = $1';

            $fres = DB\dbQuery($sql, $r['id']) or die(DB\dbQueryError());
            while ($fr = $fres->fetch_assoc()) {
                $fr['cfg'] = empty($fr['cfg']) ? array(): json_decode($fr['cfg']);
                $r['fields'][$fr['id']] = $fr;
            }
            $fres->close();

            /* store template in collection */
            $this->templates[] = new \CB\Template($r);
        }
        $res->close();
        //select name, type, cfg from templates_structure where id = $1
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
