#!/bin/bash bash

set -e

DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

echo -e "\n[*] Run core-bundle tests.\n"
[ -d var/cache/test/ ] && sudo rm -r var/cache/test
bash $DIR/../vendor/caseboxdev/core-bundle/src/Tests/run.sh

echo -e "\n[*] Run rpc-bundle tests.\n"
[ -d var/cache/test/ ] && sudo rm -r var/cache/test
bash $DIR/../vendor/caseboxdev/rpc-bundle/src/Tests/run.sh
