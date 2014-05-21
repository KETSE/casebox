<?php
namespace DisplayColumns;

use CB\Cache;

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

        $templateId = $this->findTemplate($p);

        if (empty($pid) && empty($templateId)) {
            return;
        }

        $solrFields = $this->class->getSolrColumns($pid, $templateId);

        if (!empty($solrFields['fields'])) {
            $fl = explode(',', $sp['fl']);
            foreach ($fl as $k => $f) {
                $fl[$k] = trim($f);
            }
            foreach ($solrFields['fields'] as $sc) {
                if (!in_array($sc, $fl)) {
                    $fl[] = $sc;
                }
            }
            $sp['fl'] = implode(',', $fl);
            // if(!empty())
        }
    }

    public function onSolrQuery(&$p)
    {
        $sp = &$p['params'];
        $ip = &$p['inputParams'];
        $data = &$p['result']['data'];

        $pid = empty($ip['pid']) ? false : $ip['pid'];
        $pid = is_array($pid) ? $pid[0] : $pid;

        $templateId = $this->findTemplate($p);

        if (empty($pid) && empty($templateId)) {
            return;
        }

        $displayColumns = $this->class->getCustomDisplayColumns($pid, $templateId);
        $userLanguage = \CB\Config::get('user_language');

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

                    if (!empty($col['field_' . $userLanguage])) {
                        $customField = $col['field_' . $userLanguage];
                    } elseif (!empty($col['field'])) {
                        $customField = $col['field'];
                    }

                    $customField = explode(':', $customField);
                    $templateField = null;
                    $values = array();

                    switch ($customField[0]) {

                        case 'solr':
                            $templateField = $template->getField($customField[1]);
                            if (empty($templateField)) {
                                $templateField = array(
                                    'type' => 'varchar'
                                    ,'title' => $customField[1]
                                );
                            }
                            $values = array(@$doc[$customField[1]]);
                            break;

                        case 'calc':
                            //CustomMethod call;
                            // $templateField = $template->getField($fieldName);
                            // $values = array();
                            break;

                        default:
                            $templateField = $template->getField($customField[0]);
                            $values = $obj->getFieldValue($customField[0]);
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

    /**
     * find the template id from input params or get it from last node in path
     * @param  array $p
     * @return int   | false
     */
    protected function findTemplate(&$p)
    {
        $rez = false;
        $ip = &$p['inputParams'];

        if (!empty($ip['template_id'])) {
            $rez = $ip['template_id'];
        } else {
            $path = Cache::get('current_path');

            if (!empty($path)) {
                $node = $path[sizeof($path)-1];
                $data = $node->getData();

                if (!empty($data['template_id'])) {
                    $rez = $data['template_id'];
                }
            }
        }

        return $rez;
    }
}
