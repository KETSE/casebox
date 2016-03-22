#!/bin/bash

set -e
set -x

DIR=$(realpath $(dirname "$0"))
ROOT=$(realpath "$DIR/../..")

mysql -u root -e "CREATE USER 'test'@'%' IDENTIFIED BY 'test'"
mysql -u root -e "CREATE USER 'test'@'localhost' IDENTIFIED BY 'test'"
mysql -u root -e "GRANT ALL PRIVILEGES ON *.* TO 'test'@'%'"
mysql -u root -e "GRANT ALL PRIVILEGES ON *.* TO 'test'@'localhost'"
mysql -u test --password=test -b 

echo "create MySQL database" 
mysql -u test --password=test -e "create database IF NOT EXISTS cb_default;"
mysql -u test --password=test -b cb_default < $ROOT/var/backup/cb_default.sql
echo "copy and install solr 5.5.0"
export SOLR_VERSION="5.5.0"
export SOLR_PORT="8983"
bash $DIR/solr/solr5.sh --install

# may take few seconds to start and may not be available when the script is executed
sleep 3
echo "add solr test_log"
export SOLR_CORENAME="test_log"
export SOLR_CONFIGSET="/var/solr/log/conf"
bash $DIR/solr/solr5.sh --addcore
echo "add solr core test"
export SOLR_CORENAME="test"
export SOLR_CONFIGSET="/var/solr/default/conf"
bash $DIR/solr/solr5.sh --addcore

#install nginx 
$DIR/nginx/install-nginx.sh

