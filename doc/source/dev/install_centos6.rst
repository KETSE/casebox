CentOS 6
==========

.. code-block:: bash

    ---- CentOS 6.6 --------------------------------------------------
    # check CentOs version
    > cat /etc/redhat-release

    # the default CentOS doesn't resolve 'localhost', and SOLR was showing errors during install
    # Add 127.0.0.1 in /etc/hosts
    127.0.0.1 localhost

    > yum install mc     (5.4 MB)


    ------ Install REMI repo -----------------------------------------
    ## Remi Dependency on CentOS 6 and Red Hat (RHEL) 6 ##
    > rpm -Uvh http://download.fedoraproject.org/pub/epel/6/i386/epel-release-6-8.noarch.rpm

    ## CentOS 6 and Red Hat (RHEL) 6 ##
    > rpm -Uvh http://rpms.famillecollet.com/enterprise/remi-release-6.rpm

    ----- Install: Apache, MySql, PHP  -------------------------------
    # Install Apache (httpd) Web server and PHP 5.6.6       ~37MB
    > yum --enablerepo=remi,remi-php56 install httpd php php-common


    ----- PHP --------------------------------------------------------

    # Install PHP 5.6.6 Modules
    > yum --enablerepo=remi,remi-php56 install php-pecl-apcu php-cli php-pear php-pdo php-mysqlnd php-pgsql php-pecl-mongo php-sqlite php-pecl-memcache php-pecl-memcached php-gd php-mbstring php-mcrypt php-xml

    # edit in /etc/php.ini
    > date.timezone = Europe/Zurich

    ----- Apache -----------------------------------------------------

    > yum install mod_ssl

    # Start Apache
    ## CentOS / RHEL 6.6/5.11 ##
    > chkconfig --levels 235 httpd on

    # configuration, add to httpd.conf
    NameVirtualHost *:443

    > cd /var/www/casebox
    > mkdir logs
    > chown apache:apache logs
    > mkdir data
    > chown apache:apache data


    #---- MySql 5.6  -------------------------------------------------
    > yum localinstall http://dev.mysql.com/get/mysql-community-release-el6-5.noarch.rpm
    > yum install mysql-community-server

    > /etc/init.d/mysql start ## use restart after update
    ## OR ##
    > service mysqld start ## use restart after update

    > chkconfig --levels 235 mysqld on

    > /usr/bin/mysql_secure_installation

    # Add 'local' user
    mysql> GRANT ALL ON *.* TO casebox@localhost IDENTIFIED BY 'casebox';
    mysql> FLUSH PRIVILEGES;


    --- Java JRE -----------------------------------------------------
    jre-8u40-linux-x64.rpm  from Oracle: http://www.oracle.com/technetwork/java/javase/downloads/jre8-downloads-2133155.html

    > rpm -Uvh /path/to/binary/jre-8u40-linux-x64.rpm


    ---- Various tools -----------------------------------------------
    > yum install git
    > yum install htop
    > yum install lftp
    > yum install lynx

    > yum install make
    > yum install gcc
    > yum --enablerepo=remi,remi-php56 install php-fpm php-devel php-pear
    > yum install ImageMagick ImageMagick-devel
    > pecl install imagick
    > echo "extension=imagick.so" > /etc/php.d/imagick.ini


    ---- SOLR --------------------------------------------------------
    Download SOLR5 in /tmp/

    # extracts the install_solr_service.sh script from the archive into the current directory.
    > tar xzf solr-5.0.0.tgz solr-5.0.0/bin/install_solr_service.sh --strip-components=2


    # run as root
    > sudo bash ./install_solr_service.sh solr-5.0.0.tgz

    # it is equivalent to:
    # > sudo bash ./install_solr_service.sh solr-5.0.0.tgz -i /opt -d /var/solr -u solr -s solr -p 8983



    ---- Casebox -----------------------------------------------------
    > cd /var/www/
    > git clone https://github.com/KETSE/casebox.git


Preview for Office files is generated using LibreOffice, you can install latest version from RPMs, see this article: `Install LibreOffice`_



.. _Install LibreOffice: http://www.if-not-true-then-false.com/2012/install-libreoffice-on-fedora-centos-red-hat-rhel/comment-page-3/