<?php
namespace CB\Objects\Plugins;

use CB\Objects;
use CB\Util;
use CB\Templates;

class Base
{
    // id of the objects for which the plugin is displayed
    protected $id = null;

    public function __construct($config = [])
    {
        if (!empty($config['objectId'])) {
            $this->id = $config['objectId'];
            unset($config['objectId']);
        }

        $this->config = $config;
    }
    /**
     * get plugin data for given object id
     * @return array ext direct response
     */
    public function getData($id = false)
    {
        if ($id === false) {
            $id = $this->id;
        } else {
            $this->setId($id);
        }

        if (!is_numeric($id) || !$this->isVisible()) {
            //id was not specified
            return null;
        }

        return array(
            'success' => true
        );
    }

    /**
     * check if current plugin is visible according to its config
     * @return boolean
     */
    protected function isVisible()
    {
        $rez = true;

        $config = $this->config;
        $vcfg = empty($config['visibility'])
            ? []
            : $config['visibility'];
        $obj = Objects::getCachedObject($this->id);

        // if (get_class($this) == 'CB\\Objects\\Plugins\\Files') {
        //     var_export($config);
        //     var_export($vcfg);
        // }
        if (!empty($vcfg['fn'])) {
            $rez = $this->getFunctionResult($vcfg['fn']);

        } elseif (!empty($vcfg['fields'])) {
            if (!empty($obj)) {
                foreach ($vcfg['fields'] as $fn => $fv) {
                    if (is_scalar($fv)) {
                        $fv = [$fv];
                    }

                    $val = @$obj->getFieldValue($fn, 0)['value'];
                    $fieldRez = false;

                    foreach ($fv as $v) {
                        if (is_numeric($v)) {
                            $arr = Util\toNumericArray($val);
                            $fieldRez = $fieldRez || in_array($v, $arr);
                        } else {
                            $fieldRez = $fieldRez || ($v == $val);
                        }
                    }

                    $rez = $rez && $fieldRez;
                }
            }
        }

        $template = false;
        if (!empty($obj)) {
            $template = $obj->getTemplate();
        } elseif (!empty($this->config['template_id'])) {
            $template = Templates\SingletonCollection::getInstance()->getTemplate($this->config['template_id']);
        }

        if (!empty($template)) {
            $ttype = $template->getType();

            //check if template_type is specified
            if ($rez && !empty($vcfg['template_type'])) {
                $tt = Util\toTrimmedArray($vcfg['template_type']);
                $rez = in_array($ttype, $tt);
            }

            //check if template_type negation is specified
            if ($rez && !empty($vcfg['!template_type'])) {
                $tt = Util\toTrimmedArray($vcfg['!template_type']);
                $rez = !in_array($ttype, $tt);
            }
        }

        //check if context is specified
        if ($rez && !empty($vcfg['context'])) {
            $context = Util\toTrimmedArray($vcfg['context']);
            $rez = in_array($config['context'], $context);
        }

        //check if context negation is specified
        if ($rez && !empty($vcfg['!context'])) {
            $context = Util\toTrimmedArray($vcfg['!context']);
            $rez = !in_array($config['context'], $context);
        }

        return $rez;
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
