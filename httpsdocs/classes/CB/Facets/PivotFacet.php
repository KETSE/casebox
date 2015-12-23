<?php

namespace CB\Facets;

class PivotFacet extends StringsFacet
{

    /**
     * variable to store total stats from solr query if present
     * @var array
     */
    protected $totalStats = array();

    public function getSolrParams()
    {
        $rez = array();

        $cfg = &$this->config;

        $statsTag = '';

        if (empty($cfg['facet1']) || empty($cfg['facet2'])) {
            return;
        }

        if (!empty($cfg['stats']['field'])) {
            $statsTag = '{!stats=pv1}';
            $func = empty($cfg['stats']['type'])
                ? 'min'
                : $cfg['stats']['type'];

            $rez['stats.field'][] = '{!tag=pv1 ' . $func . '=true}' . $cfg['stats']['field'];
        }

        if (is_object($cfg['facet1'])) {
            $cfg['field'] = $cfg['facet1']->field . ',' . $cfg['facet2']->field;
            $rez['facet.pivot'][] = $statsTag . $cfg['field'];
        }

        return $rez;
    }

    public function getFilters(&$p)
    {
        $rez = array();

        return $rez;
    }

    public function loadSolrResult($solrResult, $statsSolrResult = null)
    {
        $this->solrData = array();
        $cfg = &$this->config;

        if (!empty($cfg['field']) && !empty($solrResult->facet_pivot->{$cfg['field']})) {
            $this->solrData = $solrResult->facet_pivot->{$cfg['field']};

            if (!empty($statsSolrResult) && !empty($statsSolrResult->stats)) {
                $this->totalStats = $statsSolrResult->stats;
            }
        }
    }

    public function getClientData($options = array())
    {
        $rez = array(
            'index' => 'pivot'
        );

        /*
        Ex:
         array (
              0 =>
              stdClass::__set_state(array(
                 'field' => 'template_type',
                 'value' => 'object',
                 'count' => 30,
                 'pivot' =>
                array (
                  0 =>
                  stdClass::__set_state(array(
                     'field' => 'cid',
                     'value' => 1,
                     'count' => 15,
                  )),
         */
        $cfg = &$this->config;

        if (empty($cfg['field'])) {
            return false;
        }

        $f1d = array();
        $f2d = array();
        // collect all distinct values available for both fields
        foreach ($this->solrData as &$v) {
            $f1d[$v->value] = 1;
            unset($v->field);
            if (!empty($v->pivot)) {
                foreach ($v->pivot as &$sv) {
                    $f2d[$sv->value] = 1;
                    unset($sv->field);
                }
            }
        }
        unset($v);

        /* clone facet classes and get data for each separately */
        $facet1 = clone $cfg['facet1'];
        $facet1->solrData = $f1d;
        $fd1  = $facet1->getClientData();

        $facet2 = clone $cfg['facet2'];
        $facet2->solrData = $f2d;
        $fd2  = $facet2->getClientData();

        //collect titles into a 2 dimentional array
        $titles = array(array(), array());

        foreach ($fd1['items'] as $k => $v) {
            $title = is_array($v)
                ? $v['name']
                : $k;
            $titles[0][$k] = $title;
        }

        foreach ($fd2['items'] as $k => $v) {
            $title = is_array($v)
                ? $v['name']
                : $k;
            $titles[1][$k] = $title;
        }

        $rez['f'] = $cfg['field'];
        $rez['titles'] = $titles;
        $rez['stats'] =  $this->totalStats;
        $rez['data'] = $this->solrData;

        $this->adjustStatsPrecision($rez);

        return $rez;
    }

    /**
     * function to adjust stats precision if have stats calculated
     * and floadPrecision is set in config
     * @param  array &$result
     * @return void
     */
    protected function adjustStatsPrecision(&$result)
    {
        $cfg = &$this->config;

        if (!empty($cfg['stats']['field']) && !empty($cfg['stats']['floatPrecision'])) {
            $field = $cfg['stats']['field'];
            $func = empty($cfg['stats']['type'])
                ? 'min'
                : $cfg['stats']['type'];
            $result['sss'] = array('field' => $cfg['stats']['field'], 'type' => 'func');
            foreach ($result['data'] as &$v) {
                if (!empty($v->pivot)) {
                    foreach ($v->pivot as &$pv) {
                        $this->adjustStatValue($pv, $field, $func, $cfg['stats']['floatPrecision']);
                    }
                }
                $this->adjustStatValue($v, $field, $func, $cfg['stats']['floatPrecision']);
            }
        }
    }

    protected function adjustStatValue(&$value, $field, $function, $precision)
    {
        if (!empty($value->stats) &&
            !empty($value->stats->stats_fields) &&
            !empty($value->stats->stats_fields->{$field}) &&
            !empty($value->stats->stats_fields->{$field}->{$function})
        ) {
            $num = $value->stats->stats_fields->{$field}->{$function};
            $intNum = intval($num);
            if ($num != $intNum) {
                $value->stats->stats_fields->{$field}->{$function} = round($num, $precision);
            }
        }
    }
}
