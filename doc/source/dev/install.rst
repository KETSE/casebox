Install
=======

Prerequisites
*****************
* Apache 2.2.x with mod_rewrite & .htaccess support
* PHP 5.5.x with ``mbstring``
* MySQL 5.5.x with InnoDB
* JAVA JRE 8.x for SOLR
* `SOLR`_ 5.x
* ImageMagick 6.5.x + imagick PHP extension

Optionally, install OpenOffice/LibreOffice to get .docx, .xls, .odt preview.

Bare-bone server install
**************************
If you deploy Casebox on a freshly installed server, here are the instruction how to install all required software.

* :doc:`CentOS6 <install_centos6>`
* :doc:`install_centos7`
* :doc:`install_ubuntu`

.. toctree::
   :hidden:

   install_centos6
   install_centos7
   install_ubuntu



Download
********

Git is the recommended way to install Casebox: with a ``git pull`` you can upgrade easily (you may need to run some cleanup/upgrade scripts, but it's another topic). You can download a .zip `archive`_ and install

.. code-block:: bash

    > cd /var/www/
    > git clone https://github.com/KETSE/casebox.git

We use ``/var/www/casebox/``.
The ``/CB/`` notation is used to denote the folder where Casebox is extracted. In our example ``/CB/install/`` translates to ``/var/www/casebox/install/``


If apache runs under ``mod_php``, make sure CB folder have correct user/group. Usually it's webserver user/group . The default for Apache HTTPD server is ``apache/apache``. Chown the folder recursively:

.. code-block:: bash

  > chown -R apache:apache /var/www/casebox/

.. todo::

  Situation with permissions should be improved.
  Actually only a few folders should have apache:apache user/group, but we globally change the user/group for the entire CB folder. In next releases, the install script will take care of this, setting correct permissions for /data/, /logs/ (if these folders doesn't exists, create them, the install script will also do this in the future).


Install
********
Run ``/CB/bin/install.php`` script. Assuming we're in ``/CB/`` folder:
.. code-block:: bash

  > php bin/install.php


The script will ask you for the MySQL user/password, prefix for mysql&solr databases (``cb_`` by default), admin email. It will create a global ``/CB/httpsdocs/config.ini`` for all CB cores.

.. note::
    Casebox is designed for SAAS deployment: one CB install serving several independent cores (instances). A ``core`` is: a MySQL database, SOLR core and folder on the server for file storage.

CB configuration will use two MySql users:
* privileged user (mysql root): to create/drop databases, i.e. perform administrative tasks
* normal user: used only to work within the ``core`` database.

.. todo::

  show a diagram of global MySQL db and core databases


The privileged mysql user/password is not saved in CB settings for security reasons.

The install script will ask you the URL of the website, you can use https://127.0.0.1/, real server IP or a domain name.

At the end of CB installation, create a ``core`` based on CB bare-bone example (it has only the most important content types like folder/file/task).

In Apache 2.2 enable ``VirtualHost`` if not done already.



Apache configuration
********************

Use ``/CB/install/httpd/ssl_casebox.conf`` as an example how to add the CB virtualhost. ``ssl_casebox.conf`` includes SSL certificates. CB comes with self-generated certificates at ``/CB/install/httpd/ssl/``. You may also run CB under normal HTTP if you wish.

Make sure apache module ``mod_rewrite`` is enabled.

Restart apache server and try to access the URL of your CB core. Example: ``https://127.0.0.1/test/``




LibreOffice & unoconv
*********************

OpenOffice/LibreOffice is used to generate .docx, .odt, .ppt file preview (it converts them into HTML). If you need the feature: install LibreOffice.


CB relies on ``unoconv`` python script to call LibreOffice, launch it with the ``--listener`` option: it will load LibreOffice and be ready to server CB request for file preview conversion.

Launch unoconv from ``/CB/httpsdocs/libx/``:

.. code-block:: bash

  > unoconv --listener&



Cronjobs
********
Notifications by email of task creation/completion, extracting content from .docx, .pdf for fulltext indexing: these processes are done by cronjobs.

See ``/CB/install/cron/readme.txt`` for instructions: you need to edit the crontab of httpd user/group and add the following scrips:

.. code-block:: bash

    */2 * * * * php -f "/var/www/html/casebox/sys/crons/run_cron.php" -- -n send_notifications -c all
    */2 * * * * php -f "/var/www/html/casebox/sys/crons/run_cron.php" -- -n extract_files_content -c all
    */5 * * * * php -f "/var/www/html/casebox/sys/crons/run_cron.php" -- -n check_mail -c all
    */2 * * * * php -f "/var/www/html/casebox/sys/crons/cron_receive_comments.php"


.. todo::

    In future versions of CB, the install script will take care of this.


.. _SOLR: http://lucene.apache.org/solr/
.. _archive: https://github.com/KETSE/casebox/archive/master.zip
