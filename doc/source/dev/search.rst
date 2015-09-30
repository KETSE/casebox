Search
===========

CB relies on SOLR to perform search/browse operations. Fields that needs be search-able should be indexed in SOLR.

Data stored in JSON format in ``objects`` mysql table is indexed in SOLR in two steps

1. when a record is updated, CB checks what fields are marked as indexed and creates a ``solr`` entry in the JSON stored in ``objects.sys_data`` mysql table column.The update also marks the updated in ``tree`` table.

2. The record is indexed in SOLR and custom data prepared in ``objects.sys_data`` (custom fields or other calculated fields) is used.

SOLR indexing is triggered by CB immediately after the record is updated. Additionally, a cronjob will index records in ``tree`` mysql table ``WHERE updated=1``

A custom SOLR field is then used to create facets in Filter panel, charts, pivot tables, search templates.

When a template field is declared indexed, all records of that template should be re-processed: both steps 1 and 2.

To perform step 1 manually, run the script ``update_solr_prepared_data.php`` from /bin/ folder.

.. todo:: when a field is declared ``indexed``, mark the template as modified, and later have a button in UI to update/reindex records in SOLR