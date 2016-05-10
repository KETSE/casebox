#!/bin/bash

set -e

DIR=$(realpath $(dirname "$0"))
ROOT=$(realpath "$DIR/../../..")

cd $(dirname $0)

export SOLR_VERSION=${SOLR_VERSION:-5.5.0}
export SOLR_NAME="solr-$SOLR_VERSION"
export SOLR_DIR="`pwd`/${SOLR_NAME}"
export SOLR_PORT=${SOLR_PORT:-8180}
export SOLR_SOURCE_URL="http://archive.apache.org/dist/lucene/solr/${SOLR_VERSION}/${SOLR_NAME}.tgz"
export SOLR_ARCHIVE="${SOLR_NAME}.tgz"
export SOLR_CONFIGSET=${SOLR_CONFIGSET:-basic}

solr_responding() {
  port=$1
  curl -o /dev/null "http://localhost:$port/solr/admin/ping" > /dev/null 2>&1
}

wait_until_solr_responds() {
  port=$1
  while ! solr_responding $1; do
    /bin/echo -n "."
    sleep 1
  done
}


solr_install() {
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

    if [ -d "`pwd`/server" ]; then
        echo "Create  configsets in ${SOLR_DIR}"
        cp -ar "`pwd`/server" $SOLR_DIR
    fi

    echo "Changing dir into ${SOLR_DIR}"
    cd $SOLR_DIR

    # We use exec to allow process monitors to correctly kill the
    # actual Java process rather than this launcher script:

    export CMD="bin/solr start -p ${SOLR_PORT}"

    echo "Starting server on port ${SOLR_PORT}"
    exec $CMD
}

solr_addcore() {

    echo "Waiting solr to launch on ${SOLR_PORT}..."
    wait_until_solr_responds $SOLR_PORT


    if [ -n "$SOLR_CORENAME" ]; then
      echo "Add solr cores"
    for CORENAME in $SOLR_CORENAME
    do
    # create core folder
       mkdir -p "${SOLR_DIR}/server/solr/${CORENAME}/"
       cp -ar  "${ROOT}${SOLR_CONFIGSET}" "${SOLR_DIR}/server/solr/${CORENAME}/"
       echo "Configuring Core named ${CORENAME}"
        #exec $CMD
        curl -o /dev/null "http://localhost:${SOLR_PORT}/solr/admin/cores?action=CREATE&name=${CORENAME}&instanceDir=${CORENAME}" > /dev/null 2>&1
    done
    fi

}


solr_stop() {

    echo "Changing dir into ${SOLR_DIR}"
    cd $SOLR_DIR

    export CMD="bin/solr stop -p ${SOLR_PORT}"
    echo "Stop server on port ${SOLR_PORT}"
    exec $CMD

}

while [[ $# -ge 1 ]]
    do
        key="$1"

        case $key in
            --install)
            solr_install
            ;;
            --addcore)
            solr_addcore
            ;;
            --stop)
            solr_stop
            ;;
            *)
             #echo $key    # unknown option
            ;;
        esac
    shift # past argument or value
done
