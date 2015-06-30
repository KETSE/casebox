<?php

namespace CB\Objects\Plugins;

class Base
{
    // id of the objects for which the plugin is displayed
    protected $id = null;

    public function __construct($id = null)
    {
        $this->id = $id;
    }
    /**
     * get plugin data for given object id
     * @return array ext direct responce
     */
    public function getData($id = false)
    {
        if ($id === false) {
            $id = $this->id;
        } else {
            $this->setId($id);
        }

        if (!is_numeric($id)) {
            //id was not specified
            return null;
        }

        return array(
            'success' => true
        );
    }

    public function setId($id)
    {
        if ($this->id != $id) {
            unset($this->objectClass);
        }
        $this->id = $id;
    }

    protected function getObjectClass()
    {
        $rez = null;

        if (empty($this->objectClass) && !empty($this->id)) {
            $this->objectClass = \CB\Objects::getCachedObject($this->id);
            $rez = &$this->objectClass;
        }

        return $rez;
    }
}
