<?php
namespace CB\Browser;

use CB\Objects;
use CB\Util;
use CB\Solr;

class SearchRouter
{
    protected $inputParams = null;
    protected $params = null;

    public function __construct()
    {
        $this->genericObj = new Objects\Object();
        $this->search = new \CB\Search();
    }
    public function search($p)
    {

        /*
        array(9) {
          ["start"]=> int(0)
          ["limit"]=> int(50)
          ["sort"]=> string(4) "case"
          ["dir"]=> string(3) "ASC"
          ["facets"]=> string(7) "general"
          ["path"]=> string(1) "/"
          ["descendants"]=> bool(false)
          ["template_id"]=> int(5847)
          ["data"]=> array(2) {
            ["date_block"]=> array(1) {
              ["childs"]=> array(0) {
              }
            }
            ["search_date_field"]=> array(2) {
              ["value"]=> int(10616)
              ["childs"]=> array(2) {
                ["search_date_start"]=> string(19) "2013-11-11T00:00:00"
                ["search_date_end"]=> NULL
              }
            }
          }
        }

         */
        $this->inputParams = $p;
        $this->genericObj->setData($this->inputParams);
        $this->prepareParams();

        $rez = $this->search->query($this->params);
        foreach ($rez['data'] as &$r) {
            $r['nid'] = $r['id'];
            unset($r['id']);
        }

        return $rez;
    }

    protected function prepareParams()
    {

        // collect standart params
        $this->params = array_intersect_key(
            $this->inputParams,
            array(
                'start' => 1
                ,'limit' => 1
                ,'sort' => 1
                ,'dir' => 1
                ,'facets' => 1
                ,'path' => 1
                ,'rows' => 1
                ,'descendants' => 1
                ,'template_id' => 1
                ,'filters' => 1
            )
        );

        //collect params from [data] as defined according to template definitions
        if (empty($this->inputParams['data'])) {
            return;
        }

        $ld = $this->genericObj->getAssocLinearData();
        $template = $this->genericObj->getTemplate();
        if (empty($template)) {
            throw new \Exception("SarchRouter Error: No template found for given params", 1);
        }

        foreach ($ld as $fn => $values) {
            //we'll analize for now just first value for a field (duplicated fields are not processed)
            $value = @$values[0];

            if (empty($value)) {
                continue;
            }
            $field = $template->getField($fn);
            if (!empty($field['solr_column_name'])) {
                $this->addParam($field, $value);
            }
        }
    }

    protected function addParam($fieldConfig, $compositeValue)
    {
        $solrValue = '';
        switch ($fieldConfig['type']) {
            case 'int':
            case 'bool':
            case 'checkbox':
            case 'combo':
            case 'popuplist':
            case '_objects':
                $solrValue = $this->formatInts($compositeValue);
                break;
            case 'date':
            case 'datetime':
                $solrValue = $this->formatDates($compositeValue);
                break;
            case '_sex':
            case '_language':
            case 'html':
            case 'text':
            default:
                $solrValue = $this->formatStrings($compositeValue);
                break;
        }
        if (!empty($solrValue)) {
            $this->params['fq'][] =
                ((@$compositeValue['info'] == 'NOT') ? '!': '').
                $fieldConfig['solr_column_name'].':'.$solrValue;
        }
    }

    protected function formatInts($compositeValue)
    {
        $values = Util\ToNumericArray($compositeValue['value']);
        if (empty($values)) {
            return null;
        }

        if (sizeof($values) == 1) {
            return $values[0];
        }

        $joinOperator =
            in_array($compositeValue['info'], array('AND', 'OR'))
            ? $compositeValue['info']
            : 'AND';

        return '['.implode(' '.$joinOperator.' ', $values).']';

    }

    protected function formatDates($compositeValue)
    {
        return Util\dateMysqlToISO($compositeValue['value']);
    }

    protected function formatStrings($compositeValue)
    {
        return Solr\Client::escapeLuceneChars($compositeValue['value']);
    }
}
