#!/usr/bin/env bash
set -e
PHP=$( which php )
DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
$PHP $DIR/../vendor/bin/phpcs --config-set installed_paths vendor/escapestudios/symfony2-coding-standard
$PHP $DIR/../vendor/bin/phpcs --standard=Symfony2 --report-file=$DIR/../build/logs/phpcs.xml --report=xml $DIR/../vendor/caseboxdev/rpc-bundle/src/Service/ $DIR/../vendor/caseboxdev/rpc-bundle/src/Controller/ $DIR/../vendor/caseboxdev/core-bundle/src/Service/ $DIR/../vendor/caseboxdev/core-bundle/src/Controller/
$PHP $DIR/../vendor/bin/phpcs --standard=Symfony2 --report=checkstyle --report-file=$DIR/../build/logs/checkstyle.xml $DIR/../vendor/caseboxdev/rpc-bundle/src/Service/ $DIR/../vendor/caseboxdev/rpc-bundle/src/Controller/ $DIR/../vendor/caseboxdev/core-bundle/src/Service/ $DIR/../vendor/caseboxdev/core-bundle/src/Controller/