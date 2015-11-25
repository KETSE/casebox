#!/usr/bin/env bash

set -e

cd $(dirname $0)

export SOLR_VERSION=${SOLR_VERSION:-5.2.0}
export SOLR_NAME="solr-$SOLR_VERSION"
export SOLR_DIR="`pwd`/${SOLR_NAME}"
export SOLR_PORT=${SOLR_PORT:-8983}
export SOLR_SOURCE_URL="http://archive.apache.org/dist/lucene/solr/${SOLR_VERSION}/${SOLR_NAME}.tgz"

export SOLR_ARCHIVE="${SOLR_NAME}.tgz"



#if $SOLR_DIR not exist then try to download solr and extrat
if [ ! -e $SOLR_DIR ]; then

    if [ -d "${HOME}/download-cache/" ]; then
        export SOLR_ARCHIVE="${HOME}/download-cache/${SOLR_ARCHIVE}"
    fi

    if [ -f ${SOLR_ARCHIVE} ]; then
        # If the tarball doesn't extract cleanly, remove it so it'll download again:
        tar -tf ${SOLR_ARCHIVE} > /dev/null || rm ${SOLR_ARCHIVE}
    fi


    if [ ! -f ${SOLR_ARCHIVE} ]; then
        echo "Download ${SOLR_NAME} from ${SOLR_SOURCE_URL}"
        curl -Lo $SOLR_ARCHIVE $SOLR_SOURCE_URL
    # wget -nv --output-document=`pwd`/$SOLR.tgz $SOLR_SOURCE_URL
    fi

    echo "Extracting Solr ${SOLR_ARCHIVE} to ${SOLR_DIR}"

    tar -xf $SOLR_ARCHIVE

fi

if [ -d "${TRAVIS_BUILD_DIR}/sys/solr_configsets" ]; then
    echo "Create  configsets in ${SOLR_DIR}"
    mkdir "${SOLR_DIR}/server/solr/configsets/cbtest_default_configs"
    cp -ar "${TRAVIS_BUILD_DIR}/sys/solr_configsets/default_config/conf" "${SOLR_DIR}/server/solr/configsets/cbtest_default_configs/"
    mkdir "${SOLR_DIR}/server/solr/configsets/cbtest_log_configs"
    cp -ar "${TRAVIS_BUILD_DIR}/sys/solr_configsets/log_config/conf" "${SOLR_DIR}/server/solr/configsets/cbtest_log_configs/"
#    ls -la "${SOLR_DIR}/server/solr/configsets/cbtest_log_configs/conf"
fi

echo "Changing dir into ${SOLR_DIR}"
cd $SOLR_DIR

# We use exec to allow process monitors to correctly kill the
# actual Java process rather than this launcher script:

export CMD="bin/solr start -p ${SOLR_PORT}"

echo "Starting server on port ${SOLR_PORT}"
exec $CMD
