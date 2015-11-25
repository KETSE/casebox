<?php
namespace DisplayColumns;

class Map extends Base
{

    protected $fromParam = 'map';

    public function onBeforeSolrQuery(&$p)
    {
        $p['rows'] = 15;
        $p['params']['fl'] = array(
            'id', 'name'
        );

        $ip = &$p['inputParams'];

        if (!empty($ip['view']['field'])) {
            $fn = \CB\Purify::solrFieldName($ip['view']['field']);
            if (!empty($fn)) {
                $this->solrFieldName = $fn;
                $p['params']['fl'][] = $fn;
                //exclude items with empty field value
                $p['params']['fq'][] = "$fn:[-90,-180 TO 90,180]";
            }
        }

        unset($p['params']['sort']);
    }

    public function onSolrQuery(&$p)
    {
        $result = &$p['result'];
        $data = &$result['data'];
    }

    public function getSolrFields($nodeId = false, $templateId = false)
    {
        $rez = array();

        return $rez;
    }

    public function getDC()
    {
        $rez = array();

        return $rez;
    }

    public function getState($param = null)
    {
        $rez = array();

        return $rez;
    }
}
