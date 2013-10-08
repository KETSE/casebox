<?php
namespace CB;

/**
 * Template class
 */
class Template
{
    /**
     * template id
     * @var int
     */
    private $id = null;

    /**
     * template data
     * @var array
     */
    private $data = array();

    /**
     * template constructor method
     *
     * @param array $templateData default template data
     */
    public function __construct($templateData = array())
    {
        if (!empty($templateData)) {
            $this->setData($templateData);
        }
    }

    /**
     * get template data
     *
     * @return array template properties
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * set template data
     *
     * @param array $templateData template properties
     */
    public function setData($templateData)
    {
        if (empty($templateData['id'])) {
            throw new \Exception('TemplateData error: no template id found.');
        }
        $this->id = $templateData['id'];
        $this->data = $templateData;
    }

    /**
     * load template
     *
     * @param int $templateId template id
     */
    public function load($templateId)
    {
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
                WHERE is_folder = 0 AND id = $1';

        $res = DB\dbQuery($sql, $templateId) or die(DB\dbQueryError());
        if ($r = $res->fetch_assoc()) {
            $r['cfg'] = empty($r['cfg']) ? array(): json_decode($r['cfg']);
            $this->data = $r;
        } else {
            throw new \Exception("Template load error: no template found with id = $templateId");
        }
        $res->close();
        $this->id = $templateId;

        /* loading template fields */
        $this->data['fields'] = array();
        $sql = 'SELECT
                    id
                    ,name
                    ,l'.\CB\USER_LANGUAGE_INDEX.' `title`
                    ,`type`
                    ,cfg
                    ,solr_column_name
                FROM templates_structure
                WHERE template_id = $1';//and ts.solr_column_name IS NOT NULL

        $res = DB\dbQuery($sql, $templateId) or die(DB\dbQueryError()."\n".$sql);
        while ($r = $res->fetch_assoc()) {
            $r['cfg'] = json_decode($r['cfg']) or array();
            $this->data['fields'][$r['id']] = $r;
        }
        $res->close();
    }
}
