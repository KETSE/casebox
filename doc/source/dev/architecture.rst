Casebox Architecture
======================

Once deployed on a server, Casebox (CB) can serve many instances (called 'cores'). It's similar to "WordPress multisite" that allows management of multiple sites from one WordPress installation. The aim was to easily deploy an SAAS version on a physical server.

A core is represented by:

one MySql database.
one SOLR core

Global CB configuration is stored in mysql database 'cb__casebox'.

The list of CB cores are managed in ``cb__casebox.cores`` table. The URL of a core is ``https://www.domain.com/[coreName]/``. The name of the mysql database of a core starts with ``cb_``. Example: ``cb_demo``. The prefix can be changed in ``config.ini`` file in ``[database]`` section.

Overall architecture:

.. image:: /i/dev/architecture.png

::

    casebox
     +-data
     |  +-files               All files are stores in this subfolder per core
     |  |  +-[core1]
     |  |  |...
     |  |  +-[coreN]
     |  |
     |  +-tmp                 Temporary core files (uploads), minified JS, CSS
     |
     +-httpsdocs              DocumentRoot for apache
     |  +-classes             Casebox PHP classes
     |  |
     |  +-cores
     |  |  +-[core1]          Each core can define PHP, JS, SOLR config etc.
     |  |  |...
     |  |  +-[coreN]
     |  +-js                  JS classes: ExtJS UI
     |  |
     |  +-libx                All external libraries used by CB
     |  |
     |  +-config.ini          mysql/solr connection params, session lifetime
     |  |
     |  +-system.ini          mostly used to set path to unoconv util, TIKA server
     |
     +-install
     +-logs                   Each core has its own error log
     +-sys                    Crontab php scripts and utilities
     +-tests                  PHPUnit tests


Framework configuration
-----------------------
The configuration of CB is stored in mysql table ``cb__casebox.config``. Here is the list of most important settings:

+---------------------------+------------------------------------+------------------------------------+
|param                      |  value                             | Info/Example                       |
+===========================+====================================+====================================+
|general                    |                                    |                                    |
+---------------------------+------------------------------------+------------------------------------+
|- timezone                 | UTC                                | default timezone for all cores     |
+---------------------------+------------------------------------+------------------------------------+
|- default_facet_configs    | json                               | definition of facets available     |
|                           |                                    | in all cores                       |
+---------------------------+------------------------------------+------------------------------------+
|- node_facets              | json                               | default folder facets              |
|                           |                                    | usually defined for root folder    |
+---------------------------+------------------------------------+------------------------------------+
|- action_log               | | {                                |  common SOLR core                  |
|                           | | "core": "cb_log"                 |  for activity log                  |
|                           | | ,"retention_days": 30            |                                    |
|                           | | ,"host": "127.0.0.1"             |                                    |
|                           | |  }                               |                                    |
+---------------------------+------------------------------------+------------------------------------+
|- default_object_plugins   | ["objectProperties", "comments"]   | plugins enabled in the right       |
|                           |                                    | preview panel                      |
+---------------------------+------------------------------------+------------------------------------+
|                           |                                    |                                    |
+---------------------------+------------------------------------+------------------------------------+
|- default_language         | en                                 |                                    |
+---------------------------+------------------------------------+------------------------------------+
|- languages                | en,fr,ru                           | | comma separated list of UI       |
|                           |                                    | | languages enabled for all cores  |
+---------------------------+------------------------------------+------------------------------------+
|- - language_[lang]        | | {                                | date/time format settings          |
|                           | | "name": "English"                |                                    |
|                           | | ,"locale": "en_US"               |                                    |
|                           | | ,"long_date_format": "%F %j, %Y" |                                    |
|                           | | ,"short_date_format": "%m/%d/%Y" |                                    |
|                           | | ,"time_format": "%H:%i"          |                                    |
|                           | | }                                |                                    |
+---------------------------+------------------------------------+------------------------------------+
|- images_display_size      | nr_of_bytes                        | | Bigger files will not be shown   |
|                           |                                    | | in preview panel                 |
+---------------------------+------------------------------------+------------------------------------+
|- default_DC               | string                             | default Display Columns            |
+---------------------------+------------------------------------+------------------------------------+
|- oauth2_credentials_google| json                               |                                    |
+---------------------------+------------------------------------+------------------------------------+



files
^^^^^^^^^^^^^
``max_versions``
Number of file versions per filetype.

``edit``
what type of files are editable withing CB

``text``
https://ace.c9.io/ Ace editor with syntax highlighting

``html``
WYSIWYG editor

``webdav``
editing files with Office package installed on your computer via WebDav protocol

::

    {
      "max_versions": "*:1;php,odt,doc,docx,xls,xlsx:20;pdf:5;png,gif,jpg,jpeg,tif,tiff:2;"

      ,"edit" : {
        "text": "txt,php,js,xml,csv,log"
        ,"html": "html,htm"
        ,"webdav": "doc,docx,ppt,pptx,dot,dotx,xls,xlsx,xlsm,xltx,ppt,pot,pps,pptx,odt,ott,odm,ods,odg,otg,odp,odf,odb"
      }
    }



oauth2_credentials_google
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

::

        {
            "web": {
                "client_id": "952049165442-cm5b45fe0c2cifl034b0ehojs8u4a3.apps.googleusercontent.com",
                "auth_uri": "https://accounts.google.com/o/oauth2/auth",
                "token_uri": "https://accounts.google.com/o/oauth2/token",
                "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
                "client_secret": "LzxKYg5mMnBWWbn92626d5QPN",
                "redirect_uris": ["https://dev.casebox.org/oauth2callback"],
                "javascript_origins": ["https://dev.casebox.org"]
            }
        }


default_DC
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
a reference to ``/System/Config/DCConfigs/[grid_DC_cfg]``

debug_hosts
^^^^^^^^^^^^^^^^^^
list of IPs for which all serverside warnings will be displayed. Used by developers only. See Debugging

