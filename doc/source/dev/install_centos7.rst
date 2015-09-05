CentOS 7.1
===========

.. note::

        After a minimal CentOS7.1 install in VMWare, the network is not accessible.
        See these articles:

        * http://ask.xmodulo.com/configure-static-ip-address-centos7.html
        * https://geekflare.com/no-internet-connection-from-vmware-with-centos-7/

        I've added ``ONBOOT=yes`` to ``/etc/sysconfig/network-scripts/ifcfg-eno16777736`` and rebooted server.

        To get the IP of the server, run: ``> ip add``


.. code-block:: bash

    ---- CentOS 7 ---------------------------------------------------
    # check CentOs version
    > cat /etc/redhat-release

    # install Midnight Commander for a more comfortable interaction with the server
    # it has lots of perl-* package dependencies, in total it will be 42 M installed size
    > yum install mc
    > yum install unzip
    # required by SOLR
    > yum install lsof


    # Disable SELINUX
    # TODO: find a way to deploy Casebox with SELINUX enabled
    # edit /etc/sysconfig/selinux
    # set  SELINUX=disabled


    ------ Install EPEL, REMI, Webtatic repos ------------------------
    # EPEL
    > rpm -Uvh https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm

    # REMI
    > rpm -Uvh http://rpms.famillecollet.com/enterprise/remi-release-7.rpm

    # Webtatic
    > rpm -Uvh http://repo.webtatic.com/yum/el7/webtatic-release.rpm


    --- Update CentOS -----------------------------------------------
    # update the OS
    > yum update

    # Also see this article, you may perform other actions right after minimal CentOS install
    # http://www.tecmint.com/things-to-do-after-minimal-rhel-centos-7-installation/


    ------ FirewallD on CentOS7 --------------------------------------
    # CentOS7 has a Firewall enabled by default
    # see: https://www.digitalocean.com/community/tutorials/how-to-set-up-a-firewall-using-firewalld-on-centos-7
    # to open a port:
    > firewall-cmd --add-port=8983/tcp


    ----- Install MySql 5.6 ---------------------------------------
    # CentOS replaced Mysql with MariaDB in its official yum repository.
    # see this: http://serverlab.org/view/8/How-to-install-latest-mysql-5.6-on-CentOS7

    # Add mysql community into your rpm repo
    > yum install http://dev.mysql.com/get/mysql-community-release-el7-5.noarch.rpm

    # Install MySql
    > yum install mysql-community-server

    # open mysql service in firewall
    >  firewall-cmd --add-service=mysql --permanent

    # tweak /etc/my.ini
    # max_allowed_packet = 32M

    > /usr/bin/systemctl enable mysqld
    > /usr/bin/systemctl start mysqld
    > /usr/bin/mysql_secure_installation

    # Set root password? [Y/n] Y
    # Remove anonymous users? [Y/n] Y
    # Disallow root login remotely? [Y/n] Y
    # Remove test database and access to it? [Y/n] Y
    # Reload privilege tables now? [Y/n] Y

    # Create a mysql user to access CB databases on localhost
    # change cb_user/cb_password
    > mysql -u root -p
    > GRANT ALL ON *.* TO 'cb_user'@'localhost' IDENTIFIED BY 'cb_password';


    ----- Enable REMI & REMI PHP 5.6 repos ---------------------------
    # read this or just perform below actions:
    # https://www.mojowill.com/geek/howto-install-php-5-4-5-5-or-5-6-on-centos-6-and-centos-7/

    # update file /etc/yum.repos.d/remi.repo, Enable [remi] and [remi-php56] repos using enabled=1


    # Install PHP56
    # some modules you may add: php-pecl-mongo php-sqlite php-pecl-memcache php-pecl-memcached
    >  yum install php php-gd php-mysql php-mcrypt php-mbstring php-xml php-pear php-pdo php-pecl-apcu php-devel

    # edit in /etc/php.ini and set your Timezone to remove PHP warning. It will not affect Casebox Date/Time as it stores all dates in UTC format
    > date.timezone = Europe/Zurich


    ----- Apache 2.4 -------------------------------------------------
    # CentOS 7.1 comes with Apache 2.4.6 preinstalled and running.
    # enable http/https in firewall
    > firewall-cmd --add-service=http --permanent
    > firewall-cmd --add-service=https --permanent
    > firewall-cmd --reload

    # TODO: how to upgrade to latest apache 2.4.x ?

    # Install mod_ssl
    > yum install mod_ssl

    # autostart apache server
    > systemctl enable httpd.service

    ------ Utils: wget, git, gcc ...  --------------------------------
    > yum install wget
    > yum install git
    > yum install gcc

    ----- Java 8 JRE -------------------------------------------------
    # see detailed instructions here
    # http://tecadmin.net/install-java-8-on-centos-rhel-and-fedora/
    > tar xzf jre-8u60-linux-x64.tar.gz
    > cd /opt/jre1.8.0_60/
    > alternatives --install /usr/bin/java java /opt/jre1.8.0_60/bin/java 2
    > alternatives --config java


    -------- ImageMagick ---------------------------------------------
    # From REMI
    > yum install ImageMagick-last ImageMagick-last-devel
    > pecl install imagick
    > echo "extension=imagick.so" > /etc/php.d/imagick.ini

    # check imagick PHP module
    > php --ri imagick


    ---- SOLR --------------------------------------------------------
    Download SOLR5 in /tmp/

    # extracts the install_solr_service.sh script from the archive into the current directory.
    > tar xzf solr-5.3.0.tgz solr-5.3.0/bin/install_solr_service.sh --strip-components=2


    # run as root
    > sudo bash ./install_solr_service.sh solr-5.3.0.tgz

    # it is equivalent to:
    # > sudo bash ./install_solr_service.sh solr-5.3.0.tgz -i /opt -d /var/solr -u solr -s solr -p 8983

    # Opening port 8983 for SOLR. NOTICE: you should allow access to this port only for admin IPs
    > firewall-cmd --add-port=8983/tcp --permanent


    ---- Casebox -----------------------------------------------------
    > cd /var/www/
    > git clone https://github.com/KETSE/casebox.git

    # make sure user/group is correct.
    # under mod_php, apache usually runs as apache:apache
    > chown -R apache:apache /var/www/casebox/

    > cd /var/www/casebox/
    > php bin/install.php


    # Add cb/install/httpd/ssl_casebox.conf from CB to
    # your Apache config, change hostname,
    # uncomment Windows/Linux sections


    # TODO
    # we have to check if required folders are created by CB install script, you may have to manually do
    > cd /var/www/casebox
    > mkdir logs
    > chown apache:apache logs
    > mkdir data
    > chown apache:apache data



Preview for Office files is generated using LibreOffice, you can install latest version from RPMs, see this article: `Install LibreOffice`_


.. _Install LibreOffice: http://www.if-not-true-then-false.com/2012/install-libreoffice-on-fedora-centos-red-hat-rhel/comment-page-3/