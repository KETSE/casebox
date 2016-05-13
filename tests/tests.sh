#!/bin/bash bash

set -e

DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

# core-bundle
[ -d var/cache/test/ ] && sudo rm -r var/cache/test
bash $DIR/../vendor/caseboxdev/core-bundle/src/Tests/run.sh

# rpc-bundle
[ -d var/cache/test/ ] && sudo rm -r var/cache/test
bash $DIR/../vendor/caseboxdev/rpc-bundle/src/Tests/run.sh
