Casebox application development
====================================

The model of a PHP application that uses CB:

.. code-block:: php

    namespace CB;
    use CB\DB;

    // prevent casebox methods to start solr reindexing automatically
    \CB\Config::setFlag('disableSolrIndexing', true);

    // don't log each action in CB (it will create mysql records and use SOLR)
    \CB\Config::setFlag('disableActivityLog', true);

    // no events will be generated, i.e. onBeforeCreate, etc
    \CB\Config::setFlag('disableTriggers',true);


    // Commit inserts/updates in Casebox in a batch (200 - 1000 records per transaction)
    DB\startTransaction();

    foreach ($DB->query($sql) as $row) {
        importRecord($row);
    }

    DB\commitTransaction();

    // after importing, run SOLR reindex

