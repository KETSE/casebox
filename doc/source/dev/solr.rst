SOLR
===================

If the default SOLR schema is used, then ``core.properties`` file will be as simple as:

::

  name=cb_mycore
  configSet=cb_default

Thanks to SOLR `dynamicFields`_, Casebox declares the following fields. If a field doesn't match with one the the dynamicField configuration, it will be ignored: this is configured in ``/CB/sys/solr_configsets/default_config/conf/schema.xml`` after the include of dynamicFields:


.. code-block:: json

    <dynamicField name="*" type="ignored" multiValued="true" />

Default Casebox fields are defined in ``/CB/sys/solr_configsets/default_config/conf/cb_solr_fields.xml``

.. code-block:: xml

    <!-- Dynamic field definitions allow using convention over configuration
           for fields via the specification of patterns to match field names.
           EXAMPLE:  name="*_i" will match any field ending in _i (like myid_i, z_i)
           RESTRICTION: the glob-like pattern in the name attribute must have
           a "*" only at the start or the end.  -->

    <dynamicField name="*_i"  type="int"    indexed="true"  stored="true"/>
    <dynamicField name="*_is" type="int"    indexed="true"  stored="true"  multiValued="true"/>
    <dynamicField name="*_s"  type="string"  indexed="true"  stored="true" />
    <dynamicField name="*_ss" type="string"  indexed="true"  stored="true" multiValued="true"/>
    <dynamicField name="*_l"  type="long"   indexed="true"  stored="true"/>
    <dynamicField name="*_ls" type="long"   indexed="true"  stored="true"  multiValued="true"/>
    <dynamicField name="*_t"  type="text_general"    indexed="true"  stored="true"/>
    <dynamicField name="*_txt" type="text_general"   indexed="true"  stored="true" multiValued="true"/>
    <!-- <dynamicField name="*_en"  type="text_en"    indexed="true"  stored="true" multiValued="true"/>  -->
    <dynamicField name="*_b"  type="boolean" indexed="true" stored="true"/>
    <dynamicField name="*_bs" type="boolean" indexed="true" stored="true"  multiValued="true"/>
    <dynamicField name="*_f"  type="float"  indexed="true"  stored="true"/>
    <dynamicField name="*_fs" type="float"  indexed="true"  stored="true"  multiValued="true"/>
    <dynamicField name="*_d"  type="double" indexed="true"  stored="true"/>
    <dynamicField name="*_ds" type="double" indexed="true"  stored="true"  multiValued="true"/>

    <!-- Type used to index the lat and lon components for the "location" FieldType -->
    <dynamicField name="*_coordinate"  type="tdouble" indexed="true"  stored="false" />
    <dynamicField name="*_gp"  type="location" indexed="true"  stored="true" />

    <dynamicField name="*_dt"  type="date"    indexed="true"  stored="true"/>
    <dynamicField name="*_dts" type="date"    indexed="true"  stored="true" multiValued="true"/>
    <dynamicField name="*_p"  type="location" indexed="true" stored="true"/>

    <!-- some trie-coded dynamic fields for faster range queries -->
    <dynamicField name="*_ti" type="tint"    indexed="true"  stored="true"/>
    <dynamicField name="*_tl" type="tlong"   indexed="true"  stored="true"/>
    <dynamicField name="*_tf" type="tfloat"  indexed="true"  stored="true"/>
    <dynamicField name="*_td" type="tdouble" indexed="true"  stored="true"/>
    <dynamicField name="*_tdt" type="tdate"  indexed="true"  stored="true"/>


    <!-- some trie-coded dynamic fields, MultiValued! for faster range queries -->
    <dynamicField name="*_tis" type="tint"    indexed="true"  stored="true" multiValued="true"/>
    <dynamicField name="*_tls" type="tlong"   indexed="true"  stored="true" multiValued="true"/>
    <dynamicField name="*_tfs" type="tfloat"  indexed="true"  stored="true" multiValued="true"/>
    <dynamicField name="*_tds" type="tdouble" indexed="true"  stored="true" multiValued="true"/>




To index a template field in SOLR, specify ``solr_column_name`` in field config & also ``"indexing": true`` in field JSON cfg.


Create a SOLR core using a configSet
----------------------------------------------
This command will create a new SOLR core named cb_mycore based on ``cb_default`` configSet. This command creates the folder ``/var/solr/data/cb_mycore`` and the ``core.properties`` file.


.. code-block:: bash

    > curl http://localhost:8983/solr/admin/cores?action=CREATE'&'name=cb_mycore'&'configSet=cb_default

Notice that '&' is quoted to make curl work

Usually you'll use ``CB/bin/create_core.php`` and the above will be performed for you.



SOLR Custom Schema
------------------------------------

If the default SOLR config is not enough (i.e. the dynamic fields are not a solution, for ex to have a text field with special Chinese stemming), then a custom SOLR schema should be used.


.. code-block:: xml

    <?xml version="1.0" encoding="UTF-8" ?>
    <!DOCTYPE config [
            <!ENTITY cb_solr_types  SYSTEM "cb_default/conf/cb_solr_types.xml">
            <!ENTITY cb_solr_fields SYSTEM "cb_default/conf/cb_solr_fields.xml">
    ]>

    <schema name="mycore" version="1.5">
      &cb_solr_types;
      &cb_solr_fields;

      <field name="custom_field1" type="tint" indexed="true" stored="true" multiValued="false" />

      <field name="custom_field2" type="tint" indexed="true" stored="true" multiValued="true" />
      </fields>
      ...


Notice the ``cb_default/conf/`` path. ``cb_default`` is a symlink to Casebox default SOLR configSet. Trying to include Casebox file using an absolute path will not work by default, because SOLR doesn't allow access to files outside its folder structure. You need to use -Dsolr.allow.unsafe.resourceloading=true during SOLR startup if you need absolute paths. This can be avoided using symlinks.

The picture below illustrates how to include system Casebox SOLR fields:

.. image:: /i/dev/solr-config.png

In this example, default Casebox field types and definitions are declared (cb_solr_types, cb_solr_fields), and then included later in types and fields sections. Finally, two custom fields are defined. (don't forget to reindex the core after you change SOLR schema and/or add new fields in CB, linked to custom 'solr_column'. You need to RELOAD the schema using SOLR Admin UI or API methods.

::

  http://localhost:8983/solr/admin/cores?action=RELOAD&core=cb_mycore



Create a SOLR core using a custom schema
------------------------------------------------------
CB_core related files are located in ``/CB/httpsdocs/cores/[core]``, create the folder if it doesn't exists yet. Your custom SOLR schema should be added to solr subfolder (created by the instructions below).


.. code-block:: bash

    # Copy solrconfig.xml
    # from: /CB/sys/solr_configsets/default_config/conf/
    # to: /CB/httpsdocs/cores/[core]
    > cp /var/www/html/casebox/sys/solr_configsets/default_config/conf/solrconfig.xml /var/www/html/casebox/httpsdocs/cores/[core]/solr/solrconfig.xml


.. code-block:: bash

    # Copy (or use your own) stopwords.txt
    # from: /CB/sys/solr_configsets/default_config/conf/
    # to: /CB/httpsdocs/cores/[core]
    > cp /var/www/html/casebox/sys/solr_configsets/default_config/conf/stopwords.txt /var/www/html/casebox/httpsdocs/cores/[core]/solr/stopwords.txt

    # Create symlink to CB SOLR configs
    > ln -s /var/www/html/casebox/sys/solr_configsets/default_config /var/www/html/casebox/httpsdocs/cores/[core]/solr/cb_default

    # Create 'core' folder in SOLR
    > mkdir /var/solr/data/cb_[core]
    > chown solr:solr /var/solr/data/cb_[core]

    # create symlink to custom SOLR config
    > ln -s /var/www/html/casebox/httpsdocs/cores/[core]/solr /var/solr/data/cb_[core]/conf

    # use SOLR CoreAdminAPI to create the core
    > curl http://localhost:8983/solr/admin/cores?action=CREATE'&'name=cb_[core]

    # ok, now cb_[core] SOLR core is ready



For Windows, symlinks are created as follows:

.. code-block:: bash

    mklink /J c:/var/www/casebox/httpsdocs/cores/[core]/solr/cb_default c:/var/www/casebox/sys/solr_configsets/default_config

    mklink /J c:/var/solr/data/cb_[core]/conf e:/var/www/casebox/httpsdocs/cores/[core]/solr




Deleting a SOLR core
-------------------------
To delete a SOLR core and all data:

.. code-block:: bash

    curl http://localhost:8983/solr/admin/cores?action=UNLOAD'&'core=cb_corename'&'deleteInstanceDir=true



SOLR Reindexing
------------------
Modified records in MySql database will be indexed in SOLR. If you need to reindex a core (you've moved it to another server and SOLR core is empty, or SOLR has been upgraded and full reindex is required) you need to call this script:

.. code-block:: bash

    > php -f /var/www/casebox/bin/solr_reindex_core.php -c demo -a -l

* ``-a`` mark all records to be reindexed.
* ``-l`` means no-limit. By default, up to 500 records are indexed in SOLR when you run the script. Specify -l to remove this limit. Notice: you might get an "Out of memory" error, just run the script again (but without -a of course).

``solr_reindex_core.php`` script is used to index MySql database into SOLR core. If ``-a`` parameter is specified, CB will only mark all records to be reindexed. ``-l`` parameter means that CB should attempt to reindex as much as possible (no limits). If the database is huge, you might get an "Out of memory error". Just launch reindexing again (but without "-a" as it will mark all records again).







.. _dynamicFields: https://cwiki.apache.org/confluence/display/solr/Dynamic+Fields
