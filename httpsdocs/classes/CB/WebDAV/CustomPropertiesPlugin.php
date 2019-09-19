<?php
namespace CB\WebDAV;

class CustomPropertiesPlugin extends \Sabre\DAV\ServerPlugin
{
    public function getFeatures()
    {
        return array();

    }

    public function getHTTPMethods($uri)
    {
        $uri = $uri; //dummy codacy assignment
    }

    public function initialize(\Sabre\DAV\Server $server)
    {
        $this->server = $server;
        $server->subscribeEvent('beforeGetProperties', array($this,'beforeGetProperties'));
        $server->subscribeEvent('afterGetProperties', array($this,'afterGetProperties'));
    }

    public function beforeGetProperties($path, $node, &$requestedProperties, &$returnedProperties)
    {
        $path = $path; //dummy codacy assignment
        if (!in_array('{DAV:}creationdate', $requestedProperties)) {
            if (method_exists($node, 'getCreationDate')) {
                $returnedProperties[200]['{DAV:}creationdate'] = new CreationDate($node->getCreationDate());
            }

        }
    }

    public function afterGetProperties($path, $properties, $node)
    {
        $path = $path; //dummy codacy assignment
        //echo '<pre>';
        //print_r($properties);
        //echo '</pre>';

    }
}
