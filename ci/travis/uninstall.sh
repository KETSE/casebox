#!/bin/bash

set -e
set -x

DIR=$(realpath $(dirname "$0"))
ROOT=$(realpath "$DIR/../..")

export SOLR_VERSION="5.5.0"
export SOLR_PORT="8180"

bash $DIR/solr/solr5.sh --stop