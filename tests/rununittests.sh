#/bin/bash
## rununittests
# I try to automatize all tests by this shell 

# The function to show the help
showHelp () {
        echo ""
        echo "Usage";
        echo "rununittests [-c clover|craph4j|html|php|text] [-d path/to/coverage/output]";
        echo "-c [coverage type]            Create code coverage";
        echo "-d [destination]      The path to save the code coverage"
        echo "-h                            This help"
        echo "-?                            This help"
}


DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

# first check if you have composer installed
if [ ! -f $DIR/composer.phar ]; then
    echo "error: first install Composer the Dependency Manager for PHP "
    echo "curl -sS https://getcomposer.org/installer | php"
    exit
fi

# check if you have PHPunit package
if [ ! -f $DIR/vendor/bin/phpunit ]; then
    echo "error: phpunit not found, install phpunit from Composer Dependency Manager "
    echo "php composer.phar install"
    exit
fi

# The default destination of the coverage results
DEST="$DIR/temp/unittestresults"

# If option '-c' is passed, the coverage that we will generate
coverage=''
while getopts "c:d:h?" opt; do
    case $opt in
        c) 
            coverage="--coverage-$OPTARG"
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
        $DIR/vendor/bin/phpunit $coverage $dest --verbose --bootstrap init.php $DIR
        exit
    fi

$DIR/vendor/bin/phpunit --verbose --bootstrap init.php $DIR/*Test.php