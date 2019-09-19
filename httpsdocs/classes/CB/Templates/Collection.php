<?php
namespace CB\Templates;

use CB\DataModel as DM;

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
        $fields = array();
        $headers = array();
        $templateId = false;
        $headerField = false;
        $prevLevel = 0;

        $recs = DM\TemplatesStructure::getFields();

        foreach ($recs as $r) {
            if (($templateId != $r['template_id']) || ($prevLevel != $r['level'])) {
                unset($headerField);
                $headerField = false;
                $prevLevel = $r['level'];
            }

            $templateId = $r['template_id'];
            unset($r['template_id']);

            if ($r['type'] == 'H') {
                unset($headerField);
                $headerField = &$r;
            }

            $headers[$templateId][$r['name']] = &$headerField;

            $fields[$templateId][$r['id']] = &$r;

            unset($r);
        }

        /* loading templates */
        $recs = DM\Templates::readAllWithData();
        foreach ($recs as $r) {
            $r['fields'] = empty($fields[$r['id']])
                ? array()
                : $fields[$r['id']];

            $r['headers'] = empty($headers[$r['id']])
                ? array()
                : $headers[$r['id']];

            /* store template in collection */
            $this->templates[$r['id']] = new \CB\Objects\Template($r['id'], false);
            $this->templates[$r['id']]->setData($r);
        }

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

        $id = DM\Templates::toId($name);

        return $this->getTemplate($id);
    }

    /**
     * get template type by its id
     * @param  int     $id
     * @return varchar
     */
    public function getType($id)
    {
        if (!is_numeric($id)) {
            return null;
        }

        // check if template has been loaded
        if (!empty($this->templates[$id])) {
            return $this->templates[$id]->getData()['type'];
        }

        $var_name = 'template_type' . $id;

        if (!\CB\Cache::exist($var_name)) {
            $r = DM\Templates::read($id);

            if (!empty($r)) {
                \CB\Cache::set($var_name, $r['type']);
            }
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
