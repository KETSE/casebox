#/bin/bash
## rununittests inspired from 
# https://coderwall.com/p/1yywoq/shell-script-to-run-phpunit-tests-with-or-without-code-coverage 
# I try to automatize all tests by this shell 

DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
# The default destination of the coverage results
DEST="$DIR/reports/"

export TRAVIS_BUILD_DIR=`dirname "${DIR}"`

# The function to show the help

showHelp () {
        echo ""
        echo "Usage";
        echo "rununittests [-c clover|craph4j|html|php|text] [-d path/to/coverage/output]";
        echo "-c [coverage type]            Create code coverage";
        echo "-d [destination]      The path to save the code coverage (default to $DEST)"
        echo "-h                            This help"
        echo "-?                            This help"
}

# first check if you have composer installed
if [ ! -f $DIR/../composer.phar ]; then
    echo "error: first install Composer the Dependency Manager for PHP "
    echo "#curl -sS https://getcomposer.org/installer | php"
    echo "#wget https://getcomposer.org/composer.phar"

    exit
fi

# check if you have PHPunit package
if [ ! -f $DIR/../vendor/bin/phpunit ]; then
    echo "error: phpunit not found, install phpunit from Composer Dependency Manager "
    echo "#php composer.phar install"
    exit
fi



# If option '-c' is passed, the coverage that we will generate
coverage=''
while getopts "c:d:h?" opt; do
    case $opt in
        c) 
            coverage="--coverage-$OPTARG"
            if [ "$OPTARG"=="clover.xml" ]; then 
            OUTFILE="clover.xml"
            else
            OUTFILE="coverage.$OPTARG"
            fi

            continue
        ;;
        d)
            dest="$OPTARG"
            continue
        ;;
        h|\?)
            showHelp
            exit
        ;;
    esac
done
if [ $coverage ];
    then
      $DIR/../vendor/bin/phpunit $coverage $DEST$OUTFILE --configuration $DIR/phpunit.xml --verbose --bootstrap $DIR/init.php $DIR/../httpsdocs/classes/UnitTest
    else 

    service solr stop

        export SOLR_VERSION="5.2.0"
        bash $DIR/server/solr/solr5-install.sh

       export SOLR_CORENAME="cbtest_log"
       export SOLR_CONFIGSET="cbtest_log"
      bash $DIR/server/solr/solr5-addcore.sh

       export SOLR_CORENAME="cbtest_test"
      export SOLR_CONFIGSET="cbtest_default"
      bash $DIR/server/solr/solr5-addcore.sh

      cp $DIR/../httpsdocs/config.ini $DIR/tmp/config.ini.old

        php $DIR/auto_install.php
        $DIR/../vendor/bin/phpunit --colors --configuration $DIR/phpunit-travis.xml --verbose --debug --bootstrap $DIR/init.php

     bash $DIR/server/solr/solr5-stop.sh


    sleep 5
    echo "remove solr directory"
    rm -rf "${DIR}/server/solr/solr-${SOLR_VERSION}"

      cp $DIR/tmp/config.ini.old $DIR/../httpsdocs/config.ini 

   # service solr start

    fi