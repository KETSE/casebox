<?php
namespace CB;

use CB\Cache;
use CB\Util;
use CB\User;

class Search extends Solr\Client
{
    public static $defaultFields = array(
        'id', 'pid', 'name', 'path', 'template_type', 'target_id', 'system',
        'size', 'date', 'date_end', 'oid', 'cid', 'cdate', 'uid', 'udate', 'comment_user_id', 'comment_date',
        'case_id', 'acl_count', 'case', 'template_id', 'user_ids', 'task_u_assignee', 'status',
        'task_status', 'task_d_closed', 'versions', 'ntsc'
    );

    /*when requested to sort by a field the other convenient sorting field
    can be used designed for sorting. Used for string fields. */
    protected $replaceSortFields = array(
        'nid' => 'id'
        ,'name' => 'sort_name'
        // ,'path' => 'pid'
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

        $this->rows = isset($p['rows'])
            ? intval($p['rows'])
            : User::getGridMaxRows();

        $this->start = empty($p['start'])
            ? (empty($p['page'])
                ? 0
                : $this->rows * (intval($p['page']) -1)
            )
            : intval($p['start']);

        //by default filter not deleted nodes
        $fq = array('dstatus:0');

        $this->params = array(
            'defType' => 'dismax'
            ,'q.alt' => '*:*'
            ,'qf' => "name content^0.5"
            ,'tie' => '0.1'
            ,'fl' => implode(',', static::$defaultFields)
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
            if (is_numeric($p['system']) || preg_match('/^\[\d+ TO \d+\]$/', $p['system'])) {
                $fq[] = 'system:'.$p['system'];
            }
        } else {
            $fq[] = 'system:[0 TO 1]';
        }

        /* set custom field list if specified */
        if (!empty($p['fl'])) {
            //filter wrong fieldnames
            $filteredNames = array();
            $a = explode(',', $p['fl']);
            foreach ($a as $fn) {
                $fn = trim($fn);
                if (!preg_match('/^[a-z_0-9]+$/i', $fn)) {
                    continue;
                }
                $filteredNames[] = $fn;
            }

            //add target_id field
            if (!in_array('target_id', $filteredNames)) {
                $filteredNames[] = 'target_id';
            }

            //pid & template_id are also needed for shortcuts
            if (!in_array('pid', $filteredNames)) {
                $filteredNames[] = 'pid';
            }
            if (!in_array('template_id', $filteredNames)) {
                $filteredNames[] = 'template_id';
            }

            $this->params['fl'] = implode(',', $filteredNames);
        }

        /*analize sort parameter (ex: status asc,date_end asc)/**/
        if (!empty($p['strictSort'])) {
            $this->params['sort'] = $p['strictSort'];

        } else {
            $sort = array('order' => 'asc');

            if (isset($p['sort'])) {
                //clear sorting array if sorting not empty
                if (!empty($p['sort'])) {
                    $sort = array();
                }

                if (!is_array($p['sort'])) {
                    $sort[$p['sort']] = empty($p['dir'])
                        ? 'asc'
                        : strtolower($p['dir']);
                } else {
                    foreach ($p['sort'] as $s) {
                        if (is_array($s)) {
                            $sort[$s['property']] = empty($s['direction'])
                                ? 'asc'
                                : strtolower($s['direction']);
                        } else {
                            $s = explode(' ', $s);
                            $sort[$s[0]] = empty($s[1])
                                ? 'asc'
                                : strtolower($s[1]);
                        }
                    }
                }
            } else {
                $sort['sort_name'] = 'asc';
            }

            foreach ($sort as $k => $v) {
                $this->params['sort'] .= ",$k $v";
            }
        }

        //validate formed sort param
        $sort = explode(',', $this->params['sort']);
        $filteredSort = array();
        foreach ($sort as $sf) {
            $a = explode(' ', $sf);

            //skip elements with more than one space
            if (sizeof($a) !== 2) {
                continue;
            }

            //skip elements with unknown sorting order string
            if (!in_array($a[1], array('asc', 'desc'))) {
                continue;
            }

            //skip strange field_names
            if (!preg_match('/^[a-z_0-9]+$/i', $a[0])) {
                continue;
            }
            $filteredSort[] = implode(' ', $a);
        }
        $this->params['sort'] = implode(', ', $filteredSort);

        /* adding additional query filters */

        // assign security sets to filters
        // dont check if 'skipSecurity = true'
        // it's used in Objects fields where we show all nodes
        // without permission filtering
        if (!Security::isAdmin() and !@$p['skipSecurity']) {
            $pids = false;
            if (!empty($p['pid'])) {
                $pids = $p['pid'];
            } elseif (!empty($p['pids'])) {
                $pids = $p['pids'];
            }

            $sets = Security::getSecuritySets(false, 5, $pids);
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

            $filteredNames = array();
            foreach ($p['template_types'] as $tt) {
                $tt = trim($tt);
                if (!preg_match('/^[a-z]+$/i', $tt)) {
                    continue;
                }
                $filteredNames[] = $tt;
            }

            if (!empty($filteredNames)) {
                $fq[] = 'template_type:("'.implode('" OR "', $filteredNames).'")';
            }
        }

        $folderTemplates = Config::get('folder_templates');
        if (isset($p['folders']) && !empty($folderTemplates)) {
            if ($p['folders']) {
                $fq[] = 'template_type:("'.implode('" AND "', $folderTemplates).'")';
            } else {
                $fq[] = '!template_id:('.implode(' OR ', $folderTemplates).')';
            }
        }

        if (!empty($p['dateStart'])) {
            $fq[] = 'date:[' .
                Util\dateMysqlToISO($p['dateStart']) .
                ' TO ' .
                (empty($p['dateEnd'])
                    ? '*'
                    : Util\dateMysqlToISO($p['dateEnd'])
                ) .
                ']';
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

            if (!empty($this->inputParams['facet.range'])) {
                $facetParams['facet.range'] = $this->inputParams['facet.range'];
            }
            if (!empty($this->inputParams['facet.range.start'])) {
                $facetParams['facet.range.start'] = $this->inputParams['facet.range.start'];
            }
            if (!empty($this->inputParams['facet.range.end'])) {
                $facetParams['facet.range.end'] = $this->inputParams['facet.range.end'];
            }
            if (!empty($this->inputParams['facet.range.gap'])) {
                $facetParams['facet.range.gap'] = $this->inputParams['facet.range.gap'];
            }

            if (!empty($this->inputParams['facet.sort'])) {
                $facetParams['facet.sort'] = $this->inputParams['facet.sort'];
            }
            if (!empty($this->inputParams['facet.missing'])) {
                $facetParams['facet.missing'] = 'on';
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

    /**
     * analize sort param and replace sort fields if needed
     * @return void
     */
    protected function replaceSortFields()
    {
        if (empty($this->params['sort'])) {
            return;
        }

        $sort = is_array($this->params['sort'])
            ? $this->params['sort']
            : explode(',', $this->params['sort']);
        foreach ($sort as $k => $el) {
            $el = trim($el);
            list($f, $s) = explode(' ', $el);
            if (!empty($this->replaceSortFields[$f])) {
                $sort[$k] = $this->replaceSortFields[$f].' '.$s;
            }
        }

        $this->params['sort'] = implode(', ', $sort);
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

            $this->replaceSortFields();

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
        $rez = array(
            'total' => $this->results->response->numFound,
            'data' => array()
        );

        if (IS_DEBUG_HOST) {
            $rez['search'] = array(
                'query' => $this->query
                ,'start' => $this->start
                ,'rows' => $this->rows
                ,'params' => $this->params
                ,'inputParams' => $this->inputParams
            );
        }

        $sr = &$this->results;
        $shortcuts = array();

        foreach ($sr->response->docs as $d) {
            $rd = array();
            foreach ($d as $fn => $fv) {
                $rd[$fn] = is_array($fv) ? implode(',', $fv) : $fv;
            }

            $rez['data'][] = &$rd;

            //check if shortcut
            if (!empty($rd['target_id'])) {
                $shortcuts[$rd['target_id']] = &$rd;
            }
            unset($rd);
        }

        $this->updateShortcutsData($shortcuts);

        $this->setPaths($rez['data']);

        //add highlights
        if (!empty($sr->highlighting)) {
            foreach ($rez['data'] as &$d) {
                $id = empty($d['target_id'])
                    ? $d['id']
                    : $d['target_id'];

                if (!empty($sr->highlighting->{$id}->{'name'})) {
                    $d['hl'] = $sr->highlighting->{$id}->{'name'}[0];
                }
                if (!empty($sr->highlighting->{$id}->{'content'})) {
                    $d['content'] = $sr->highlighting->{$id}->{'content'}[0];
                }
            }
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

    private function updateShortcutsData(&$shortcutsArray)
    {
        if (empty($shortcutsArray)) {
            return;
        }

        $p = &$this->params;
        $ids = array_keys($shortcutsArray);

        $sr = $this->search(
            $this->escapeLuceneChars(''),
            0,
            1000,
            array(
                'defType' => $p['defType']
                ,'fl' => $p['fl']
                ,'q.alt' => $p['q.alt']
                ,'fq' => array(
                    'id:(' . implode(' OR ', $ids) . ')'
                )
            )
        );

        $shortcuts = array();

        foreach ($sr->response->docs as $d) {
            $oldProps = $shortcutsArray[$d->id];
            $ref = &$shortcutsArray[$d->id];

            foreach ($d as $fn => $fv) {
                $ref[$fn] = is_array($fv) ? implode(',', $fv) : $fv;
            }
            //set element id to original so all actions will be made on shortcut by default
            //only opening will check if this object data has a target id
            $ref['id'] = $oldProps['id'];
        }
    }

    /**
     * update path property for an items array
     * @param array $dataArray
     */
    public static function setPaths(&$dataArray)
    {
        if (!is_array($dataArray)) {
            return;
        }

        //collect distinct paths and ids
        $paths = array();
        $distinctIds = array();

        foreach ($dataArray as &$item) {
            if (isset($item['path']) && !isset($paths[$item['path']])) {
                $path = Util\toNumericArray($item['path'], '/');
                if (!empty($path)) {
                    $paths[$item['path']] = $path;
                    $distinctIds = array_merge($distinctIds, $path);
                }
            }
        }

        //get names for distinct ids
        if (!empty($distinctIds)) {
            $names = static::getObjectNames($distinctIds);

            //replace ids with names
            foreach ($paths as $path => $elements) {
                for ($i=0; $i < sizeof($elements); $i++) {
                    if (isset($names[$elements[$i]])) {
                        $elements[$i] = $names[$elements[$i]];
                    }
                }
                array_unshift($elements, '');
                array_push($elements, '');
                $paths[$path] = implode('/', $elements);
            }

            //replace paths in objects data
            foreach ($dataArray as &$item) {
                if (isset($item['path'])) {
                    $item['path'] = @$paths[$item['path']];
                }
            }
        }
    }

    /**
     * method to get object names from solr
     * Multilanguage plugin works also
     *
     * @param  array | string $ids
     * @return array          associative array of names per id
     */
    public static function getObjectNames($ids)
    {
        $objectNames = Cache::get('objectNames');

        $rez = array();
        $getIds = array();

        $ids = Util\toNumericArray($ids);

        foreach ($ids as $id) {
            if (isset($objectNames[$id])) {
                $rez[$id] = $objectNames[$id];
            } else {
                $getIds[] = $id;
            }
        }

        if (!empty($getIds)) {
            $newData = static::getObjects($getIds);

            foreach ($newData as $k => $v) {
                $objectNames[$k] = $v['name'];
                $rez[$k] = $v['name'];
            }

            Cache::set('objectNames', $objectNames);
        }

        return $rez;
    }

    /**
     * method to get multiple object properties from solr
     * Multilanguage plugin works also
     *
     * @param  array | string $ids
     * @param  string         $fieldList
     * @return array          associative array of properties per id
     */
    public static function getObjects($ids, $fieldList = 'id,name')
    {
        $rez = array();
        $ids = Util\toNumericArray($ids);
        if (empty($ids)) {
            return $rez;
        }

        $chunks = array_chunk($ids, 200);

        //connect or get solr service connection
        $conn = Cache::get('solr_service');

        if (empty($conn)) {
            $conn = new Solr\Service();

            Cache::set('solr_service', $conn);
        }

        //execute search
        try {
            foreach ($chunks as $chunk) {
                $params = array(
                    'defType' => 'dismax'
                    ,'q.alt' => '*:*'
                    ,'fl' => $fieldList
                    ,'fq' => array(
                        'id:(' . implode(' OR ', $chunk). ')'
                    )
                );

                $inputParams = array(
                    'ids' => $chunk
                );

                $eventParams = array(
                    'params' => &$params
                    ,'inputParams' => &$inputParams
                );

                \CB\fireEvent('beforeSolrQuery', $eventParams);

                $searchRez = $conn->search(
                    '',
                    0,
                    200,
                    $params
                );

                if (!empty($searchRez->response->docs)) {
                    foreach ($searchRez->response->docs as $d) {
                        $rd = array();
                        foreach ($d as $fn => $fv) {
                            $rd[$fn] = $fv;
                        }
                        $rez[$d->id] = $rd;
                    }
                }

                $eventParams['result'] = array(
                    'data' => &$rez
                );
                \CB\fireEvent('solrQuery', $eventParams);
            }
        } catch ( \Exception $e ) {
            throw new \Exception("An error occured in getObjectNames: \n\n {$e->__toString()}");
        }

        return $rez;
    }
}
