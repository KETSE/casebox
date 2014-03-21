<?php
namespace CB\TreeNode;

use CB\Util;

class SearchResults extends DBNode
{
    protected function acceptedPath()
    {
        $p = &$this->path;

        if ((!empty($this->lastNode) &&
            ($this->lastNode instanceof Dbnode) &&
            (get_class($this->lastNode) != get_class($this))
        )
        ) {
            $data = $this->lastNode->getData();
            if (!empty($this->requestParams['search']) || (@$data['template_type'] == 'search')) {
                return true;
            }
        }

        return false;
    }

    public function getChildren(&$pathArray, $requestParams)
    {
        $rez = array();
        $this->path = $pathArray;
        $this->lastNode = @$pathArray[sizeof($pathArray) - 1];
        $this->requestParams = $requestParams;

        if (!$this->acceptedPath()) {
            return;
        }
        // creating search object
        $so = new \CB\Objects\Object();
        if (!empty($requestParams['search']['data'])) {
            // searching from a search form
            $so->setData($requestParams['search']);
        } else {
            $searchId = $this->lastNode->id;
            if (!empty($requestParams['search']['id']) && is_numeric($requestParams['search']['id'])) {
                $searchId = $requestParams['search']['id'];
            }
            // executing a saved search
            $so->load($searchId);
        }

        $p = $this->getSearchParams($so);
        $s = new \CB\Search();
        $rez = $s->query($p);

        return $rez;
    }

    protected function getSearchParams(&$searchObject)
    {
        $tpl = $searchObject->getTemplate();
        $rez = $tpl->getData()['cfg'];

        if (empty($rez['fq'])) {
            $rez['fq'] = array();
        }

        $ld = $searchObject->getLinearData();
        foreach ($ld as $v) {
            $condition = $this->adjustCondition($tpl->getField($v['name']), $v);
            if (!empty($condition)) {
                $rez['fq'][] = $condition;
            }
        }

        return $rez;
    }

    protected function adjustCondition($templateField, $value)
    {
        $rez = '';
        if (empty($value['value'])) {
            return $rez;
        }

        $f = $value['name'];
        $v = $value['value'];

        switch ($templateField['type']) {
            case 'int':
            case 'float':
                if (is_numeric($v)) {
                    switch ($value['cond']) {
                        case '<=':
                            $rez = $f.':[* TO '.$v.']';
                            break;
                        case '>=':
                            $rez = $f.':['.$v.' TO *]';
                            break;
                        case '!=':
                            $rez = '-'.$f.':'.$v;
                            break;

                        case '=':
                        default:
                            $rez = $f.':'.$v;
                            break;
                    }

                } else {
                    $a = explode('..', $v);
                    if (sizeof($a) == 2) {
                        $a[0] = trim($a[0]);
                        $a[1] = trim($a[1]);
                        $rez = $f.':['.$a[0].' TO '.$a[1].']';
                    }
                }
                break;
            case 'date':
            case 'datetime':
                $a = explode('..', $v);
                if (sizeof($a) == 2) {
                    $a[0] = trim($a[0]);
                    $a[1] = trim($a[1]);
                    $rez = $f.':['.$this->toSolrDate($a[0]).' TO '.$this->toSolrDate($a[1]).']';
                } else {
                    switch ($value['cond']) {
                        case '<=':
                            $rez = $f.':[* TO '.$this->toSolrDate($v).']';
                            break;
                        case '>=':
                            $rez = $f.':['.$this->toSolrDate($v).' TO *]';
                            break;
                        case '!=':
                            $rez = '-'.$f.':'.$this->toSolrDate($v);
                            break;

                        case '=':
                        default:
                            $rez = $f.':'.$this->toSolrDate($v);
                            break;
                    }
                }
                /*cond = [
                    {id: '=', name: '='}
                    ,{id: '<', name: '<'}
                    ,{id: '>', name: '>'}
                    ,{id: '<=', name: '<='}
                    ,{id: '>=', name: '>='}
                    ,{id: '!=', name: '!='}
                ];
                // custom value formats (date1 .. date2, )/**/
                break;

            case '_objects':
            case 'combo':
                $v = Util\toNumericArray($v);
                if (!empty($v)) {
                    switch ($value['cond']) {
                        case '<=':
                            $rez = $f.':('.implode(' OR ', $v).')';
                            break;
                        case '>=':
                            $rez = $f.':('.implode(' AND ', $v).')';
                            break;
                        case '!=':
                            $rez = '-'.$f.':('.implode(' OR ', $v).')';
                            break;

                        case '=':
                        default:
                            $rez = $f.':('.implode(' AND ', $v).') AND -'.$f.':[* TO *]';
                            break;
                    }
                }
                break;
            case '_sex':
                $v = '"'.$v.'"';
                switch ($value['cond']) {
                    case '!=':
                        $rez = '-'.$f.':'.$v;
                        break;

                    case '=':
                    default:
                        $rez = $f.':'.$v;
                        break;
                }
                /*cond = [
                    {id: '<', name: 'contains any'}
                    ,{id: '>', name: 'contains all'}
                    ,{id: '=', name: 'equal'}
                    ,{id: '!=', name: 'not equal'}
                ];
                //= (exact match), contains any, contains all, does not contain any, does not contain all/**/
                break;

            case '_auto_title':
            case 'varchar':
            case 'text':
            case 'memo':
            case 'html':
                $rez = $f.':'.$v;

                /*cond = [
                    {id: 'contain', name: 'contain'}
                    ,{id: 'start', name: 'start with'}
                    ,{id: 'end', name: 'end with'}
                    ,{id: 'not', name: 'does not contain'}
                    ,{id: '=', name: 'equal'}
                    ,{id: '!=', name: 'not equal'}
                ];/**/
                break;

            case 'checkbox':
                $rez = (($value['cond'] == '=')
                    ? ''
                    : '-').$f.':'.$v;
                /*cond = [
                    {id: '=', name: 'is'}
                    ,{id: '!=', name: 'is not'}
                ];/**/
                break;
        }

        return $rez;
    }

    protected function toSolrDate($date)
    {
        if (empty($date)) {
            return '*';
        }
        if (Util\validISO8601Date($date)) {
            $date = date(DATE_ISO8601, strtotime($date));
        }

        return $date;
    }
}
