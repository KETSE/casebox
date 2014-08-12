<?php
namespace CB\WebDAV;

class CustomPropertiesPlugin extends \Sabre\DAV\ServerPlugin {

    function getFeatures() {

        return array();

    }

    function getHTTPMethods($uri) {
    }

    function initialize(\Sabre\DAV\Server $server) {

        $this->server = $server;
        $server->subscribeEvent('beforeGetProperties',array($this,'beforeGetProperties'));
        $server->subscribeEvent('afterGetProperties',array($this,'afterGetProperties'));
    }

    function beforeGetProperties($path, $node, &$requestedProperties, &$returnedProperties) {

        if (!in_array('{DAV:}creationdate', $requestedProperties) ) {
            if(method_exists($node, 'getCreationDate')){
                $returnedProperties[200]['{DAV:}creationdate'] = new CreationDate($node->getCreationDate());
            }

        }
    }
    function afterGetProperties($path, $properties, $node) {
//echo '<pre>';
//print_r($properties);
//echo '</pre>';

    }
}