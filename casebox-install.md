
sudo apt-get install -y mysql-server
mysql -u root
CREATE USER 'local'@'localhost' IDENTIFIED BY 'h0st';
GRANT ALL PRIVILEGES ON * . * TO 'local'@'localhost';
sudo nano /etc/mysql/my.cnf
**add this line**
    
[mysqld]
sql-mode="ONLY_FULL_GROUP_BY,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION"
```
This removes the `STRICT_TRANS_TABLES` mode for MySQL version 5.7 and newer [https://dba.stackexchange.com/a/48745] to prevent the error below and and enable updating of items in Casebox.
```
Query error (cb_xxx): Incorrect datetime value: '2018-01-01T00:00:00Z' for column 'cdate' at row 1
``` 

sudo service mysql restart
sudo apt-get install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt-get update
sudo apt-get install -y php5.6 php5.6-mbstring php5.6-mcrypt php5.6-mysql php5.6-xml php5.6-curl
sudo php -v
sudo add-apt-repository ppa:webupd8team/java
sudo apt-get update
sudo apt-get install -y oracle-java8-installer

cd ~
wget http://archive.apache.org/dist/lucene/solr/5.5.5/solr-5.5.5.tgz
tar xzf solr-5.5.5.tgz solr-5.5.5/bin/install_solr_service.sh --strip-components=2
sudo bash ./install_solr_service.sh solr-5.5.5.tgz
sudo service solr status
sudo a2enmod rewrite
sudo a2enmod headers
sudo service apache2 restart
cd /var/www/
sudo apt-get install -y git
git clone https://github.com/huridocs/casebox.git
sudo git clone https://github.com/huridocs/casebox.git
sudo chown -R www-data:www-data /var/www/casebox/ (for ubuntu/debian)
sudo chown -R apache:apache /var/www/casebox/ (for CentOS/redhat)
sudo php casebox/bin/install.php
sudo php -f /var/www/casebox/bin/core_create.php -- -c demo -s /var/www/casebox/install/mysql/bare_bone_core.sql

cd /etc/apache2/sites-available/
sudo nano casebox.conf
<VirtualHost *:80>
DocumentRoot /var/www/casebox/httpsdocs
ServerName casebox.local
ServerAdmin webmaster@localhost
ErrorLog /var/log/apache2/error.log
CustomLog /var/log/apache2/access.log combined

    #SSLEngine on
    #SSLVerifyClient none

    # provide your own SSL certificates or remove SSL support and use CaseBox via http
    # you may change the location of SSL certificates

    # Linux
    #SSLCertificateFile /var/www/html/casebox/install/httpd/ssl/casebox.crt
    #SSLCertificateKeyFile /var/www/html/casebox/install/httpd/ssl/casebox.key

    #SSLProtocol All -SSLv2 -SSLv3
    #SSLCipherSuite ECDHE-RSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-SHA384:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-SHA256:ECDHE-RSA-AES256-SHA:!RC4:HIGH:!MD5:!aNULL:!EDH


    # SSLHonorCipherOrder on
    # Add six earth month HSTS header for all users...
    # Header always set Strict-Transport-Security "max-age=15768000"

    # HTTP Security Headers
    Header always set X-Content-Type-Options no-sniff
    Header always set X-Download-Options noopen
    Header always set X-Frame-Options deny
    Header always set X-XSS-Protection "1; mode=block"

    # Linux
<Directory /var/www/casebox/httpsdocs>
    <IfModule mod_php5.c>
        php_admin_flag engine on
        php_admin_flag safe_mode off

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
    Require all granted
</Directory>
</VirtualHost>
sudo service apache2 restart
sudo apt-get clean -y
sudo apt-get autoclean -y