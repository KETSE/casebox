<?php
namespace CB\Solr;

/**
 * Solr service class that manages communication between CaseBox and Solr service
 *
 * This class only manages connection and standart calls to Solr service
 */
class Service
{
    /** @type Apache_Solr_Service solr handler to Solr Service. */
    private $solr_handler = null;

    /** @type varchar solr host. */
    private $host = null;
    /** @type varchar solr port. */
    private $port = null;
    /** @type varchar solr core. */
    private $core = null;

    /**
     * constructor
     *
     * @param string[] $p {
     *     @type boolean $host    custom Solr host or default will be used from config
     *     @type string  $port    Solr port
     *     @type string  $core    Solr core
     * }
     */
    public function __construct ($p = array())
    {
        $this->host = empty($p['host']) ? \CB\CONFIG\SOLR_HOST : $p['host'];
        $this->port = empty($p['port']) ? \CB\CONFIG\SOLR_PORT : $p['port'];
        $this->core = empty($p['core']) ? \CB\CONFIG\SOLR_CORE : $p['core'];
        $this->connect();
    }

    /**
     * connect to solr service
     *
     * @return Apache_Solr_Service handler to solr intance
     */
    public function connect()
    {
        if (!empty($this->solr_handler)) {
            return $this->solr_handler;
        }

        require_once \CB\SOLR_CLIENT;

        $this->solr_handler = new \Apache_Solr_Service(
            $this->host,
            $this->port,
            $this->core
        );

        if (! $this->solr_handler->ping()) {
            throw new \Exception('Solr_connection_error'.$this->debugInfo(), 1);
        }

        return $this->solr_handler;
    }

    /**
     * add/update a single document into solr
     *
     * @param array $d array of document properties
     */
    public function addDocument($d)
    {
        $doc = new \Apache_Solr_Document();
        foreach ($d as $fn => $fv) {
                $doc->$fn = $fv;
        }

        try {
            \CB\fireEvent('beforeNodeSolrUpdate', $doc);
            $this->solr_handler->addDocument($doc);
            \CB\fireEvent('nodeSolrUpdate', $doc);
        } catch (\SolrClientException $e) {
            $msg = "Error adding document to solr (id:".$d['id'].')'.$this->debugInfo();
            debug($msg);
            throw new \Exception($msg, 1);
        }

        return true;
    }

    /**
     * updating multiple documents into solr using atomic updates
     * @param array $docs array of documents to be updated into solr
     */
    public function updateDocuments($docs)
    {
        $url = 'http://'.$this->host.':'.$this->port.$this->core.'/update/json';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type:application/json; charset=utf-8"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array_values($docs)));
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);

        $data = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception("curl_error:" . curl_error($ch).$this->debugInfo(), 1);
        }
    }

    /**
     * adding/updating multiple documents to solr.
     *
     * This function will divide received documents array into two sets of documents
     * thouse that should be updated via by adding them again into solr
     * and other that should be updated via atomic update (if update property is set)
     *
     * @param array $docs array of documents to be indexed into solr
     */
    public function addDocuments(&$docs)
    {
        $addDocs = array();
        $updateDocs = array();

        foreach ($docs as $in_doc) {
            if (empty($in_doc['update'])) {
                $doc = new \Apache_Solr_Document();
                foreach ($in_doc as $fn => $fv) {
                    $doc->$fn = $fv;
                }
                \CB\fireEvent('beforeNodeSolrUpdate', $doc);
                $addDocs[] = $doc;
            } else {
                $doc = array();
                unset($in_doc['update']);
                foreach ($in_doc as $fn => $fv) {
                    if ($fn == 'id') {
                        $doc[$fn] = $fv;
                    } else {
                        $doc[$fn] = array( 'set' => $fv );
                    }
                }
                $updateDocs[] = $doc;
            }
        }

        try {
            if (!empty($addDocs)) {
                $this->solr_handler->addDocuments($addDocs);
            }
            if (!empty($updateDocs)) {
                $this->updateDocuments($updateDocs);
            }

        } catch (\Exception $e) {
            var_dump($addDocs);
            var_dump($updateDocs);
            $msg = "Error adding multiple documents to solr.\n".$e->__toString().$this->debugInfo();
            debug($msg);
            throw new \Exception($msg, 1);
        }

        /* fire after update events */
        for ($i=0; $i < sizeof($addDocs); $i++) {
            \CB\fireEvent('nodeSolrUpdate', $addDocs[$i]);
        }

        for ($i=0; $i < sizeof($updateDocs); $i++) {
            \CB\fireEvent('nodeSolrUpdate', $updateDocs[$i]);
        }

        return true;
    }

    public function search($query, $start, $rows, $params)
    {
        return $this->solr_handler->search(
            $query,
            $start,
            $rows,
            $params
        );
    }
    /**
     * commit solr updates
     * @return null
     */
    public function commit()
    {
        $this->solr_handler->commit();
    }

    /**
     * delete documents from solr by a query
     *
     * @param varchar $query solr query
     */
    public function deleteByQuery($query)
    {
        try {
            $this->solr_handler->deleteByQuery($query);
            $this->commit();
        } catch (\Exception $e) {
            $msg = "Cannot delete by query".$this->debugInfo();
            debug($msg);
            throw new Exception($msg, 1);
        }
    }

    /**
     * optimize current solr core
     * @return null
     */
    public function optimize()
    {
        try {
            $this->solr_handler->optimize();
            $this->commit();
        } catch (\Exception $e) {
            $msg = "Cannot optimize solr core".$this->debugInfo();
            debug($msg);
            throw new Exception($msg, 1);
        }
    }

    private function debugInfo()
    {
        return \CB\isDebugHost()
            ? "\n".' ('.$this->host.':'.$this->port.' -> '.$this->core.' )'
            : '';
    }
}
