<?php
namespace CB\Solr;

/**
 * Solr service handler that overrides Apache_Sold_Service handler class
 * to add ability for nested documents
 * and query by a different request hadler for BlockJoin requests
 */

class ServiceHandler extends \Apache_Solr_Service
{

    public function setSearchHandler($searchHandler)
    {
        $this->_searchUrl = $this->_constructUrl($searchHandler);
    }

    protected function _generateQueryString($params)
    {
        $jsonFacets = empty($params['json.facet'])
            ? []
            : $params['json.facet'];

        unset($params['json.facet']);

        $rez = parent::_generateQueryString($params);

        foreach ($jsonFacets as $k => $v) {
            $fqs = urlencode(json_encode($v));
            $rez .= "&json.facet.$k=" . $fqs;
        }

        return $rez;
    }

    protected function _documentToXmlFragment(\Apache_Solr_Document $document)
    {
        $xml = '<doc';

        if ($document->getBoost() !== false) {
            $xml .= ' boost="' . $document->getBoost() . '"';
        }

        $xml .= '>';

        foreach ($document as $key => $value) {
            $fieldBoost = $document->getFieldBoost($key);
            $key = htmlspecialchars($key, ENT_QUOTES, 'UTF-8');

            //adding here the check for child documents
            if ($key === "_childDocuments_") {
                foreach ($value as $cd) {
                    $xml .= $this->_documentToXmlFragment($cd);
                }

            } elseif (is_array($value)) {
                foreach ($value as $multivalue) {
                    $xml .= '<field name="' . $key . '"';

                    if ($fieldBoost !== false) {
                        $xml .= ' boost="' . $fieldBoost . '"';

                        // only set the boost for the first field in the set
                        $fieldBoost = false;
                    }

                    $multivalue = htmlspecialchars($multivalue, ENT_NOQUOTES, 'UTF-8');

                    $xml .= '>' . $multivalue . '</field>';
                }
            } else {
                $xml .= '<field name="' . $key . '"';

                if ($fieldBoost !== false) {
                    $xml .= ' boost="' . $fieldBoost . '"';
                }

                $value = htmlspecialchars($value, ENT_NOQUOTES, 'UTF-8');

                $xml .= '>' . $value . '</field>';
            }
        }

        $xml .= '</doc>';

        // replace any control characters to avoid Solr XML parser exception
        return $this->_stripCtrlChars($xml);
    }
}
