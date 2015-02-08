<?php
namespace CB\TreeNode;

use CB\Config;
use CB\Util;
use CB\Facets;
use CB\Search;

class FacetNav extends Base
{
    protected function acceptedPath()
    {
        $p = &$this->path;

        // can't be a root folder
        if (sizeof($p) == 0) {
            return false;
        }

        //get the configured 'pid' property for this tree plugin
        //default is 0
        //thats the parent node id where this class shold start to give result nodes
        $ourPid = @$this->config['pid'];

        // ROOT NODE: check if last node is the one we should attach to
        if ($this->lastNode->getId() == (String)$ourPid) {
            return true;
        }

        // CHILDREN NODES: accept if last node is an instance of this class (same GUID)
        if ($this->lastNode->guid == $this->guid) {
            return true;
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

        $this->lastNodeDepth = $this->lastNode->getClassDepth();

        if (empty($this->lastNode) || ($this->lastNode->guid != $this->guid)) {
            $rez = $this->getRootNode();
        } else {
            $rez = $this->getChildNodes();
        }

        //set view if set in config
        if (!empty($this->config['view'])) {
            $rez['view'] = $this->config['view'];
        }

        return $rez;
    }

    public function getName($id = false)
    {
        $rez = 'no name';
        if ($id === false) {
            $id = $this->id;
        }

        if (!empty($id) && is_numeric($id)) {
            $rez = @Search::getObjectNames($id)[$id];

        } else {
            switch ($id) {
                case 'root':
                    $cfg = &$this->config;
                    $l = Config::get('user_language');

                    if (empty($cfg['title_'.$l])) {
                        $l = Config::get('language');
                        if (empty($cfg['title_'.$l])) {
                            if (!empty($cfg['title'])) {
                                $rez = $cfg['title'];
                            }
                        } else {
                            $rez = $cfg['title_' . $l];
                        }
                    } else {
                        $rez = $cfg['title_' . $l];
                    }

                    break;
            }
        }

        return $rez;
    }

    protected function getRootNode()
    {
        return array(
            'data' => array(
                array(
                    'name' => $this->getName('root')
                    ,'id' => $this->getId('root')
                    ,'iconCls' => Util\coalesce(@$this->config['iconCls'], 'icon-folder')
                    ,'has_childs' => (!empty($this->config['level_fields']) || !empty($this->config['show_in_tree']))
                )
            )
        );
    }


    protected function getParentNodeFilters()
    {
        $rez = array();

        $depth = $this->lastNodeDepth -1; //exclude root node

        $lfc = $this->getLevelFieldConfigs();

        $configs = array_splice($lfc, 0, $depth);

        //iterate from last node to top and collect filters
        $pn = $this->lastNode;
        while ($config = array_pop($configs)) {
            $rez[] = $config['field'] . ':' . $pn->id;
            $pn = $pn->parent;
        }

        return $rez;
    }

    protected function getCurrentFacetFieldConfig()
    {
        $depth = $this->lastNodeDepth;

        $levelFields = $this->getLevelFieldConfigs();

        $rez = reset($levelFields);

        while ($depth > 1) {
            $rez = next($levelFields);
            $depth--;
        }

        return $rez;
    }

    /**
     * getChildNodes description
     * @return json responce
     */
    protected function getChildNodes()
    {
        $rez = array('data' => array());

        $currentFacetFieldConfig  = $this->getCurrentFacetFieldConfig();

        $lfc = $this->getLevelFieldConfigs();
        $isLastFacetField = ($this->lastNodeDepth >= sizeOf($lfc));

        if (empty($currentFacetFieldConfig) || (@$this->requestParams['from'] !== 'tree')) {
            return $this->getItems();
        }

        $facetName = $currentFacetFieldConfig['name'];
        $facetField = $currentFacetFieldConfig['field'];

        $fq = array_merge(
            $this->config['fq'],
            $this->getParentNodeFilters()
        );

        $this->replaceFilterVars($fq);

        $s = new \CB\Search();
        $sr = $s->query(
            array(
                'rows' => 0
                ,'fq' => $fq
                ,'facet' => true
                ,'facet.field' => array(
                    '{!ex=' . $facetField . ' key=' . $facetName . '}' . $facetField
                )
            )
        );

        if (!empty($sr['facets']->facet_fields->{$facetName})) {
            $facetClass = Facets::getFacetObject($currentFacetFieldConfig);
            $facetClass->loadSolrResult($sr['facets']);
            $facetData = $facetClass->getClientData();
            $showChilds = (!$isLastFacetField || !empty($this->config['show_in_tree']));

            foreach ($facetData['items'] as $k => $v) {
                if (is_numeric($v)) {
                    $name = $k;
                    $count = $v;
                } else {
                    $name = $v['name'];
                    $count = $v['count'];
                }

                if (!empty($this->config['show_count']) && !empty($count)) {
                    $name .= ' (' . $count . ')';

                }

                $r = array(
                    'name' => $name
                    ,'id' => $this->getId($k)
                    ,'iconCls' => 'icon-folder'
                );

                if ($showChilds) {
                    $r['has_childs'] = true;
                }

                $rez['data'][] = $r;
            }
        }

        return $rez;
    }

    /**
     * get items
     * @return json responce
     */
    protected function getItems()
    {
        $rez = array('data' => array());

        $p = $this->requestParams;

        $p['fq'] = array_merge(
            $this->config['fq'],
            $this->getParentNodeFilters()
        );

        $this->replaceFilterVars($p['fq']);

        $s = new \CB\Search();
        $rez = $s->query($p);

        return $rez;
    }

    /**
     * get level fields array from config
     * if in config level_fields is a comma separated string
     * it will be converted to an associative array of fieldname => config
     * @return array
     */
    protected function getLevelFieldConfigs()
    {
        $rez = array();

        if (isset($this->LevelFieldConfigs)) {
            return $this->LevelFieldConfigs;
        }

        $facetsDefinitions = \CB\Config::get('facet_configs');

        if (!empty($this->config['level_fields'])) {
            $fields = $this->config['level_fields'];

            if (is_string($fields)) {
                $fields = explode(',', $fields);
            }

            foreach ($fields as $key => $value) {
                if (is_scalar($value)) {
                    $key = trim($value);
                    $value = array();
                }

                if (!empty($facetsDefinitions[$key])) {
                    $value = $facetsDefinitions[$key];

                    $value['name'] = $key;

                    if (empty($value['field'])) {
                        $value['field'] = $key;
                    }
                }

                $rez[$key] = $value;
            }
        }

        $this->LevelFieldConfigs = $rez;

        return $rez;
    }

    /**
     * replace possible variables in a filter array for solr query
     * @param  array reference &$filterArray
     * @return void
     */
    protected function replaceFilterVars(&$filterArray)
    {
        //
        foreach ($filterArray as $key => $value) {
            $filterArray[$key] = str_replace('$activeUserId', $_SESSION['user']['id'], $value);
        }
    }
}
