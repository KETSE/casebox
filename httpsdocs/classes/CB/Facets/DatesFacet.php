<?php

namespace CB\Facets;

use CB\L;

class DatesFacet extends StringsFacet
{
    public function getSolrParams()
    {
        $rez = array();
        $cfg = &$this->config;
        switch ($cfg['facet']) {
            case "query":
                foreach ($cfg['queries'] as $key => $query) {
                    $qk = $cfg['name'].'_'.$key; //{!key=0today}cdate:[NOW/DAY TO NOW/DAY+1DAY ]
                    $rez['facet.query'][] = '{!key='.$qk.'}'.$this->field.':'.$this->replaceQueriesShortcuts($query);
                }
                break;
            case "range":

                break;
            default:
                return parent::getSolrParams();
        }

        return $rez;
    }

    public function getFilters(&$p)
    {
        $rez = array();
        if (!empty($p['filters'][$this->field])) {
            $condition = array();
            foreach ($p['filters'][$this->field] as $v) {
                if (empty($v['values'])) {
                    continue;
                }
                foreach ($v['values'] as $val) {
                    $condition[] = $this->field.':'.$this->replaceQueriesShortcuts($val);
                }
            }
            if (!empty($condition)) {
                $rez['fq'][] = '('.implode(' '.$v['mode'].' ', $condition).')';
            }
        }

        return $rez;
    }

    protected function replaceQueriesShortcuts($query)
    {
        $rez = $query;
        switch ($query) {
            case 'yesterday':
                $rez = '[NOW/DAY-1DAY TO NOW/DAY]';
                break;
            case 'today':
                $rez = '[NOW/DAY TO NOW/DAY]';
                break;
            case 'week':
                $rez = '['.$this->currentWeekDiapazon().']';
                break;
            case 'next7days':
                $rez = '[NOW/DAY TO NOW/DAY+6DAY]';
                break;
            case 'month':
                $rez = '[NOW/MONTH TO NOW/MONTH+1MONTH]';
                break;
            case 'next31days':
                $rez = '[NOW/DAY TO NOW/DAY+31DAY]';
                break;
            default: //manual period
                $a = explode('~', $query);
                if (!empty($a)) {
                    if (empty($a[0])) {
                        $a[0] = '*';
                    }
                    if (empty($a[1])) {
                        $a[1] = '*';
                    }
                    $rez = '['.$a[0].' TO '.$a[1].']';
                }
        }

        return $rez;
    }

    public function loadSolrResult($solrResult)
    {
        $this->solrData = array();
        $cfg = &$this->config;

        foreach ($cfg['queries'] as $key => $query) {
            $qk = $cfg['name'].'_'.$key;
            if (!empty($solrResult->facet_queries->{$qk})) {
                $this->solrData[$query] = $solrResult->facet_queries->{$qk};
            }
        }
    }

    public function getClientData($options = array())
    {
        $rez = array(
            'f' => $this->field
            ,'title' => $this->getTitle()
            ,'items' => array()
        );

        $cfg = &$this->config;
        if (!empty($cfg['manualPeriod'])) {
            $rez['manualPeriod'] = true;
        }

        foreach ($cfg['queries'] as $key => $query) {
            if (!empty($this->solrData[$query])) {
                $name = L\get($query);
                if (empty($name)) {
                    $name = $query;
                }

                $rez['items'][$query] = array(
                    'name' => $name
                    ,'count' => $this->solrData[$query]
                );
            }
        }

        return $rez;
    }

    public function currentWeekDiapazon()
    {
          $time1 = strtotime('previous monday');
          $time2 = strtotime('previous monday + 1 week');

        return date('Y-m-d\TH:i:s\Z', $time1).' TO '.date('Y-m-d\TH:i:s\Z', $time2);
    }
}
