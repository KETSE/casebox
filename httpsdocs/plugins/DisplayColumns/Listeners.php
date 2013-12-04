<?php
namespace DisplayColumns;

class Listeners
{
    private $class = null;
    public function __construct()
    {
        $this->class = DCSingleton::getInstance();
    }

    public function onBeforeSolrQuery(&$p)
    {
        $searchClass = &$p['class'];
        $sp = &$p['params'];
        $ip = &$p['inputParams'];

        $pid = empty($ip['pid']) ? false : $ip['pid'];
        $pid = is_array($pid) ? $pid[0] : $pid;

        $templateId = empty($ip['template_id']) ? false : $ip['template_id'];

        if (empty($pid) && empty($templateId)) {
            return;
        }

        $requiredSolrColumns = $this->class->getSolrColumns($pid, $templateId);
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

        $pid = empty($ip['pid']) ? false : $ip['pid'];
        $pid = is_array($pid) ? $pid[0] : $pid;

        $templateId = empty($ip['template_id']) ? false : $ip['template_id'];

        if (empty($pid) && empty($templateId)) {
            return;
        }

        $displayColumns = $this->class->getCustomDisplayColumns($pid, $templateId);

        if (!empty($displayColumns)) {
            //set custom display columns data
            $customColumns = array();
            foreach ($displayColumns as $k => $col) {
                $fieldName = is_numeric($k) ? $col : $k;
                $customColumns[$fieldName] = is_numeric($k) ? array() : $col;
            }
            // fill custom columns data
            foreach ($data as &$doc) {
                $obj = \CB\Objects::getCachedObject($doc['id']);
                $template = $obj->getTemplate();
                foreach ($customColumns as $fieldName => &$col) {
                    $customField = $fieldName;
                    if (!empty($col['field_'.\CB\USER_LANGUAGE])) {
                        $customField = $col['field_'.\CB\USER_LANGUAGE];
                    } elseif (!empty($col['field'])) {
                        $customField = $col['field'];
                    }
                    $customField = explode(':', $customField);
                    $templateField = null;
                    $values = array();
                    switch ($customField[0]) {
                        case 'solr':
                            $templateField = $template->getField($fieldName);
                            $values = array(@$doc[$customField[0]]);
                            break;
                        case 'calc':
                            $templateField = $template->getField($fieldName);
                            $values = array(); //CustomMethod call;
                            break;
                        default:
                            $templateField = $template->getField($customField[0]);
                            $values = $obj->getFieldValue($fieldName);
                    }

                    //populate column properties if empty
                    if (empty($col['title'])) {
                        $col['title'] = $templateField['title'];
                    }

                    foreach ($values as $value) {
                        $value = is_array($value)
                            ? @$value['value']
                            : $value;
                        $doc[$fieldName] = $template->formatValueForDisplay($templateField, $value, false);
                    }
                }
            }
            $p['result']['DC'] = $customColumns;
        }
    }
}
