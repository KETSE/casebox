Multilingual Casebox
========================

In mysql config use ``languages`` to specify what languages your data will be available in. When an object is saved, CB checks for ``title_$lg`` fields and saves them in SOLR columns ``title_$lg_t``. ``_t`` is used to specify a SOLR dynamic field. It doesn't matter if a content type has ``title_$lg`` fields, CB will always populate these SOLR columns, making sure each record has titles in all languages.

Each object has a SOLR ``name`` field that is used if there is no ``title_$lg`` field defined in content type. Example: filename of a file is used as ``name``.

When a record is displayed, CB will try to use SOLR ``title_$lg_t`` field. Same applies for object fields. Example: a product has ``color`` field of type object. A thesauri content type is defined to have ``title_en``, ``title_fr`` fields and used to create a few colors. The preview of the product will show language dependent color name.