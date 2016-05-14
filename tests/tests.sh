#!/bin/bash bash
set -e
DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
PHP=$(which php)
echo -e "\n[*] Run core-bundle tests.\n"
[ -d var/cache/test/ ] && sudo rm -r var/cache/test
bash $DIR/../vendor/caseboxdev/core-bundle/src/Tests/run.sh
echo -e "\n[*] Run rpc-bundle tests.\n"
[ -d var/cache/test/ ] && sudo rm -r var/cache/test
bash $DIR/../vendor/caseboxdev/rpc-bundle/src/Tests/run.sh
echo -e "\n[*] Merge coverage-clover reports to clover.xml file.\n"
[ -d build/logs/clover.xml ] && sudo rm build/logs/clover.xml
$PHP $DIR/../bin/clover_merge -o $DIR/../build/logs/clover.xml -f $DIR/../build/logs/clover-core-app.xml -f $DIR/../build/logs/clover-rpc-api.xml -f $DIR/../build/logs/clover-rpc-app.xml