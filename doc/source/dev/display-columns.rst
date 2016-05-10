Display Columns (DC)
=======================

There is a central repository fo DC configs in ``/System/Config/DCConfigs/`` that can be referenced in:

* global search box (top-right corner, use ``search_DC`` config option and specify the DC ref)
* treeNode instance
* dbNode: in tree.cfg mysql field
* object form editor
* search templates
* onClick in object preview (todo)

Use 'DC_cfg' to reference a DC config by name. the 'DC' option can remain to specify the DC inline


Column config
------------------

::

    {
        "col_name": {
            "solr_column_name": "name",
            "title": "Col Title",
            "width": 70,   // width in pixels
            "extjs_cfg": val  // options for ExtJS grid column

      }
    }


If ``col_name`` is found in displayed objects, then column configuration is used from template field definition. Usually you'll specify ``DC`` without additional column settings:

::

    {
        "col_name1": {},
        "col_name2": {},
        "col_name3": {}
      }
    }



Custom columns for a node
-----------------------------
DC is specified in field 'cfg' of 'tree' mysql table for the node.

::

    {
      "DC": {
        "nr": {}
        ,"case_type": {}
        ,"statut": {}
        ,"substatut": {}
        ,"interview_personne": {}
        ,"requerant_type": {}
        ,"victim_province": {}
      }
    }


Custom columns for a template type
--------------------------------------
Specified in 'Config' field of the Template


Custom columns for a virtual folder plugin
----------------------------------------------
Specified in plugin configuration: 'treeNodes'.

::

    {
        ,"CasesByLawyers": {
          "pid": 1
           ,"class": "core\\TreeNode\\CasesByLawyers"
           ,"DC": {
               "nr": {
                   "solr_column_name": "reg_nr"
               },
               "other_sender_fullname": {}
           }
        }

        ,"MyCases": {
          "pid": 1
           ,"class": "core\\TreeNode\\MyCases"
           ,"DC": {
               "nr": {"solr_column_name": "reg_nr"},
               "other_sender_fullname": {}
           }

        }
    }

