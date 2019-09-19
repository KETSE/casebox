<?php

interface Apache_Solr_Compatibility_AddDocumentXmlCreator
{
    /**
     * Creates an add command XML string
     *
     * @param string  $rawDocuments String containing XML representation of documents.
     * @param boolean $allowDups
     * @param boolean $overwritePending
     * @param boolean $overwriteCommitted
     * @param integer $commitWithin The number of milliseconds that a document must be committed within,
     *          see @{link http://wiki.apache.org/solr/UpdateXmlMessages#The_Update_Schema} for details. If left empty
     *          this property will not be set in the request.
     * @return string An XML string
     */
    public function createAddDocumentXmlFragment(
        $rawDocuments,
        $allowDups = false,
        $overwritePending = true,
        $overwriteCommitted = true,
        $commitWithin = 0
    );
} 
