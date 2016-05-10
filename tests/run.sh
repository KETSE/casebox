#!/bin/bash bash

set -e

DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

# rpc-bundle
rm -r $DIR/../var/cache/test
bash $DIR/../vendor/caseboxdev/rpc-bundle/src/Tests/run.sh

# core-bundle
rm -r $DIR/../var/cache/test
bash $DIR/../vendor/caseboxdev/core-bundle/src/Tests/run.sh
