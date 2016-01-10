<?php
namespace CB;

/**
 * main search class for solr queries
 *
 */

use CB\Util;
use CB\User;

class Search extends Solr\Client
{

    /**
     * default field list used for queries
     * @var array
     */
    public static $defaultFields = array(
        'id', 'pid', 'name', 'path', 'template_type', 'target_id', 'system',
        'size', 'date', 'date_end', 'oid', 'cid', 'cdate', 'uid', 'udate', 'comment_user_id', 'comment_date',
        'case_id', 'acl_count', 'case', 'template_id', 'user_ids', 'task_u_assignee', 'status',
        'task_status', 'task_d_closed', 'versions', 'ntsc'
    );

    /**
     * fields that should be added aotmaticall to any query
     * @var array
     */
    protected $requiredFields = array(
            'pid'
            ,'tempalte_id'
            ,'target_id' //for shortcuts
        );

    /*when requesting sort by a field the other convenient sorting field
    can be used designed for sorting. Used for string fields. */
    protected $replaceSortFields = array(
        'nid' => 'id'
        ,'name' => 'sort_name'
    );

    /**
     * flag to detect facets or use defined from input params
     * @var boolean
     */
    protected $facetsSetManually = false;

    /**
     * query solr
     * @param  array $p [description]
     * @return array
     */
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

    /**
     * prepare input params
     * @return void
     */
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
                : $this->rows * (intval($p['page']) - 1)
            )
            : intval($p['start']);

        $this->params = array(
            'defType' => 'dismax'
            ,'q.alt' => '*:*'
            ,'qf' => "name content^0.5"
            ,'tie' => '0.1'
            ,'fl' => $this->getFieldListParam($p)
            ,'fq' => $this->getFilterQueryParam($p)
            ,'sort' => $this->getSortParam($p)
        );

        /* setting highlight if query parrameter is present /**/
        if (!empty($this->query)) {
            $this->params['hl'] = 'true';
            $this->params['hl.fl'] = 'name'; //,content
            $this->params['hl.simple.pre'] = '<em class="hl">';
            $this->params['hl.simple.post'] = '</em>';
            $this->params['hl.usePhraseHighlighter'] = 'true';
            $this->params['hl.highlightMultiTerm'] = 'true';
            $this->params['hl.fragsize'] = '256';
        }

        $this->facets = array();
        if (!$this->facetsSetManually && !empty($p['facets'])) {
            $this->facets = &$p['facets'];
        }

        $fp = $this->getFacetParams($p);
        if (!empty($fp)) {
            $this->params = array_merge($this->params, $fp);
        }

        //analize facet filters
        $this->params['fq'] = array_merge(
            $this->params['fq'],
            $this->getFacetFilters($p)
        );
    }

    /**
     * get field list from given params
     * @param  array   &$p
     * @return varchar
     */
    protected function getFieldListParam(&$p)
    {
        $rez = static::$defaultFields;

        if (!empty($p['fl'])) {
            $rez = array();

            //filter wrong fieldnames
            $a = Util\toTrimmedArray($p['fl']);
            foreach ($a as $fn) {
                if (preg_match('/^[a-z_0-9]+$/i', $fn)) {
                    $rez[] = $fn;
                }
            }

            //add required fields
            foreach ($this->requiredFields as $fn) {
                if (!in_array('target_id', $rez)) {
                    $rez[] = 'target_id';
                }

            }

            //add title field for current language
            $field = 'title_' . Config::get('user_language') . '_t';
            if (!in_array($field, $rez)) {
                $rez[] = $field;
            }
        }

        return implode(',', $rez);
    }

    /**
     * get filtering query array
     * @param  array &$p
     * @return array
     */
    protected function getFilterQueryParam(&$p)
    {
        //by default filter deleted nodes
        $fq = array('dstatus:0');

        if (!empty($p['dstatus'])) {
            $fq = array('dstatus:' . intval($p['dstatus']));
        }

        //check if fq is set and add it to result
        if (!empty($p['fq'])) {
            if (!is_array($p['fq'])) {
                $p['fq'] = array($p['fq']);
            }
            $fq = array_merge($fq, $p['fq']);
        }

        //check system param
        $sysParam = 'system:[0 TO 1]';
        if (isset($p['system'])) {
            if (is_numeric($p['system']) || preg_match('/^\[\d+ TO \d+\]$/', $p['system'])) {
                $sysParam = 'system:'.$p['system'];
            }
        }
        $fq[] = $sysParam;

        /* adding additional query filters */

        //check securitySets param
        $ss = $this->getSecuritySetsParam($p);
        if (!empty($ss)) {
            $fq[] = $ss;
        }

        //check numeric params
        $params = array(
            'pid' => 'pid'
            ,'ids' => 'id'
            ,'pids' => 'pids'
            ,'templates' => 'template_id'
        );

        foreach ($params as $param => $fn) {
            if (!empty($p[$param])) {
                $ids = Util\toNumericArray($p[$param]);
                if (!empty($ids)) {
                    $fq[] = $fn . ':('.implode(' OR ', $ids).')';
                }
            }
        }

        if (!empty($p['template_types'])) {
            $types = Util\toTrimmedArray($p['template_types']);

            $filteredTypes = array();
            foreach ($types as $tt) {
                if (preg_match('/^[a-z]+$/i', $tt)) {
                    $filteredTypes[] = $tt;
                }
            }

            if (!empty($filteredTypes)) {
                $fq[] = 'template_type:("'.implode('" OR "', $filteredTypes).'")';
            }
        }

        // $folderTemplates = Config::get('folder_templates');
        // if (isset($p['folders']) && !empty($folderTemplates)) {
        //     $fq[] = '!template_id:('.implode(' OR ', $folderTemplates).')';
        // }

        if (!empty($p['dateStart'])) {
            $range = ':[' .
                Util\dateMysqlToISO($p['dateStart']) .
                ' TO ' .
                (empty($p['dateEnd'])
                    ? '*'
                    : Util\dateMysqlToISO($p['dateEnd'])
                ) .
                ']';
            $fq[] = "date$range OR date_end$range";
        }

        return $fq;
    }

    /**
     * get assign security sets to filters
     * dont check if 'skipSecurity = true'
     * it's used in Objects fields where we show all nodes
     * without permission filtering
     * @param  array   &$p
     * @return varchar
     */
    protected function getSecuritySetsParam(&$p)
    {
        $rez = '';

        if (!Security::isAdmin() && empty($p['skipSecurity'])) {
            $pids = false;

            if (!empty($p['pid'])) {
                $pids = $p['pid'];
            } elseif (!empty($p['pids'])) {
                $pids = $p['pids'];
            }

            $sets = Security::getSecuritySets(false, 5, $pids);

            if (!empty($sets)) {
                $rez = 'security_set_id:('.implode(' OR ', $sets).') OR oid:' . User::getId();

            } else {
                //for created users that doesnt belong to any group
                //and dont have any security sets associated
                // $rez = '!security_set_id:[* TO *]';
                $rez = 'oid:' . User::getId();
            }

        }

        return $rez;
    }

    /**
     * get sort param from given params
     * @param  array   &$p
     * @return varchar
     */
    protected function getSortParam(&$p)
    {
        $rez = 'ntsc asc';
        $sort = array('ntsc' => 'asc');

        if (!empty($p['strictSort'])) {
            //if strictSort specified in imput params
            //then set it as is.
            //We'll probably remove this option in the future
            $rez = $p['strictSort'];

        } else {
            //sort by order by default
            $sort = array('order' => 'asc');

            if (isset($p['sort'])) {
                //clear sorting array if sorting not empty
                if (!empty($p['sort'])) {
                    $sort = array();
                }

                //check if sort is a string (considered a property name)
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
                $rez .= ",$k $v";
            }
        }

        $rez = $this->filterSortParam($rez);

        return $rez;
    }

    /**
     * filter a sorting string
     * @param  varchar $sort
     * @return varchar
     */
    protected function filterSortParam($sort)
    {
        $sort = Util\toTrimmedArray($sort);

        $rez = array();
        foreach ($sort as $sf) {
            $a = explode(' ', $sf);

            //skip elements with more than one space
            if (sizeof($a) == 2) {
                //skip elements with unknown sorting order string
                if (in_array($a[1], array('asc', 'desc'))) {
                    //skip strange field_names
                    if (preg_match('/^[a-z_0-9]+$/i', $a[0])) {
                        $rez[] = implode(' ', $a);
                    }
                }
            }
        }

        return implode(', ', $rez);
    }

    /**
     * analize facets param and get their filters
     * @param  array &$p
     * @return array
     */
    private function getFacetFilters(&$p)
    {
        $rez = array();
        if (!$this->facetsSetManually) {
            foreach ($this->facets as $facet) {
                $f = $facet->getFilters($p);
                if (!empty($f['fq'])) {
                    $rez = array_merge($rez, $f['fq']);
                }
            }
        }

        return $rez;
    }

    private function getFacetParams(&$p)
    {
        $rez = array();

        if ($this->facetsSetManually) {
            $copyParams = array(
                'facet.field'
                ,'facet.query'
                ,'facet.range'
                ,'facet.range.start'
                ,'facet.range.end'
                ,'facet.range.gap'
                ,'facet.sort'
                ,'facet.missing' //"on" ?
                ,'stats.field'
            );

            foreach ($copyParams as $pn) {
                if (!empty($p[$pn])) {
                    $rez[$pn] = $p[$pn];
                }
            }

        } else {
            foreach ($this->facets as $facet) {
                $fp = $facet->getSolrParams();

                $copyParams = array(
                    'facet.field'
                    ,'facet.query'
                    ,'facet.pivot'
                    ,'stats.field'
                );
                foreach ($copyParams as $pn) {
                    if (!empty($fp[$pn])) {
                        if (empty($rez[$pn])) {
                            $rez[$pn] = array();
                        }
                        $rez[$pn] = array_merge($rez[$pn], $fp[$pn]);
                    }
                }
            }
        }

        if (!empty($rez)) {
            $rez['facet'] = 'true';

            if (empty($rez['facet.mincount'])) {
                $rez['facet.mincount'] = 1;
            }

            if (!empty($rez['stats.field'])) {
                $rez['stats'] = 'true';
            }
        }

        return $rez;
    }

    /**
     * analize sort param and replace sort fields if needed
     * @return void
     */
    protected function replaceSortFields()
    {
        if (!empty($this->params['sort'])) {
            $sort = Util\toTrimmedArray($this->params['sort']);

            foreach ($sort as $k => $el) {
                list($f, $s) = explode(' ', $el);
                if (!empty($this->replaceSortFields[$f])) {
                    $sort[$k] = $this->replaceSortFields[$f].' '.$s;
                }
            }

            $this->params['sort'] = implode(', ', $sort);
        }
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

        //add extra params for debugging if is debug host
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
        $titleField = 'title_' . Config::get('user_language') . '_t';

        //iterate documents, add resulting record to $rez['data']
        //and collect shortcut records to be prepared
        foreach ($sr->response->docs as $d) {
            $rd = array();
            //implode multivalued fields to sting
            foreach ($d as $fn => $fv) {
                $rd[$fn] = is_array($fv) ? implode(',', $fv) : $fv;
            }

            //update name field to language title field if not empty
            $rd['name'] = empty($rd[$titleField])
                ? @$rd['name']
                : $rd[$titleField];

            unset($rd[$titleField]);

            $rez['data'][] = &$rd;

            if (!empty($rd['target_id'])) {
                $shortcuts[$rd['target_id']] = &$rd;
            }

            unset($rd);
        }

        //add highlights
        if (!empty($sr->highlighting)) {
            foreach ($rez['data'] as &$d) {
                $id = empty($d['target_id'])
                    ? $d['id']
                    : $d['target_id'];

                if (!empty($sr->highlighting->{$id}->{'name'})) {
                    $d['hl'] = $sr->highlighting->{$id}->{'name'}[0];
                }
                // if (!empty($sr->highlighting->{$id}->{'content'})) {
                //     $d['content'] = $sr->highlighting->{$id}->{'content'}[0];
                // }
            }
        }

        $this->warmUpNodes($rez);

        $this->updateShortcutsData($shortcuts);

        $this->setPaths($rez['data']);

        //shold also be added to warmUp ?
        $rez = array_merge($rez, $this->processResultFacets());

        if (!empty($this->inputParams['view'])) {
            $rez['view'] = $this->inputParams['view'];
        }

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
        $rez = array(
            'facets' => $this->results->facet_counts
        );

        if (!$this->facetsSetManually) {
            $rez = array();

            foreach ($this->facets as $facet) {
                $facet->loadSolrResult($this->results->facet_counts, $this->results);

                $fr = $facet->getClientData();

                if (!empty($fr)) {
                    $idx = empty($fr['index'])
                        ? 'facets'
                        : $fr['index'];

                    $rez[$idx][$fr['f']] = $fr;
                }
            }
        }

        return $rez;
    }

    protected function updateShortcutsData(&$shortcutsArray)
    {
        if (empty($shortcutsArray)) {
            return;
        }

        $ids = array_keys($shortcutsArray);

        $objects = Objects::getCachedObjects($ids);

        foreach ($objects as $obj) {
            $d = $obj->getData();
            $sd = $obj->getSolrData();
            $oldProps = $shortcutsArray[$d['id']];
            $ref = &$shortcutsArray[$d['id']];

            //set data form target objects solr data or general data if present
            foreach ($ref as $fn => $fv) {
                if (isset($d[$fn])) {
                    $fv = $d[$fn];
                }
                if (isset($sd[$fn])) {
                    $fv = $sd[$fn];
                }
                $ref[$fn] = is_array($fv) ? implode(',', $fv) : $fv;
            }
            //set element id to original so all actions will be made on shortcut by default
            //only opening will check if this object data has a target id
            $ref['id'] = $oldProps['id'];
        }
    }/**/

    /**
     * method to collect all node ids needed for rendering of loaded data set
     * @param  array &$result containing recxords in 'data' property
     * @return void
     */
    protected function warmUpNodes(&$result)
    {
        $d = &$result['data'];

        $requiredIds = array();
        $paths = array();

        foreach ($d as &$rec) {
            if (!empty($rec['id'])) {
                $requiredIds[$rec['id']] = 1;
            }

            //add shorcut targets
            if (!empty($rec['target_id'])) {
                $requiredIds[$rec['target_id']] = 1;
            }

            //add path ids
            if (isset($rec['path']) && !isset($paths[$rec['path']])) {
                $path = Util\toNumericArray($rec['path'], '/');
                if (!empty($path)) {
                    $paths[$rec['path']] = $path;
                    foreach ($path as $id) {
                        $requiredIds[$id] = 1;
                    }
                }
            }

        }

        $requiredIds = array_keys($requiredIds);

        //preload templates
        Templates\SingletonCollection::getInstance()->loadAll();

        //preload all users display data

        //now there objects should be loaded in bulk before firing event
        //because DisplayColumns analizes each object from result
        Objects::getCachedObjects($requiredIds);

        $requiredIds = array();

        $eventParams = array(
            'inputParams' => &$this->inputParams
            ,'params' => &$this->params
            ,'data' => &$d
            ,'requiredIds' => &$requiredIds
        );

        \CB\fireEvent('solrQueryWarmUp', $eventParams);

        return $requiredIds;
    }

    /**
     * update path property for an items array
     * @param array $dataArray
     */
    public static function setPaths(&$dataArray)
    {
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

        if (!empty($distinctIds)) {
            $distinctIds = array_unique($distinctIds);
            $objects = Objects::getCachedObjects($distinctIds);
            $names = array();

            foreach ($distinctIds as $id) {
                if (!empty($objects[$id])) {
                    $names[$id] = $objects[$id]->getHtmlSafeName();
                }
            }

            //replace ids in paths with names
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

        if (!empty($ids)) {
            $chunks = array_chunk($ids, 200);

            //execute search
            try {
                foreach ($chunks as $chunk) {
                    $params = array(
                        'fl' => $fieldList
                        ,'facet' => false
                        ,'skipSecurity' => true
                        ,'fq' => array(
                            'id:(' . implode(' OR ', $chunk). ')'
                        )
                    );

                    $search = new Search();
                    $sr = $search->query($params);

                    if (!empty($sr['data'])) {
                        foreach ($sr['data'] as &$d) {
                            $rez[$d['id']] = $d;
                        }
                    }

                }
            } catch ( \Exception $e ) {
                throw new \Exception("An error occured in getObjects: \n\n {$e->__toString()}");
            }
        }

        return $rez;
    }
}
