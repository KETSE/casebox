#!/bin/bash

set -e
set -x
apt-get install nginx
sudo apt-get install php7.0-fpm

DIR=$(realpath $(dirname "$0"))
USER=$(whoami)
PHP_VERSION=$(phpenv version-name)
ROOT=$(realpath "$DIR/../../..")
PORT=9000
SERVER="/tmp/php.sock"

function tpl {
    sed \
        -e "s|{DIR}|$DIR|g" \
        -e "s|{USER}|$USER|g" \
        -e "s|{PHP_VERSION}|$PHP_VERSION|g" \
        -e "s|{ROOT}|$ROOT|g" \
        -e "s|{PORT}|$PORT|g" \
        -e "s|{SERVER}|$SERVER|g" \
        < $1 > $2
}

tpl .travis_nginx.conf /etc/nginx/nginx.conf
/etc/init.d/nginx restart