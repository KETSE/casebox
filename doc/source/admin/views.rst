Views
==============

Grid
--------------


Activity Stream
-----------------


Charts
------------------


Pivot table / Stacked bar charts
------------------------------------


Map
--------------------

To display records on a map, first specify the ``field`` that contains the Location. In the example below it's ``location_gp``
``*_gp`` is a dynamic SOLR field for LatLong.

.. note:: The ``_gp`` field should be ``indexed`` in SOLR

.. code-block:: json

    "view": {
        "type": "map"
        ,"field": "location_gp"

        ,"url": "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
        ,"minZoom": 1
        ,"maxZoom": 10

        ,"defaultLocation": {
            "lat": 38.8033
            ,"lng": -95.1121
            ,"zoom": 12
        }
    }

