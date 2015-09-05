FacetedNav
==========


.. code-block:: json

  {
    "class": "CB\\TreeNode\\FacetNav"

    ,"pid": 1

    ,"iconCls": "icon-case"
    ,"title_en": "Cases"
    ,"title_ru": "Дела"

    ,"fq": [
      "template_id: 13114"
    ]

    ,"level_fields": "assigned, task_status"


    ,"DC": {}

    ,"facets": ["facet1", "facet2"]

    ,"stats": [
        {"field": "invoice"
         ,"title_en": "Invoice"
         ,"title_ru": "Счет-фактура"
       }
       ,{"field": "fieldname2"
       }
    ]


    ,"sort": {
      "property": "date"
      ,"direction": "DESC"
    }


    ,"view": "grid"

    ,"views": {

        "chart": {
            "chart_type": "bar"
            ,"facet": "user_ids"
            ,"sort": "name"
            ,"direction": "desc"
            ,"stats": {
            }
        },


        "pivot": {
           "pivot_type": "table"

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
    }

    ,"show_count": true

    ,"show_in_tree":  true
  }

