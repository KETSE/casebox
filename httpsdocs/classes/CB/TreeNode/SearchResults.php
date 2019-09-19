<?php
namespace CB\TreeNode;

use CB\Util;

class SearchResults extends Dbnode
{
    /**
     * check if current class is configured to return any result for
     * given path and request params
     * @param  array   &$pathArray
     * @param  array   &$requestParams
     * @return boolean
     */
    protected function acceptedPath(&$pathArray, &$requestParams)
    {
        $lastNode = null;

        if (!empty($pathArray)) {
            $lastNode = $pathArray[sizeof($pathArray) - 1];
        }

        if ((!empty($lastNode) &&
            (get_class($lastNode) == get_class($this))
        )
        ) {
            // $data = $lastNode->getData();
            // if (!empty($this->requestParams['search']) || (@$data['template_type'] == 'search')) {
                return true;
            // }
        }

        return false;
    }

    public function getChildren(&$pathArray, $requestParams)
    {
        $rez = array();
        $this->path = $pathArray;
        $this->lastNode = @$pathArray[sizeof($pathArray) - 1];
        $this->requestParams = $requestParams;

        if (!$this->acceptedPath($pathArray, $requestParams)) {
            return;
        }

        $p = $this->getSearchParams($requestParams);

        //view is detected earlier by Browser class
        if (!empty($requestParams['view'])) {
            $p['view'] = $requestParams['view'];
        }

        //facets are obtained by browser class before collecting children
        unset($p['facets']);

        $p = array_merge($requestParams, $p);

        $s = new \CB\Search();
        $rez = $s->query($p);

        return $rez;
    }

    /**
     * get create menu for current node
     * @param  array   $rp request params
     * @return varchar menu config string
     */
    public function getCreateMenu(&$rp)
    {
        $rez = '';
        $cfg = $this->getSearchParams($rp);

        if (!empty($cfg['createMenu'])) {
            $rez = $cfg['createMenu'];
        } else {
            if (!empty($this->parent)) {
                $rez = $this->parent->getCreateMenu($rp);
            }
        }

        return $rez;
    }

    public function getNodeParam($param = 'facets')
    {
        $rez = parent::getNodeParam($param);
        if (!empty($this->config['template_id'])) {
            $rez['from'] = 'template_' . $this->config['template_id'];
        }

        return $rez;
    }

    /**
     * get view config for given view or default view if set in config
     * @param  array &$pathArray
     * @param  array &$rp        requestParams
     * @return array
     */
    public function getViewConfig(&$pathArray, &$rp)
    {
        $copyParams = array('view', 'views', 'stats');

        $sp = $this->getSearchParams($rp);

        foreach ($copyParams as $k) {
            if (isset($sp[$k])) {
                $this->config[$k] = $sp[$k];
            }
        }

        $rez = parent::getViewConfig($pathArray, $rp);

        return $rez;
    }

    /**
     * get search params for given request params
     * @param  array &$rp
     * @return array
     */
    protected function getSearchParams(&$rp) //searchObject
    {
        $rez = array();

        // creating search object
        $so = new \CB\Objects\Object();

        if (!empty($rp['search']['template_id'])) {
            // searching from a search form
            $so->setData($rp['search']);

        } else {
            // should/will be reviewed for saved searches
            $searchId = $this->lastNode->id;
            if (!empty($rp['search']['id']) && is_numeric($rp['search']['id'])) {
                $searchId = $rp['search']['id'];
            }
            // executing a saved search
            $so->load($searchId);
        }

        $t = $so->getTemplate();
        $td = $t->getData();

        // if we have a router defined in config of the search template then try to prepare search params wit it
        // otherwise use default search method
        // if (empty($td['cfg']['router'])) {
        $rez = $t->getData()['cfg'];

        @$rez['template_id'] = $so->getData()['template_id'];

        if (empty($rez['fq'])) {
            $rez['fq'] = array();
        }

        $ld = $so->getLinearData();
        foreach ($ld as $v) {
            $condition = $this->adjustCondition($t->getField($v['name']), $v);
            if (!empty($condition)) {
                $rez['fq'][] = $condition;
            }
        }

        // } else {

        if (!empty($td['cfg']['router'])) {
            $a = explode('.', $td['cfg']['router']);
            $class = str_replace('_', '\\', $a[0]);
            $class = new $class();
            $rez = $class->{$a[1]}($rp, $rez);
        }

        return $rez;
    }

    protected function adjustCondition($templateField, $value)
    {
        $rez = '';
        if (empty($value['value']) || empty($templateField['solr_column_name'])) {
            return $rez;
        }

        $f = $templateField['solr_column_name'];

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
                            $rez = $f.':[* TO "'.$this->toSolrDate($v).'"]';
                            break;
                        case '>=':
                            $rez = $f.':["'.$this->toSolrDate($v).'" TO *]';
                            break;
                        case '!=':
                            $rez = '-'.$f.':"' . $this->toSolrDate($v) . '"';
                            break;

                        case '=':
                        default:
                            $rez = $f.':"' . $this->toSolrDate($v) . '"';
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
                if (!empty($templateField['solrValuePrefix'])) {
                    for ($i = 0; $i < sizeof($v); $i++) {
                        $v[$i] = $templateField['solrValuePrefix'].$v[$i];
                    }
                }
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
                            $rez = $f.':('.implode(' AND ', $v).')'; // AND -'.$f.':[* TO *]
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
                $rez = $f.':"'.$v.'"';

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
