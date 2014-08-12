<?php

namespace CB\WebDAV;

use \Sabre\DAV\PropFind;
use \Sabre\DAV\PropPatch;

class PropertyStorageBackend implements \Sabre\DAV\PropertyStorage\Backend\BackendInterface {


    /**
     * Fetches properties for a path.
     *
     * This method received a PropFind object, which contains all the
     * information about the properties that need to be fetched.
     *
     * Ususually you would just want to call 'get404Properties' on this object,
     * as this will give you the _exact_ list of properties that need to be
     * fetched, and haven't yet.
     *
     * @param string $path
     * @param PropFind $propFind
     * @return void
     */
    public function propFind($path, PropFind $propFind) {

        $propertyNames = $propFind->get404Properties();
        if (!$propertyNames) {
            return;
        }

        // error_log("propFind: path($path), " . print_r($propertyNames, true));

        $cachedNodes = \CB\Cache::get('DAVNodes');
        // error_log("propFind: " . print_r($cachedNodes, true));

        $path = trim($path, '/');
        $path = str_replace('\\', '/', $path);

        // Node with $path is not in cached nodes, return
        if (! array_key_exists($path, $cachedNodes)) {
            return;
        }

        $node = $cachedNodes[$path];


        // while($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        //     $propFind->set($row['name'], $row['value']);
        // }
        foreach ($propertyNames as $prop) {
            if ($prop == '{DAV:}creationdate') {
                $dttm = new \DateTime($node['cdate']);

                // $dttm->getTimestamp()
                $propFind->set($prop, \Sabre\HTTP\Util::toHTTPDate($dttm));
            } elseif ($prop == '{urn:schemas-microsoft-com:office:office}modifiedby' or
                      $prop == '{DAV:}getmodifiedby') {
                $propFind->set($prop, \CB\User::getDisplayName($node['uid']));
            }
        }
    }



    /**
     * Updates properties for a path
     *
     * This method received a PropPatch object, which contains all the
     * information about the update.
     *
     * Usually you would want to call 'handleRemaining' on this object, to get;
     * a list of all properties that need to be stored.
     *
     * @param string $path
     * @param PropPatch $propPatch
     * @return void
     */
    public function propPatch($path, PropPatch $propPatch) {
        return true;
    }


    /**
     * This method is called after a node is deleted.
     *
     * This allows a backend to clean up all associated properties.
     */
    public function delete($path) {
    }

}
