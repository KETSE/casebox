FacetedNav
==========

.. code-block:: json

  ,"pluginId": {
    "class": "CB\\TreeNode\\FacetNav"

    ,"pid": 1

    ,"iconCls": "icon-case"
    ,"title_en": "Cases"
    ,"title_ru": "Дела"

    ,"fq": [
      "template_id: 13114"
    ]

    // fields defined in 'facet_configs', used to generate subfolders for root node
    ,"level_fields": "assigned, task_status"

    //final results config
    ,"DC": ...

    // facets should be defined in mysql config table in 'facet_configs'
    ,"facets": ["facet1", "facet2"]

    // Enable statistical function for specified fields
    ,"stats": [
        {"field": "invoice"   // a SOLR field
         ,"title_en": "Invoice"
         ,"title_ru": "Счет-фактура"
       }
       ,{"field": "fieldname2"
         ...
       }
    ]

    // by which column to sort the results
    ,"sort": {
      "property":"date"
      ,"direction":"DESC"
    }

    //view config: grid, charts, pivot, calendar
    ,"view": 'grid'

    //charts example
    ,"view": {
      "type": "charts"
      ,"chart_type": "bar"   // column, pie
      ,"facet": "user_ids"   // one of available facets
      ,"sort": "name"        // name | count
      ,"direction": "desc"
      ,"stats": {
          ... see Pivot view "stats" cfg
      }
    }


    // pivot example (cant afford sorting for pivot views right now)
    ,"view": {
        "type": "pivot"
       ,"pivot_type": "table" // stackedBars, stackedColumns (string or array)

       // by default, enable Stats for a field
       ,"stats": {
           "field": "invoice"
           ,"type": "sum",
       }

       ,"rows": {
          "facet": "color"
          ,"sort": "name"
          ,"direction": "asc"
       }
       ,"cols": {
          "facet": "country"
          ,"sort": "count"
          ,"direction": "desc"
       }
    }

    // display total numer of records per folder
    ,"show_count": true

    // show actual records as the last tree level
    ,"show_in_tree":  true
  }

