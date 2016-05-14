#!/usr/bin/env bash
set -ex
PHP=$( which php )
DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
$PHP vendor/bin/phpcs --config-set installed_paths vendor/escapestudios/symfony2-coding-standard
$PHP vendor/bin/phpcs --standard=Symfony2 --report-file=$DIR/../build/logs/phpcs.xml --report=xml vendor/caseboxdev/rpc-bundle/src/Service/ vendor/caseboxdev/rpc-bundle/src/Controller/ vendor/caseboxdev/core-bundle/src/Service/ vendor/caseboxdev/core-bundle/src/Controller/
$PHP vendor/bin/phpcs --standard=Symfony2 --report=checkstyle --report-file=$DIR/../build/logs/checkstyle.xml vendor/caseboxdev/rpc-bundle/src/Service/ vendor/caseboxdev/rpc-bundle/src/Controller/ vendor/caseboxdev/core-bundle/src/Service/ vendor/caseboxdev/core-bundle/src/Controller/