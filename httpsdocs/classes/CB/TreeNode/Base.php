<?php
namespace CB\TreeNode;

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
     * get list of facets classses that should be available for this node
     * @return array
     */
    public function getFacets()
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
                \CB\debug('Cannot find facet config:'.var_export($name, 1).var_export($v, 1));
            } else {
                $config['name'] = $name;
                $facets[$name] = \CB\Facets::getFacetObject($config);
            }
        }

        /* add pivot facet if we are in pivot view*/
        $rp = \CB\Cache::get('requestParams');
        if (!empty($rp['from']) && ($rp['from'] == 'pivot') && (sizeof($facets) > 1)) {
            reset($facets);
            $facet1 = current($facets);
            next($facets);
            $facet2 = current($facets);

            if (!empty($rp['selectedFacets']) && (is_array($rp['selectedFacets'])) && sizeof($rp['selectedFacets'] > 1)) {
                $facet1 = $rp['selectedFacets'][0];
                $facet2 = $rp['selectedFacets'][1];
                foreach ($facets as $facet) {
                    if ($facet->field == $facet1) {
                        $facet1 = $facet;
                    }
                    if ($facet->field == $facet2) {
                        $facet2 = $facet;
                    }
                }
            }

            $config = array(
                'type' => 'pivot'
                ,'name' => 'pivot'
                ,'facet1' => $facet1
                ,'facet2' => $facet2
            );
            $facets[] = \CB\Facets::getFacetObject($config);
        }
        /* end of add pivot facet if we are in pivot view*/

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
            return array(
                'from' => $this->getClassRoot()->getId()
                ,'data' => $this->config[$param]
            );
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
