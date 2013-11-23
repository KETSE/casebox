<?php
namespace DisplayColumns;

class Listeners
{
    private $class = null;
    public function __construct()
    {
        $this->class = DCSingleton::getInstance();
    }

    public function onBeforeSolrQuery($p)
    {
        $searchClass = &$p['class'];
        $sp = &$p['params'];
        $ip = &$p['inputParams'];
        if (empty($ip['pid'])) {
            return;
        }
        $requiredSolrColumns = $this->class->getSolrColumns($ip['pid']);
        if (!empty($requiredSolrColumns)) {
            $fl = explode(',', $sp['fl']);
            foreach ($fl as $k => $f) {
                $fl[$k] = trim($f);
            }
            foreach ($requiredSolrColumns as $sc) {
                if (!in_array($sc, $fl)) {
                    $fl[] = $sc;
                }
            }
            $sp['fl'] = implode(',', $fl);
        }
    }

    public function onSolrQuery(&$p)
    {
        $sp = &$p['params'];
        $ip = &$p['inputParams'];
        $data = &$p['result']['data'];

        if (empty($ip['pid'])) {
            return;
        }

        $displayColumns = $this->class->getCustomDisplayColumns($ip['pid']);
        // var_dump($displayColumns);
        if (!empty($displayColumns)) {
            //set custom display columns data
            $customColumns = array();
            foreach ($displayColumns as $k => $col) {
                $fieldName = is_numeric($k) ? $col : $k;
                $customColumns[$fieldName] = is_numeric($k) ? array() : $col;
            }
            // fill custom columns data
            foreach ($data as &$doc) {
                $obj = $this->getObject($doc['id']);
                $template = $obj->getTemplate();
                foreach ($customColumns as $fieldName => &$col) {
                    $field = $template->getField($fieldName);
                    //populate column properties if empty
                    if (empty($col)) {
                        $col['title'] = $field['title'];
                    }

                    $value = $obj->getFieldValue($fieldName);
                    $doc[$fieldName] = $template->formatValueForDisplay($field, $value);
                }
            }
            $p['result']['DC'] = $customColumns;
        }
    }

    protected function getObject($id)
    {
        //verify if already have cached result
        $var_name = 'Objects['.$id.']';
        if (\CB\Cache::exist($var_name)) {
            return \CB\Cache::get($var_name);
        }
        $obj = \CB\Objects::getCustomClassByObjectId($id);
        $obj->load();
        \CB\Cache::set($var_name, $obj);

        return $obj;
    }
}
