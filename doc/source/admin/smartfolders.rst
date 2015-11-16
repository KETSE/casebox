Smart Folders
=============

The virtual folder sets the display columns, filters
http://docs.casebox.org/en/latest/dev/tree.html

Suppose you have a folder with 1000 products. A product has Country and Color fields. You'd like to browse products by Country and then by Color (or vice-versa). ``FacetedNav`` node class is able to do this.

How to configure ``FacetedNav``:

1. the filter ``fq``: CB will perform a SOLR query with the filter applied.
2. the levels ``level_fields``: search results will be grouped by given fields and the groups displayed in the tree.


.. code-block:: json
    :emphasize-lines: 9,12

    {
      "class": "CB\\TreeNode\\FacetNav"
      ,"pid": 1
      ,"iconCls": "icon-product"
      ,"title_en": "Products"
      ,"fq": [
        "template_id: 13114"
      ]

      ,"level_fields": "country, color"
      ,"show_count": true
      ,"show_in_tree":  true
    }


``level_fields`` should be defined in :doc:`filters`. These are fields indexed in SOLR.

Configuration options
***********************

iconCls
-------------
Icon CSS class for root node

title_en
----------------
Root node title for a given UI language


show_count
-------------------
display total number of records

show_in_tree
-------------------
The last group node in the tree is expandable and will show actual records. Useful to browse 'folder' type nodes (i.e. cases that contains other subfolders and records)


view
-------------
how to display results:

* grid
* chart
* pivot: Pivot table
* calendar
* stream: Activity stream

view: grid
------------


DC
-------------
``DC`` means 'display columns': the list of grid columns when view==grid


facets
-------------
available facets in Filter panel


stats
-------------
Statistics functions for given fields


sort
--------------
default sorting when view=grid


view: chart
--------------


view: pivot
--------------

view: calendar
--------------

view: stream
--------------
Activity stream shows search results ordered by last_action_date (update, comment, any action performed on a node).





Extended example
***********************

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

