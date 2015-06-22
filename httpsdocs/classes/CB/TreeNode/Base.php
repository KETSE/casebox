<?php
namespace CB\TreeNode;

use CB\Util;

class Base implements \CB\Interfaces\TreeNode
{
    protected $config;
    public $guid = null;
    public $id = null;

    public function __construct ($config = array(), $id = null)
    {
        if (!empty($config['pid']) && ($config['pid'] == 'root')) {
            $config['pid'] = \CB\Browser::getRootFolderId();
        }

        if (!empty($config['realNodeId']) && ($config['realNodeId'] == 'root')) {
            $config['realNodeId'] = \CB\Browser::getRootFolderId();
        }

        $this->config = $config;
        $this->guid = @$config['guid'];
        $this->id = $id;
    }

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

        if (empty($pathArray)) {
            return false;
        } else {
            $lastNode = $pathArray[sizeof($pathArray) - 1];
        }

        //get the configured 'pid' property for this tree plugin
        //default is 0
        //thats the parent node id where this class shold start to give result nodes
        $ourPid = @$this->config['pid'];

        // ROOT NODE: check if last node is the one we should attach to
        if ($lastNode->getId() == (String)$ourPid) {
            return true;
        }

        // CHILDREN NODES: accept if last node is an instance of this class (same GUID)
        if ($lastNode->guid == $this->guid) {
            return true;
        }

        return false;
    }

    /**
     * return the children for for input params
     * @param  array $pathArray
     * @param  array $requestParams
     * @return array
     */
    public function getChildren(&$pathArray, $requestParams)
    {
        return array();
    }

    /**
     * the the formated id (with plugin guid prefix) for a given node id
     * @param  varchar $id
     * @return varchar
     */
    public function getId($id = null)
    {
        if (is_null($id)) {
            $id = $this->id;
        }
        if (!empty($this->guid)) {
            $id = $this->guid.'-'.$id;
        }

        return $id;
    }

    /**
     * get the name for a given node id
     * @param  variant $id
     * @return varchar
     */
    public function getName($id = false)
    {
        $t = @$this->config['text'] ? @$this->config['text']
                                    : 'Unamed';

        return $t;
    }

    /**
     * get data for current node instance, based on this->id
     * @return array
     */
    public function getData()
    {
        return array();
    }

    /**
     * get node configuration
     * @return array set of properties
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * get view config for given view or default view if set in config
     * @param  array &$pathArray
     * @param  array &$rp        requestParams
     * @return array
     */
    public function getViewConfig(&$pathArray, &$rp)
    {
        $rez = array();

        if (!$this->acceptedPath($pathArray, $requestParams)) {
            return $rez;
        }

        $cfg = &$this->config;

        $view = array('type' => 'grid');

        if (!empty($rp['userViewChange'])) {
            $type  = empty($rp['view'])
                ? $rp['from']
                : $rp['view'];

            $view = array(
                'type' => $type
            );

        } elseif (!empty($cfg['view'])) {
            $view = is_scalar($cfg['view'])
                ? array(
                    'type' => $cfg['view']
                )
                : $cfg['view'];
        }

        if (!empty($view)) {
            $rez['view'] = $view;

            switch ($view['type']) {
                case 'pivot':
                case 'charts':
                    if (!empty($cfg['stats'])) {
                        $stats = array();
                        foreach ($cfg['stats'] as $item) {
                            $stats[] = array(
                                'title' => Util\detectTitle($item)
                                ,'field' => $item['field']
                            );
                        }
                        $rez['stats'] = $stats;
                    }

                    break;

                default: // grid
                    // if (!empty($cfg['view']['group'])) {
                    //     $rez['group'] = $cfg['view']['group'];
                    // }
            }
        }

        return $rez;
    }

    /**
     * get parent node
     * @return object | null
     */
    public function getParent()
    {

    }

    /**
     * get depth of the node
     * @return int
     */
    public function getDepth()
    {
        $rez = 1;

        if (empty($this->parent)) {
            return $rez;
        }

        return ($this->parent->getDepth() + 1);
    }

    /**
     * get depth of the node from same classes nodes branch
     * @return int
     */
    public function getClassDepth()
    {
        $rez = 1;

        if (empty($this->parent) || ($this->parent->guid !== $this->guid)) {
            return $rez;
        }

        return ($this->parent->getClassDepth() + 1);
    }

    /**
     * get root node of the same class branch
     * @return object
     */
    public function getClassRoot()
    {
        $rez = &$this;

        if (empty($this->parent) || (get_class($this->parent) !== get_class($this))) {
            return $rez;
        }

        return ($this->parent->getClassRoot());
    }

    /**
     * check if a node has children
     * @return int
     */
    public function hasChildren()
    {

    }

    /**
     * get list of facets classes that should be available for this node
     * @param  array &$rp request params
     * @return array
     */
    public function getFacets(&$rp)
    {
        $facets = array();
        $cfg = $this->getNodeParam('facets');

        if (empty($cfg['data'])) {
            return $facets;
        }

        //creating facets
        $facetsDefinitions = \CB\Config::get('facet_configs');

        foreach ($cfg['data'] as $k => $v) {
            $name = $k;
            $config = null;
            if (is_scalar($v)) {
                $name = $v;
                if (!empty($facetsDefinitions[$name])) {
                    $config = $facetsDefinitions[$name];
                }
            } else {
                $config = $v;
            }

            if (is_null($config)) {
                \CB\debug('Cannot find facet config:' . var_export($name, 1) . var_export($v, 1));
            } else {
                $config['name'] = $name;
                $facets[$name] = \CB\Facets::getFacetObject($config);
            }
        }

        if (!empty($rp['view']['type'])) {

            $v = &$rp['view'];

            $rows = false;
            $cols = false;

            if (!empty($v['rows']['facet'])) {
                $rows = $v['rows']['facet'];
            }
            if (!empty($v['cols']['facet'])) {
                $cols = $v['cols']['facet'];
            }

            if (($rp['view']['type'] == 'pivot') && (sizeof($facets) > 1)) {
                if (!empty($rp['selectedFacets']) &&
                    (is_array($rp['selectedFacets'])) &&
                    sizeof($rp['selectedFacets'] > 1)
                ) {
                    $rows = $rp['selectedFacets'][0];
                    $cols = $rp['selectedFacets'][1];
                }

                reset($facets);

                if (empty($rows)) {
                    $rows = current($facets);
                    next($facets);
                }

                if (empty($cols)) {
                    $cols = current($facets);
                }

                if (is_scalar($rows) || is_scalar($cols)) {
                    foreach ($facets as $facet) {
                        if ((is_scalar($rows)) && ($facet->field == $rows)) {
                            $rows = $facet;
                        }
                        if ((is_scalar($cols)) && ($facet->field == $cols)) {
                            $cols = $facet;
                        }
                    }
                }

                $config = array(
                    'type' => 'pivot'
                    ,'name' => 'pivot'
                    ,'facet1' => $rows
                    ,'facet2' => $cols
                );

                if (!empty($rp['selectedStat'])) {
                    $config['stats'] = $rp['selectedStat'];
                } elseif (!empty($cfg['view']['stats'])) {
                    $config['stats'] = $cfg['view']['stats'];
                }

                $facets[] = \CB\Facets::getFacetObject($config);
            }
        }

        return $facets;
    }

    /**
     * get create menu for current node
     * @return varchar menu config string
     */
    public function getCreateMenu()
    {
        $rez = '';
        if (!empty($this->config['createMenu'])) {
            $rez = $this->config['createMenu'];
        } else {
            if (!empty($this->parent)) {
                $rez = $this->parent->getCreateMenu();
            }
        }

        return $rez;
    }

    /**
     * Get param for current node(considered last node in active path)
     *
     * @param  varchar $param for now using to get 'facets' or 'DC'
     * @return array
     */
    public function getNodeParam($param = 'facets')
    {
        // check if directly set into node config
        if (isset($this->config[$param])) {
            $rez = array(
                'from' => $this->getClassRoot()->getId()
                ,'data' => $this->config[$param]
            );

            //add sorting if set in config
            if (!empty($this->config['sort'])) {
                $rez['sort'] = $this->config['sort'];

            }

            //add grouping param for DC
            if (($param == 'DC')) {
                if (!empty($this->config['view']['group'])) {
                    $rez['group'] = $this->config['view']['group'];

                } elseif (!empty($this->config['group'])) {
                    $rez['group'] = $this->config['group'];
                }
            }

            return $rez;
        }

        //check in config
        $paramConfigs = \CB\Config::get('node_'.$param);

        if (empty($paramConfigs[$this->getId($this->id)])) {
            if (empty($this->parent)) {
                $default = \CB\Config::get('default_' . $param);

                if (empty($default)) {
                    return array();
                }

                return array(
                    'from' => 'default'
                    ,'data' => $default
                );
            }

            return $this->parent->getParentNodeParam($param);
        }

        return array(
            'from' => $this->getId()
            ,'data' => $paramConfigs[$this->id]
        );
    }

    /**
     * get params for parent nodes (not last node in active path)
     *
     * Generally this method should work as getNodeParam but for
     * descendant class Dbnode this method should avoid checking templates config
     * @param  varchar $param same as for getNodeParam
     * @return variant
     */
    public function getParentNodeParam($param = 'facets')
    {
        return $this->getNodeParam($param);
    }
}
