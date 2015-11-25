#!/usr/bin/env bash

set -e

cd $(dirname $0)

export SOLR_VERSION=${SOLR_VERSION:-5.2.0}
export SOLR_NAME="solr-$SOLR_VERSION"
export SOLR_DIR="`pwd`/${SOLR_NAME}"
export SOLR_PORT=${SOLR_PORT:-8983}

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


echo "Waiting solr to launch on ${SOLR_PORT}..."
wait_until_solr_responds $SOLR_PORT


if [ -n "$SOLR_CORENAME" ]; then
  echo "Add solr cores"
for CORENAME in $SOLR_CORENAME
do
# create core folder
   mkdir -p "${SOLR_DIR}/server/solr/${CORENAME}/"
   cp -ar "${SOLR_DIR}/server/solr/configsets/${SOLR_CONFIGSET}_configs/conf" "${SOLR_DIR}/server/solr/${CORENAME}/"
   export CMD="${SOLR_DIR}/bin/solr create_core -c ${CORENAME}"
     echo "Configuring Core named ${CORENAME}"
   exec $CMD
    curl -o /dev/null "http://localhost:${SOLR_PORT}/solr/admin/cores?action=CREATE&name=${CORENAME}&instanceDir=${CORENAME}" > /dev/null 2>&1
done
fi
