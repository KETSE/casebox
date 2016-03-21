#/bin/bash
DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

# first test rpc
$DIR/../../vendor/bin/phpunit --colors --verbose --debug --configuration $DIR/../../vendor/caseboxdev/rpc-bundle/Tests/phpunit.xml