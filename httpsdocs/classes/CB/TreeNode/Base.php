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

    public function getChildren(&$pathArray, $requestParams)
    {
        return array();
    }

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

    public function getName($id = false)
    {
        return 'no name';
    }

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
     * get depth
     * @return int
     */
    public function getDepth()
    {

    }

    /**
     * check if a node has children
     * @return int
     */
    public function hasChildren()
    {

    }

    /**
     * get list of facets classses  that should be available for this node
     * @return array
     */
    public function getFacets()
    {
        $rez = array();
        $nodesFacetsConfig = \CB\Config::get('node_facets');
        if (empty($nodesFacetsConfig[$this->id])) {
            if (empty($this->parent)) {
                return $rez;
            }

            return $this->parent->getFacets();
        }

        $cfg = $nodesFacetsConfig[$this->id];

        //creating facets
        $facetsDefinitions = \CB\Config::get('facet_configs');
        $facets = array();
        foreach ($cfg as $k => $v) {
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
}
