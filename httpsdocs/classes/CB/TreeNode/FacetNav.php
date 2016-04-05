<?php
namespace CB\TreeNode;

use CB\Config;
use CB\Facets;
use CB\Objects;
use CB\Search;
use CB\User;

class FacetNav extends Query
{
    public function getName($id = false)
    {
        $rez = 'no name';
        if ($id === false) {
            $id = $this->id;
        }

        if (!empty($id) && is_numeric($id)) {
            $facetConfig = $this->getFacetFieldConfig($this->getClassDepth() - 1);
            switch (@$facetConfig['type']) {
                case 'users':
                    $rez = User::getDisplayName($id);
                    break;

                case 'varchar':
                    $rez = $id;
                    break;

                default:
                    $rez = Objects::getName($id);
            }

        } else {
            switch ($id) {
                case 'root':
                    $rez = parent::getName('root');

                    break;
            }
        }

        return $rez;
    }

    protected function getRootNode()
    {
        $rez = parent::getRootNode();
        $rez['data'][0]['has_childs'] = (!empty($this->config['level_fields']) || !empty($this->config['show_in_tree']));

        return $rez;
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
        return $this->getFacetFieldConfig($this->lastNodeDepth);
    }

    protected function getFacetFieldConfig($index)
    {
        $levelFields = $this->getLevelFieldConfigs();

        $rez = reset($levelFields);

        while ($index > 1) {
            $rez = next($levelFields);
            $index--;
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

        $cffc  = $this->getCurrentFacetFieldConfig();

        $lfc = $this->getLevelFieldConfigs();
        $isLastFacetField = ($this->lastNodeDepth >= sizeOf($lfc));

        if (empty($cffc) || (@$this->requestParams['from'] !== 'tree')) {
            return $this->getItems();
        }

        $facetName = $cffc['name'];
        $facetField = $cffc['field'];

        $fq = empty($this->config['fq'])
            ? array()
            : $this->config['fq'];

        $s = new \CB\Search();

        if (empty($cffc['child'])) {
            $fq = array_merge(
                $fq,
                $this->getParentNodeFilters()
            );

            $this->replaceFilterVars($fq);

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

        } else { //BlockJoin query
            $query = '{!parent which=child:false}child:true';

            $parentFilters = $this->getParentNodeFilters();

            if (!empty($parentFilters)) {
                $query .= ' ' . implode(' ', $parentFilters);
            }

            $domain = empty($cffc['domain'])
                ? ['blockParent' => 'child:false']
                : $cffc['domain'];

            $sr = $s->query(
                [
                    'query' => $query,
                    'fq' => $fq,
                    'rows' => 0,
                    'facet' => true,
                    'json.facet' => [
                        $facetName => [
                            'type' => 'terms',
                            'field' => $facetField,
                            'domain' => $domain
                        ]
                    ]
                ]
            );

            // $sr = $s->query(
            //     array(
            //         'query' => $query
            //         ,'rows' => 0
            //         ,'facet' => true
            //         ,'child.facet.field' => $facetField
            //     ),
            //     'bjf'
            // );
            // //block join is experimental and doesnt support aliasing
            // //for child.facet.field
            // $facetName = $facetField;
        }

        if (!empty($sr['facets']->facet_fields->{$facetName}) ||
            !empty($sr['facets']->{$facetName})
        ) {
            $facetClass = Facets::getFacetObject($cffc);
            $facetClass->loadSolrResult((object) $sr);
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
                    // $name .= ' (' . $count . ')';
                    $name .= ' <span style="color: #AAA; font-size: 12px">' . $count . '</span>';

                }

                $r = array(
                    'name' => $name
                    ,'id' => $this->getId($k)
                    ,'iconCls' => 'icon-folder'
                    // ,'iconCls' => 'icon-none'
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

        $fq = empty($this->config['fq'])
            ? []
            : $this->config['fq'];

        $p['fq'] = array_merge(
            $fq,
            $this->getParentNodeFilters()
        );

        $this->replaceFilterVars($p['fq']);

        if (!empty($this->requestParams['view']['child'])) {
            $p['child'] = true;
        }

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
}
