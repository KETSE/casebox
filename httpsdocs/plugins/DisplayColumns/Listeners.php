<?php
namespace DisplayColumns;

use CB\Cache;
use CB\Util;
use CB\State;

class Listeners
{

    public function onBeforeSolrQuery(&$p)
    {
        $sp = &$p['params'];
        $this->inputParams = &$p['inputParams'];

        if (@$this->inputParams['from'] == 'tree') {
            return;
        }

        $solrFields = $this->getSolrFields();

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
        }

        if (empty($p['inputParams']['strictSort']) && !empty($solrFields['sort'])) {
            $sp['sort'] = $solrFields['sort'];
        }
    }

    public function onSolrQuery(&$p)
    {
        $sp = &$p['params'];
        $ip = &$p['inputParams'];
        $data = &$p['result']['data'];

        if (@$ip['from'] == 'tree') {
            return;
        }

        $userLanguage = \CB\Config::get('user_language');

        $displayColumns = $this->getDC();

        if (!empty($displayColumns['data'])) {
            //set custom display columns data
            $customColumns = array();

            foreach ($displayColumns['data'] as $k => $col) {
                $fieldName = is_numeric($k) ? $col : $k;
                $customColumns[$fieldName] = is_numeric($k) ? array() : $col;
                if (empty($customColumns[$fieldName]['solr_column_name'])) {
                    $customColumns[$fieldName]['localSort'] = true;
                }
            }

            // fill custom columns data
            foreach ($data as &$doc) {
                if (!is_numeric($doc['id'])) {
                    continue;
                }

                $obj = \CB\Objects::getCachedObject($doc['id']);
                $template = $obj->getTemplate();

                foreach ($customColumns as $fieldName => &$col) {
                    //detect field name
                    $customField = $fieldName;

                    if (!empty($col['field_' . $userLanguage])) {
                        $customField = $col['field_' . $userLanguage];
                    } elseif (!empty($col['field'])) {
                        $customField = $col['field'];
                    }

                    $customField = explode(':', $customField);
                    $templateField = null;
                    $values = array();

                    if (($customField[0] == 'solr') || (!empty($col['solr_column_name']))) {
                        $solrFieldName = empty($col['solr_column_name'])
                            ? $customField[1]
                            : $col['solr_column_name'];

                        $customField = ($customField[0] == 'solr')
                            ? $customField[1]
                            : $customField[0];

                        $values = array(@$doc[$solrFieldName]);

                        $templateField = $template->getField($solrFieldName);
                        if (empty($templateField)) {
                            $templateField = array(
                                'type' => 'varchar'
                                ,'name' => $solrFieldName
                                ,'title' => @Util\coalesce(
                                    $col[$userLanguage],
                                    $col['title_'.$userLanguage],
                                    $col['title'],
                                    $col['name'],
                                    $customField
                                )
                            );
                        }
                        // $values = array(@$doc[$customField[1]]);

                    } elseif ($customField[0] == 'calc') { //calculated field
                            //CustomMethod call;
                            // $templateField = $template->getField($fieldName);
                            // $values = array();

                    } else { //default
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

            $rez = $customColumns;
        }

        /* get user state and merge the state with display columns */

        $stateFrom = empty($displayColumns['from'])
            ? 'default'
            : $displayColumns['from'];

        $state = State\DBProvider::getGridViewState($stateFrom);
        if (!empty($state['columns'])) {
            $rez = array();
            foreach ($state['columns'] as $k => $c) {
                if (!empty($customColumns[$k])) {
                    $c = array_merge($customColumns[$k], $c);
                    unset($customColumns[$k]);
                }
                $rez[$k] = $c;
            }

            if (!empty($customColumns)) {
                $rez = array_merge($rez, $customColumns);
            }
        }

        if (!empty($state['sort'])) {
            $p['result']['sort'] = $state['sort'];
        }
        /* end of get user state and merge the state with display columns */

        if (!empty($rez)) {
            $p['result']['DC'] = $rez;
        }
    }

    /**
     * get display columns for last node in active path
     * @return array
     */
    public static function getDC()
    {
        $rez = array();

        $path = Cache::get('current_path');

        if (!empty($path)) {
            $node = $path[sizeof($path)-1];
            $rez = $node->getNodeParam('DC');
        }

        return $rez;
    }

    /**
     * get solr columns for a node based on display columns
     * @return array
     */
    public function getSolrFields($nodeId = false, $templateId = false)
    {
        $rez = array(
            'fields' => array()
            ,'sort' => array()
        );

        $displayColumns = $this->getDC();

        if (!empty($displayColumns['data'])) {
            foreach ($displayColumns['data'] as $column) {
                if (is_array($column) && !empty($column['solr_column_name'])) {
                    $rez['fields'][$column['solr_column_name']] = 1;

                    if ((@$this->inputParams['sort'] == $column['solr_column_name']) &&
                        !empty($this->inputParams['dir'])
                    ) {
                        $rez['sort'][] = $column['solr_column_name'] . ' ' . strtolower($this->inputParams['dir']);
                    } elseif (!empty($column['sort'])) {
                        $rez['sort'][] = $column['solr_column_name'] . ' ' . $column['sort'];
                    }

                } elseif (is_scalar($column)) {
                    $a = explode(':', $column);
                    if ($a[0] == 'solr') {
                        $rez['fields'][$a[1]] = 1;
                    }
                }
            }
        }

        /* get user state and check if user has a custom sorting */
        if (empty($this->inputParams['sort'])) {
            $stateFrom = empty($displayColumns['from'])
                ? 'default'
                : $displayColumns['from'];

            $state = State\DBProvider::getGridViewState($stateFrom);

            if (!empty($state['sort']['field'])) {
                $rez['sort'] = array(
                    $state['sort']['field']
                    .' '
                    .strtolower(Util\coalesce(@$state['sort']['direction'], 'asc'))
                );
            }

            if (!empty($rez['sort'])) {
                $rez['sort'] = 'ntsc asc,'.implode(',', $rez['sort']);
            }
        }
        /* end of get user state and check if user has a custom sorting */

        if (!empty($rez['fields'])) {
            $rez['fields'] = array_keys($rez['fields']);
        }

        return $rez;
    }
}
