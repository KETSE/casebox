<?php
namespace CB\Objects;

use CB\Util;
use CB\DataModel as DM;

/**
 * Template class
 */
class TemplateField extends Object
{

    /**
     * available table fields in templates table
     * @var array
     */
    protected $tableFields =  array(
        'id'
        ,'pid'
        //,'template_id'
        ,'name'
        // ,'l1'
        // ,'l2'
        // ,'l3'
        // ,'l4'
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

        $data = $this->collectCustomModelData();

        $data['template_id'] = $this->detectParentTemplate();

        DM\TemplatesStructure::create($data);
    }

    /**
     * load template custom data
     */
    protected function loadCustomData()
    {
        parent::loadCustomData();

        $r = DM\Objects::read($this->id);

        if (!empty($r)) {
            $r = $r['data'];
        } else {
            //read from templates_structure if object not present in tree
            // for backward compatibility
            $r = DM\TemplatesStructure::read($this->id);
        }

        if (!empty($r)) {
            if (isset($r['cfg'])) {
                $r['cfg'] = Util\toJSONArray($r['cfg']);
            }
            $r['title'] = Util\detectTitle($r);

            $this->data = array_merge($this->data, $r);
        } else {
            \CB\debug("Template field load error: no field found with id = " . $this->id);
            // throw new \Exception("Template field load error: no field found with id = ".$this->id);
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
        $p = &$this->data;

        $data = $this->collectCustomModelData();

        $data['template_id'] = $this->detectParentTemplate();

        DM\TemplatesStructure::update($data);
    }

    protected function detectParentTemplate($targetPid = false)
    {
        $rez = ($targetPid === false)
            ? $this->data['pid']
            : $targetPid;

        if (empty($rez)) {
            return null;
        }

        $r = DM\TemplatesStructure::read($rez);

        if (!empty($r)) {
            $rez = $r['template_id'];
        }

        return $rez;
    }

    /**
     * copy data from templates structure table
     * @param  int  $targetId
     * @return void
     */
    protected function copyCustomDataTo($targetId)
    {
        DM\TemplatesStructure::copy(
            $this->id,
            $targetId,
            $this->detectParentTemplate($this->data['pid'])
        );
    }

    protected function moveCustomDataTo($targetId)
    {
        DM\TemplatesStructure::move(
            $this->id,
            $targetId
        );
    }
}
