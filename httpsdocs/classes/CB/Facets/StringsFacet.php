<?php

namespace CB\Facets;

use CB\L;

class StringsFacet
{

    protected $config = array();

    public function __construct($config)
    {
        $this->config = $config;
        $this->field = empty($config['field'])
            ? $config['name']
            : $config['field'];
    }

    public function getSolrParams()
    {
        return array(
            'facet' => true
            ,'facet.field' => array(
                '{!ex='.$this->field.' key='.$this->config['name'].'}'.$this->field
            )
        );
    }

    public function getFilters(&$p)
    {
        $rez = array();
        if (!empty($p['filters'][$this->field])) {
            $conditions = array();
            $v = $p['filters'][$this->field];
            for ($i=0; $i < sizeof($v); $i++) {
                if (!empty($v[$i]['values'])) {
                    $conditions[] = $this->field.':('.implode(' '.$v[$i]['mode'].' ', $v[$i]['values']).')';
                }
            }

            $rez['fq'][] = implode(' AND ', $conditions);
        }

        return $rez;
    }

    public function loadSolrResult($solrResult)
    {
        $this->solrData = array();
        if (!empty($solrResult->facet_fields->{$this->config['name']})) {
            $this->solrData = $solrResult->facet_fields->{$this->config['name']};
        }
    }

    public function getTitle()
    {
        $rez = 'Facet';
        if (!empty($this->config['title'])) {
            $t = &$this->config['title'];
            if (is_scalar($t)) {
                $rez = $t;
                if ($t[0] == '[') {
                    $rez = L\get(substr($t, 1, strlen($t) - 2));
                    if (empty($rez)) {
                        $rez = $t;
                    }
                }
            } elseif (!empty($t[\CB\USER_LANGUAGE])) {
                $rez = $t[\CB\USER_LANGUAGE];
            } elseif (!empty($t[\CB\LANGUAGE])) {
                $rez = $t[\CB\LANGUAGE];
            }
        }

        return $rez;
    }

    public function getClientData()
    {
        return array(
            'f' => $this->field
            ,'title' => $this->getTitle()
            ,'items' => $this->solrData
        );
    }
}
