Ubuntu 14.04
==============

Thanks to @RafaPolit for this guide.

# Casebox on Ubuntu 14.04

Start with a clean install and make sure the software is up to date:
`$ sudo apt-get update`

## Install Apache 2.4
`$ sudo apt-get install apache2`
`$ sudo a2enmod rewrite`
`$ sudo a2enmod headers`
`$ sudo a2enmod ssl`

#------------------------------------------------
For *developer* mode on local server
`$ echo "ServerName localhost" | sudo tee /etc/apache2/conf-available/fqdn.conf`
`$ sudo a2enconf fqdn`

#------------------------------------------------
`sudo service apache2 restart`

## Install MySQL 5.x
* When configuring root password, the casebox install script had issues with special characters.  Avoid quotes and other characters to prevent conflicts:

`$ sudo apt-get install mysql-server php5-mysql`
`$ sudo mysql_install_db`
For security reasons, run:
`$ sudo mysql_secure_installation`

## Insatll PHP 5.x
`$ sudo apt-get install php5 libapache2-mod-php5 php5-mcrypt php5-mysqlnd`

## Install JAVA (1.7+)
`$ sudo apt-get install default-jdk`

## Install SOLR 5.2
- Navigate to: http://lucene.apache.org/solr/
- Click on Download
- Select a Mirror
- Download: **solr-5.X.X.tgz** (as of this writting solr-5.2.0.tgz)
- On the terminal cd to the donwload location

`$ tar xzf solr-5.2.0.tgz solr-5.2.0/bin/install_solr_service.sh --strip-components=2`
`$ sudo bash ./install_solr_service.sh solr-5.2.0.tgz`

Follow on-screen instructions and then confirm by running
`$ sudo service solr status`

## Install Imagemagick and imagick for php
`$ sudo apt-get install imagemagick`
`$ sudo apt-get install php5-imagick`
`$ sudo php5enmod imagick`
`$ sudo service apache2 restart`

## Clone and install the project
- cd to **/var/www/html** (default Apache 2.4 directory under Ubuntu)

`$ sudo apt-get install git`
`$ sudo git clone https://github.com/KETSE/casebox.git`
`$ sudo mkdir /var/www/html/casebox/logs`
`$ sudo mkdir /var/www/html/casebox/data`
`$ sudo touch /var/www/html/casebox/httpsdocs/config.ini`
`$ sudo chown -R www-data:www-data /var/www/html/casebox/`

#------------------------------------------------
For *developer* mode on local server
`$ cd casebox`
`$ sudo git checkout devel`
`$ sudo chown -R www-data:www-data /var/www/html/casebox/`

#------------------------------------------------

## Run the Casebox install.php
`$ sudo php bin/install.php`

__Important !__
- Default Apache owner on Ubuntu is **www-data**, not *apache*, be sure to override that default.
- We are configuring later on Casebox virtual host to be casebox.local and on port 80 non-https, so we sugest **http://casebox.local/** as the base URL.

## Configure Apache for Local Development non-SSL on port :80

- edit file /etc/hosts to include this line to configure your hostname as desired.  For our own example, we are using **casebox.local**

`127.0.0.1  casebox.local`
- Add this to the **/etc/apache2/sites-available/000-default.conf** file (changing to your own configuration options for names, ports, etc.).  We are overriding the default port:80 localhost, so, you may need to change or delete the default Apache virtual host.  You may also configure the casebox virtual host on a different port

.. code-block:: xml

    <VirtualHost *:80>
        # change it to your devel/production domain
        ServerName casebox.local

        ServerAdmin admin@domain.com

        # Windows ------------------------------------------
        # DocumentRoot "c:/var/www/casebox/httpsdocs"
        # CustomLog c:/var/www/casebox/logs/ssl_access_log common
        # ErrorLog  "c:/var/www/casebox/logs/ssl_error_log"

        # Linux --------------------------------------------
        DocumentRoot "/var/www/html/casebox/httpsdocs"
        CustomLog /var/www/html/casebox/logs/ssl_access_log common
        ErrorLog  "/var/www/html/casebox/logs/ssl_error_log"

        # SSLEngine on
        # SSLVerifyClient none

        # provide your own SSL certificates or remove SSL support and use CaseBox via http
        # you may change the location of SSL certificates

        # Windows ------------------------------------------
        # SSLCertificateFile c:/var/www/casebox/install/httpd/ssl/casebox.crt
        # SSLCertificateKeyFile c:/var/www/casebox/install/httpd/ssl/casebox.key

        # Linux
        # SSLCertificateFile /var/www/html/casebox/install/httpd/ssl/casebox.crt
        # SSLCertificateKeyFile /var/www/html/casebox/install/httpd/ssl/casebox.key

        # SSLProtocol All -SSLv2 -SSLv3
        # SSLCipherSuite ECDHE-RSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-SHA384:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-SHA256:ECDHE-RSA-AES256-SHA:!RC4:HIGH:!MD5:!aNULL:!EDH


        # SSLHonorCipherOrder on
        # Add six earth month HSTS header for all users...
        Header add Strict-Transport-Security "max-age=15768000"

        # HTTP Security Headers
        Header add X-Content-Type-Options no-sniff
        Header add X-Download-Options noopen
        # Header add X-Frame-Options deny
        Header add X-XSS-Protection "1; mode=block"

        # Windows ------------------------------------------
        # <Directory c:/var/www/casebox/httpsdocs>
        # Linux
        <Directory /var/www/html/casebox/httpsdocs>
        <IfModule mod_php5.c>
            php_admin_flag engine on
            php_admin_flag safe_mode off

            # Windows ------------------------------------------
            # php_admin_value open_basedir "c:/var/www/casebox/;c:/windows/temp;"

            # Linux --------------------------------------------
            # php_admin_value open_basedir "/var/www/html/casebox/:/usr/lib64/libreoffice:/tmp"

            php_admin_value max_execution_time 300
            php_admin_value short_open_tag off

            php_admin_value upload_max_filesize 200M
            php_admin_value post_max_size 200M
            php_admin_value max_file_uploads 20
            php_admin_value memory_limit 200M

            php_admin_value expose_php Off
        </IfModule>

        #SSLRequireSSL
        Options -Includes -ExecCGI
        AllowOverride All
        </Directory>
    </VirtualHost>



`$ sudo service apache2 restart`

