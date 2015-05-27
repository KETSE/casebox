#/bin/bash
## rununittests inspired from 
# https://coderwall.com/p/1yywoq/shell-script-to-run-phpunit-tests-with-or-without-code-coverage 
# I try to automatize all tests by this shell 

DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
# The default destination of the coverage results
DEST="$DIR/reports/"

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
if [ ! -f $DIR/composer.phar ]; then
    echo "error: first install Composer the Dependency Manager for PHP "
    echo "#curl -sS https://getcomposer.org/installer | php"
    echo "#wget https://getcomposer.org/composer.phar"

    exit
fi

# check if you have PHPunit package
if [ ! -f $DIR/vendor/bin/phpunit ]; then
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
            OUTFILE="coverage.$OPTARG"
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
        $DIR/vendor/bin/phpunit $coverage $DEST$OUTFILE --configuration $DIR/phpunit.xml --verbose --bootstrap init.php $DIR/test
        exit
    fi

$DIR/vendor/bin/phpunit --verbose --debug --bootstrap init.php $DIR/test