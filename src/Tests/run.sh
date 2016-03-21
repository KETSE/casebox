#/bin/bash
DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

# first test RPC
$DIR/../../vendor/bin/phpunit --colors --verbose --debug --configuration $DIR/../../vendor/caseboxdev/rpc-bundle/Tests/phpunit.xml

# second test REST
$DIR/../../vendor/bin/phpunit --colors --verbose --debug --configuration $DIR/../../vendor/caseboxdev/rest-bundle/Tests/phpunit.xml

# third test CORE
$DIR/../../vendor/bin/phpunit --colors --verbose --debug --configuration $DIR/../../vendor/caseboxdev/core-bundle/Tests/phpunit.xml