#!/usr/bin/env bash
PHP=$( which php )
DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
$PHP $DIR/../vendor/bin/phpmd $DIR/../vendor/caseboxdev/rpc-bundle/src/Service/ xml codesize,unusedcode,naming --reportfile $DIR/../build/logs/phpmd-rpc-service.xml
$PHP $DIR/../vendor/bin/phpmd $DIR/../vendor/caseboxdev/rpc-bundle/src/Controller/ xml codesize,unusedcode,naming --reportfile $DIR/../build/logs/phpmd-rpc-controller.xml
$PHP $DIR/../vendor/bin/phpmd $DIR/../vendor/caseboxdev/core-bundle/src/Service/ xml codesize,unusedcode,naming --reportfile $DIR/../build/logs/phpmd-core-service.xml
$PHP $DIR/../vendor/bin/phpmd $DIR/../vendor/caseboxdev/core-bundle/src/Controller/ xml codesize,unusedcode,naming --reportfile $DIR/../build/logs/phpmd-core-controller.xml
