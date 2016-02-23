Core configuration
=====================

The configuration of a core is stored in mysql table ``cb_coreName.config`` and accessible from Casebox ``/System/Config/`` folder. When you update a config option in CB, it's saved in MySql table.

project_name_$lang
^^^^^^^^^^^^^^^^^^^^
Application title displayed in the window title of the browser (html page title).


folder_templates
^^^^^^^^^^^^^^^^^^^
Templates displayed as folder in the Tree

default_folder_template
^^^^^^^^^^^^^^^^^^^^^^^^

default_file_template
^^^^^^^^^^^^^^^^^^^^^^^^

default_task_template
^^^^^^^^^^^^^^^^^^^^^^^^

default_event_template
^^^^^^^^^^^^^^^^^^^^^^^^


default_language
^^^^^^^^^^^^^^^^^^^

languages
^^^^^^^^^^^^^^^^^^^

treeNodes
---------------


node_facets
^^^^^^^^^^^^^^^^^


facet_configs
^^^^^^^^^^^^^^^^^

maintenance_cfg
^^^^^^^^^^^^^^^^^^^^


::

    {
    "start_time": "17:00 UTC TIME",
    "end_time": "18:45 UTC TIME",
    "allowed_IP": "129.115.72.81,127.0.0.1",
    "auto_enable": false
    }

maintenance_mode
^^^^^^^^^^^^^^^^^
on, off


DCConfigs
=====================


leftRibbonButtons
=====================


favBtn
^^^^^^^^^^^^^^^

::

    {
    "title": "Favs"
    ,"path": "1/1-favorites"
    ,"iconCls": "i-star-negative"
    }



geoMapping
^^^^^^^^^^^^^^
true/false


