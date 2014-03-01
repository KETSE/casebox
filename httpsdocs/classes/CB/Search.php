<?php
namespace CB;

class Search extends Solr\Client
{
    /*when requested to sort by a field the other convenient sorting field
    can be used designed for sorting. Used for string fields. */
    public $replaceSortFields = array('nid' => 'id', 'name' => 'sort_name', 'path' => 'sort_path');
    public $acceptableSortFields = array(
         'id'
         ,'name'
         ,'sort_name'
         ,'path'
         ,'sort_path'
         ,'size'
         ,'date'
         ,'date_end'
         ,'importance'
         ,'completed'
         ,'category_id'
         ,'status'
         ,'oid'
         ,'cid'
         ,'uid'
         ,'cdate'
         ,'udate'
         ,'case'
     );
    protected $facetsSetManually = false;

    public function query($p)
    {
        $this->results = false;
        $this->inputParams = $p;
        $this->facetsSetManually = (
            isset($p['facet']) ||
            isset($p['facet.field']) ||
            isset($p['facet.query'])
        );
        $this->prepareParams();
        $this->connect();
        $this->executeQuery();
        $this->processResult();

        return $this->results;

    }

    private function prepareParams()
    {
        $p = &$this->inputParams;
        /* initial parameters */
        $this->query = empty($p['query'])
            ? ''
            : $this->escapeLuceneChars($p['query']);
        $this->start = empty($p['start'])? 0 : intval($p['start']);
        $this->rows = isset($p['rows']) ? intval($p['rows']) : \CB\CONFIG\MAX_ROWS;

        $fq = array('dstatus:0'); //by default filter not deleted nodes

        $this->params = array(
            'defType' => 'dismax'
            ,'q.alt' => '*:*'
            ,'qf' => "name content^0.5"
            ,'tie' => '0.1'
            ,'fl' => "id, pid, path, name, template_type, subtype, system, ".
                "size, date, date_end, oid, cid, cdate, uid, udate, case_id, acl_count, ".
                "case, template_id, user_ids, status, category_id, importance, completed, versions"
            ,'sort' => 'ntsc asc'
        );
        /* initial parameters */

        if (!empty($p['dstatus'])) {
            $fq = array('dstatus:'.intval($p['dstatus']));
        }
        if (!empty($p['fq'])) {
            if (!is_array($p['fq'])) {
                $p['fq'] = array($p['fq']);
            }
            $fq = array_merge($fq, $p['fq']);
        }

        if (isset($p['system'])) {
            $fq[] = 'system:'.$p['system'];
        } else {
            $fq[] = 'system:[0 TO 1]';
        }

        /* set custom field list if specified */
        if (!empty($p['fl'])) {
            $this->params['fl'] = $p['fl'];
        }

        /*analize sort parameter (ex: status asc,date_end asc)/**/
        if (isset($p['sort'])) {
            $sort = array();
            if (!is_array($p['sort'])) {
                $sort = array($p['sort'] => empty($p['dir']) ? 'asc' : strtolower($p['dir']) );
            } else {
                foreach ($p['sort'] as $s) {
                    $s = explode(' ', $s);
                    $sort[$s[0]] = empty($s[1]) ? 'asc' : strtolower($s[1]);
                }
            }
            foreach ($sort as $f => $d) {
                if (isset($this->replaceSortFields[$f])) {
                    $f = $this->replaceSortFields[$f]; // replace with convenient sorting fields if defined
                }
                if (!in_array($f, $this->acceptableSortFields)) {
                    continue;
                }

                $this->params['sort'] .= ",$f $d";
            }
        } else {
            $this->params['sort'] .= ', sort_name asc';//, subtype asc
        }

        /* adding additional query filters */

        /* assign security sets to filters */

        if (!Security::isAdmin()) {
            $sets = Security::getSecuritySets();
            if (empty($sets)) {
                $sets = array(0);
            }
            $fq[] = 'security_set_id:('.implode(' OR ', $sets).') OR oid:'.$_SESSION['user']['id'];
        }
        /* end of assign security sets to filters */

        if (!empty($p['pid'])) {
            $ids = Util\toNumericArray($p['pid']);
            if (!empty($ids)) {
                $fq[] = 'pid:('.implode(' OR ', $ids).')';
            }
        }
        if (!empty($p['ids'])) {
            $ids = Util\toNumericArray($p['ids']);
            if (!empty($ids)) {
                $fq[] = 'id:('.implode(' OR ', $ids).')';
            }
        }
        if (!empty($p['pids'])) {
            $ids = Util\toNumericArray($p['pids']);
            if (!empty($ids)) {
                $fq[] = 'pids:('.implode(' OR ', $ids).')';
            }
        }
        if (!empty($p['types'])) {
            if (!is_array($p['types'])) {
                $p['types'] = explode(',', $p['types']);
            }
            for ($i=0; $i < sizeof($p['types']); $i++) {
                switch ($p['types'][$i]) {
                    case 'folder':
                        $p['types'][$i] = 1;
                        break;
                    case 'link':
                        $p['types'][$i] = 2;
                        break;
                    case 'case':
                        $p['types'][$i] = 3;
                        break;
                    case 'object':
                        $p['types'][$i] = 4;
                        break;
                    case 'file':
                        $p['types'][$i] = 5;
                        break;
                    case 'task':
                        $p['types'][$i] = 6;
                        break;
                    case 'event':
                        $p['types'][$i] = 7;
                        break;
                    default: $p['types'][$i] = intval($p['types'][$i]);
                }
            }
            // $ids = Util\toNumericArray($p['types']);
            if (!empty($p['types'])) {
                $fq[] = 'type:('.implode(' OR ', $p['types']).')';
            }
        }

        if (!empty($p['templates'])) {
            $ids = Util\toNumericArray($p['templates']);
            if (!empty($ids)) {
                $fq[] = 'template_id:('.implode(' OR ', $ids).')';
            }
        }
        if (!empty($p['template_types'])) {
            if (!is_array($p['template_types'])) {
                $p['template_types'] = explode(',', $p['template_types']);
            }
            if (!empty($p['template_types'])) {
                $fq[] = 'template_type:("'.implode('" OR "', $p['template_types']).'")';
            }
        }

        if (isset($p['folders']) && !empty($GLOBALS['folder_templates'])) {
            if ($p['folders']) {
                $fq[] = 'template_type:("'.implode('" AND "', $GLOBALS['folder_templates']).'")';
            } else {
                $fq[] = '!template_id:('.implode(' OR ', $GLOBALS['folder_templates']).')';
            }
        }

        if (!empty($p['tags'])) {
            $ids = Util\toNumericArray($p['tags']);
            if (!empty($ids)) {
                $fq[] = 'sys_tags:('.implode(' OR ', $ids).')';
            }
        }

        if (!empty($p['dateStart'])) {
            $fq[] = 'date:['.$p['dateStart'].' TO '.$p['dateEnd'].']';
        }

        $this->params['fq'] = $fq;
        /* end of adding additional query filters */

        /* setting highlight if query parrameter is present /**/
        if (!empty($this->query)) {
            $this->params['hl'] = 'true';
            $this->params['hl.fl'] = 'name,content';
            $this->params['hl.simple.pre'] = '<em class="hl">';
            $this->params['hl.simple.post'] = '</em>';
            $this->params['hl.usePhraseHighlighter'] = 'true';
            $this->params['hl.highlightMultiTerm'] = 'true';
            $this->params['hl.fragsize'] = '256';
        }

        $this->facets = array();
        if (!$this->facetsSetManually) {
            $path = Cache::get('current_path');
            if (!empty($path)) {
                $lastNode = $path[sizeof($path) -1];
                $this->facets = $lastNode->getFacets();
            }
        }

        $this->prepareFacetsParams();
        $this->setFilters();
    }

    private function setFilters()
    {
        if ($this->facetsSetManually) {
            return;
        }

        foreach ($this->facets as $facet) {
            $f = $facet->getFilters($this->inputParams);
            if (!empty($f['fq'])) {
                $this->params['fq'] = array_merge($this->params['fq'], $f['fq']);
            }
        }
    }

    private function prepareFacetsParams()
    {
        $facetParams = array();
        if ($this->facetsSetManually) {
            if (!empty($this->inputParams['facet.field'])) {
                $facetParams['facet.field'] = $this->inputParams['facet.field'];
            }
            if (!empty($this->inputParams['facet.query'])) {
                $facetParams['facet.query'] = $this->inputParams['facet.query'];
            }
        } else {
            foreach ($this->facets as $facet) {
                $fp = $facet->getSolrParams();
                if (!empty($fp['facet.field'])) {
                    if (empty($facetParams['facet.field'])) {
                        $facetParams['facet.field'] = array();
                    }
                    $facetParams['facet.field'] = @array_merge($facetParams['facet.field'], $fp['facet.field']);
                } elseif (!empty($fp['facet.query'])) {
                    if (empty($facetParams['facet.query'])) {
                        $facetParams['facet.query'] = array();
                    }
                    $facetParams['facet.query'] = @array_merge($facetParams['facet.query'], $fp['facet.query']);
                } elseif (!empty($fp['facet.pivot'])) {
                    if (empty($facetParams['facet.pivot'])) {
                        $facetParams['facet.pivot'] = array();
                    }
                    $facetParams['facet.pivot'] = @array_merge($facetParams['facet.pivot'], $fp['facet.pivot']);
                }
            }
        }

        if (!empty($facetParams)) {
            $facetParams['facet'] = 'true';
            if (empty($facetParams['facet.mincount'])) {
                $facetParams['facet.mincount'] = 1;
            }
        }

        $this->params = array_merge($this->params, $facetParams);
    }

    private function executeQuery()
    {
        try {
            $eventParams = array(
                'class' => &$this
                ,'query' => &$this->query
                ,'start' => &$this->start
                ,'rows' => &$this->rows
                ,'params' => &$this->params
                ,'inputParams' => &$this->inputParams
            );
            \CB\fireEvent('beforeSolrQuery', $eventParams);
            $this->results = $this->search(
                $this->escapeLuceneChars($this->query),
                $this->start,
                $this->rows,
                $this->params
            );
        } catch ( \Exception $e ) {
            throw new \Exception("An error occured: \n\n {$e->__toString()}");
        }
    }

    private function processResult()
    {
        $rez = array( 'total' => $this->results->response->numFound, 'data' => array() );
        if (isDebugHost()) {
            $rez['search'] = array(
                'query' => $this->query
                ,'start' => $this->start
                ,'rows' => $this->rows
                ,'params' => $this->params
                ,'inputParams' => $this->inputParams
            );
        }
        $sr = &$this->results;
        foreach ($sr->response->docs as $d) {
            $rd = array();
            foreach ($d as $fn => $fv) {
                $rd[$fn] = is_array($fv) ? implode(',', $fv) : $fv;
            }
            if (!empty($sr->highlighting)) {
                if (!empty($sr->highlighting->{$rd['id']}->{'name'})) {
                    $rd['hl'] = $sr->highlighting->{$rd['id']}->{'name'}[0];
                }
                if (!empty($sr->highlighting->{$rd['id']}->{'content'})) {
                    $rd['content'] = $sr->highlighting->{$rd['id']}->{'content'}[0];
                }
            }
            $rez['data'][] = $rd;
        }
        $rez = array_merge($rez, $this->processResultFacets());

        $eventParams = array(
            'result' => &$rez
            ,'params' => &$this->params
            ,'inputParams' => &$this->inputParams
        );

        \CB\fireEvent('solrQuery', $eventParams);

        $this->results = $rez;
    }

    private function processResultFacets()
    {
        if ($this->facetsSetManually) {
            return array(
                'facets' => $this->results->facet_counts
            );
        }

        $rez = array();
        foreach ($this->facets as $facet) {
            $facet->loadSolrResult($this->results->facet_counts);
            $fr = $facet->getClientData();
            if (!empty($fr)) {
                $idx = empty($fr['index'])
                    ? 'facets'
                    : $fr['index'];

                $rez[$idx][$fr['f']] = $fr;
            }
        }

        return $rez;
    }

    public function analizeTreeTagsFacet($values, &$rez)
    {
        $groups = defined('CB\\CONFIG\\TAGS_FACET_GROUPING') ? CONFIG\TAGS_FACET_GROUPING : 'pids';
        $ids = array();
        foreach ($values as $k => $v) {
            $ids[] = $k;
        }

        if (empty($ids)) {
            return false;
        }

        $names = array();
        /* selecting names*/
        $res = DB\dbQuery(
            'SELECT t.id
                 , t.name
            FROM tree t
            WHERE t.id IN ('.implode(', ', $ids).')'
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $names[$r['id']] = L\getTranslationIfPseudoValue($r['name']);
        }
        $res->close();
        /* end of selecting names*/

        switch ($groups) {
            case 'all':
                foreach ($values as $k => $v) {
                    $rez['tree_tags']['items'][$k] = array('name' => $names[$k], 'count' => $v);
                }
                break;
            case 'pids':
                $res = DB\dbQuery(
                    'SELECT t.id
                         , t.pid
                         , p.name
                    FROM tree t
                    JOIN tree p ON t.pid = p.id
                    WHERE t.id IN ('.implode(', ', $ids).')'
                ) or die(DB\dbQueryError());

                while ($r = $res->fetch_assoc()) {
                    $rez['ttg_'.$r['pid']]['f'] = 'tree_tags';
                    $rez['ttg_'.$r['pid']]['name'] = L\getTranslationIfPseudoValue($r['name']);
                    $rez['ttg_'.$r['pid']]['items'][$r['id']] =  array('name' => $names[$r['id']], 'count' => $values->{$r['id']});
                }
                $res->close();
                break;
            default:
                $res = DB\dbQuery(
                    'SELECT t.id
                         , t.pid
                         , p.name
                    FROM tree t
                    JOIN tree p ON t.pid = p.id
                    WHERE t.id IN ('.implode(', ', $ids).')
                        AND p.id IN('.$groups.')'
                ) or die(DB\dbQueryError());

                while ($r = $res->fetch_assoc()) {
                    $rez['ttg_'.$r['pid']]['f'] = 'tree_tags';
                    $rez['ttg_'.$r['pid']]['name'] = $r['name'];
                    $rez['ttg_'.$r['pid']]['items'][$r['id']] = array('name' => $names[$r['id']], 'count' => $values->{$r['id']});
                    unset($values->{$r['id']});
                }
                $res->close();

                if (!empty($values)) {
                    foreach ($values as $k => $v) {
                        if (isset( $names[$k] )) {
                            $rez['tree_tags']['items'][$k] = array('name' => $names[$k], 'count' => $v);
                        }
                    }
                }
                break;
        }

        return true;
    }
}
