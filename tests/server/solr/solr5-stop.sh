#!/usr/bin/env bash

set -e

cd $(dirname $0)

export SOLR_VERSION=${SOLR_VERSION:-5.2.0}
export SOLR_NAME="solr-$SOLR_VERSION"
export SOLR_DIR="`pwd`/${SOLR_NAME}"
export SOLR_PORT=${SOLR_PORT:-8983}



echo "Changing dir into ${SOLR_DIR}"
cd $SOLR_DIR

export CMD="bin/solr stop -p ${SOLR_PORT}"
echo "Stop server on port ${SOLR_PORT}"
exec $CMD


