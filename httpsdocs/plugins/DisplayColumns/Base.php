<?php
namespace DisplayColumns;

use CB\Config;
use CB\User;
use CB\Cache;
use CB\Util;
use CB\State;
use CB\data;
use CB\Search;

class Base
{

    protected $fromParam = 'none';

    /**
     * method used to implement custom logic on before solr query
     * @param  array $p search params
     * @return void
     */
    public function onBeforeSolrQuery(&$p)
    {
        $this->params = &$p;

        $sp = &$p['params'];
        $this->inputParams = &$p['inputParams'];

        if (@$this->inputParams['from'] !== $this->fromParam) {
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

        } elseif (!empty($this->inputParams['sort'][0]['property']) &&
            empty($solrFields['sort'])
            // && !in_array($this->inputParams['sort'][0]['property'], \CB\Search::$defaultFields)
        ) {
            $sp['sort'] = 'ntsc asc, order asc';
        }
    }

    /**
     * method used to implement custom logic on solr query
     * @param  array $p search params
     * @return void
     */
    public function onSolrQuery(&$p)
    {
        $this->params = &$p;

        $ip = &$p['inputParams'];

        if (@$ip['from'] !== $this->fromParam) {
            return;
        }

        $sp = &$p['params'];
        $data = &$p['result']['data'];

        $rez = array();

        $userLanguage = \CB\Config::get('user_language');

        $displayColumns = $this->getDC();

        if (!empty($displayColumns['sort'])) {
            $p['result']['sort'] = $displayColumns['sort'];
        }

        //get state
        $stateFrom = empty($displayColumns['from'])
            ? 'default'
            : $displayColumns['from'];

        $state = $this->getState($stateFrom);

        //check grouping params
        if (!empty($ip['userGroup']) && !empty($ip['group'])) {
            $p['result']['group'] = array(
                'property' => $ip['sourceGroupField']
                ,'direction' => $ip['group']['direction']
            );

        } elseif (!empty($state['group'])) {
            $p['result']['group'] = $state['group'];
        }

        $customColumns = array();

        $idx = 0;

        //set custom display columns data
        if (!empty($displayColumns['data'])) {

            foreach ($displayColumns['data'] as $k => $col) {
                $fieldName = is_numeric($k) ? $col : $k;
                $customColumns[$fieldName] = is_numeric($k) ? array() : $col;

                if (empty($customColumns[$fieldName]['solr_column_name']) &&
                    !in_array($fieldName, Search::$defaultFields)
                ) {
                    $customColumns[$fieldName]['localSort'] = true;
                }

                if (!isset($customColumns[$fieldName]['idx'])) {
                    $customColumns[$fieldName]['idx'] = $idx++;
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

                        $templateField = $template->getField($customField);
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

                    if (empty($col['sortType']) && ($customField[0] != 'solr') && (empty($col['solr_column_name']))) {
                        switch ($templateField['type']) {
                            case 'date':
                            case 'datetime':
                                $col['sortType'] = 'asDate';
                                break;

                            case 'float':
                                $col['sortType'] = 'asFloat';
                                break;

                            case '_objects':
                            case 'checkbox':
                            case 'int':
                                $col['sortType'] = 'asInt';
                                break;

                            case 'html':
                            case 'memo':
                            case 'text':
                                $col['sortType'] = 'asUCText';
                                break;

                            default:
                                $col['sortType'] = 'asUCString';
                                break;
                        }
                    }

                    //update value from document if empty from solr query
                    if (empty($doc[$fieldName]) ||
                        // temporary check, this should be reanalised
                        in_array($templateField['type'], array('_objects'))
                    ) {
                        foreach ($values as $value) {
                            $value = is_array($value)
                                ? @$value['value']
                                : $value;
                            $doc[$fieldName] = $template->formatValueForDisplay($templateField, $value, false);
                        }
                    }

                    //
                }
            }

            $rez = $customColumns;
        }

        /* merge the state with display columns */
        if (!empty($state['columns'])) {
            $rez = array();

            $defaultColumns = array_keys(Config::getDefaultGridViewColumns());

            foreach ($state['columns'] as $k => $c) {
                if (!empty($customColumns[$k])) {
                    $c = array_merge($customColumns[$k], $c);
                    unset($customColumns[$k]);
                    $rez[$k] = $c;
                } elseif (in_array($k, $defaultColumns)) {
                    $rez[$k] = $c;
                }
            }

            if (!empty($customColumns)) {
                $rez = array_merge($rez, $customColumns);
            }
        }

        //analize grouping
        $this->analizeGrouping($p);

        /* user clicked a column to sort by */
        if (!empty($ip['userSort'])) {
            $p['result']['sort'] = array(
                'property' => $ip['sort'][0]['property']
                ,'direction' => $ip['sort'][0]['direction']
            );

        } elseif (!empty($state['sort'])) {
            $p['result']['sort'] = $state['sort'];
        }
        /* end of get user state and merge the state with display columns */

        if (!empty($rez)) {
            $p['result']['DC'] = $rez;
        }

        /* check if we need to sort records using php (in case sort field is not from solr)*/
        if (!empty($p['result']['sort']) &&
            !empty($rez[$p['result']['sort']['property']]['localSort'])
        ) {
            $this->sortRecords($data, $p['result']['sort'], $rez[$p['result']['sort']['property']]);
        }
    }

    /**
     * method to analize grouping params and add group column to result
     * @param  array $p search params
     * @return void
     */
    protected function analizeGrouping(&$p)
    {
        if (empty($p['result']['group']['property'])) {
            return;
        }

        $field = $p['result']['group']['property'];
        $data = &$p['result']['data'];

        $count = sizeof($data);
        for ($i=0; $i < $count; $i++) {
            $d = &$data[$i];

            $v = @$d[$field];

            switch ($field) {
                case 'cid':
                case 'uid':
                case 'oid':
                    if (empty($v)) {
                        $d['group'] = '';
                        $d['groupText'] = 'none';

                    } else {
                        $d['group'] = $v;
                        $d['groupText'] = User::getDisplayName($d[$field]);
                    }
                    break;

                case 'date':
                case 'date_end':
                case 'cdate':
                case 'udate':
                case 'ddate':
                    if (empty($v)) {
                        $d['group'] = 'empty';
                        $d['groupText'] = 'empty';
                    } else {
                        $d['group'] = substr($v, 0, 7) . '-01T00:00:00Z';
                        $d['groupText'] = Util\formatMysqlDate(
                            $d['group'],
                            'Y, F'
                        );
                    }

                    break;

                case 'size':
                    if (empty($v)) {
                        $d['group'] = 'up to 1 MB';
                    } else {
                        $t = Util\formatFileSize($v);
                        $d['size'] .= ' - '.$t;
                        $t = explode(' ', $t);

                        if ((@$t[1] == 'KB') || ($t[0] <= 1)) {
                            $t = 1;
                        } else {
                            $q = floor($t[0] / 10) * 10;
                            $t =  ($t[0] > $q)
                                ? $q + 10
                                : $q;
                        }

                        $d['size'] .= ' - '.$t;
                        $d['group'] = ($t < 1)
                            ? 'up to 1 MB'
                            : 'up to ' . $t . ' MB';
                    }
                    break;

                default:
                    if (empty($d[$field])) {
                        $d['group'] = 'empty';
                    } else {
                        //split values by comma and duplicate records if multivalued
                        $values = is_array($d[$field])
                            ? $d[$field]
                            : explode(',', $d[$field]);

                        $d['group'] = trim(array_shift($values));

                        for ($j=0; $j < sizeof($values); $j++) {
                            $newRecord = $d;
                            $newRecord['group'] = trim($values[$j]);
                            array_push($data, $newRecord);
                        }
                    }
            }
        }
    }

    /**
     * get display columns for last node in active path
     * @return array
     */
    public function getDC()
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
     * get state
     * @param  variant $param some param if needed
     * @return array
     */
    protected function getState($param = null)
    {
        return array();
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
            foreach ($displayColumns['data'] as $columnName => $column) {
                if (is_array($column) && !empty($column['solr_column_name'])) {
                    $rez['fields'][$column['solr_column_name']] = 1;

                    if ((@$this->inputParams['sort'][0]['property'] == $columnName) &&
                        !empty($this->inputParams['sort'][0]['direction'])
                    ) {
                        $rez['sort'][] = $column['solr_column_name'] . ' ' . strtolower($this->inputParams['sort'][0]['direction']);
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

        /* user clicked a column to sort by */
        $property = null;
        $dir = 'asc';

        if (!empty($this->inputParams['userSort'])) {
            $dir = strtolower($this->inputParams['sort'][0]['direction']);

            if (in_array($dir, array('asc', 'desc')) &&
                preg_match('/^[a-z_0-9]+$/i', $this->inputParams['sort'][0]['property'])
            ) {
                $property = $this->inputParams['sort'][0]['property'];
                if (!empty($displayColumns['data'][$property]['solr_column_name'])) {
                    $property = $displayColumns['data'][$property]['solr_column_name'];
                }
            }

        } else {
            /* get user state and check if user has a custom sorting */
            $stateFrom = empty($displayColumns['from'])
                ? 'default'
                : $displayColumns['from'];

            $state = $this->getState($stateFrom);

            if (!empty($state['sort']['property'])) {
                $property = $state['sort']['property'];
                $dir = strtolower(Util\coalesce(@$state['sort']['direction'], 'asc'));

                if (!empty($displayColumns['data'][$property]['solr_column_name'])) {
                    $property = $displayColumns['data'][$property]['solr_column_name'];
                }
            }
        }

        if (!empty($property)) {
            $rez['sort'] = 'ntsc asc,' . $property . ' ' . $dir;
        }

        /* end of get user state and check if user has a custom sorting */

        if (!empty($rez['fields'])) {
            $rez['fields'] = array_keys($rez['fields']);
        }

        return $rez;
    }

    protected function sortRecords(&$data, $sortOptions, $fieldConfig)
    {
        $sortType = empty($fieldConfig['sortType'])
            ? 'asString'
            : $fieldConfig['sortType'];

        $sortDir = strtolower($sortOptions['direction']);

        data\Sorter::$sortField = $sortOptions['property'];

        $sorter = 'CB\\data\\Sorter::' . $sortType . ucfirst($sortDir);

        usort($data, $sorter);
    }
}
