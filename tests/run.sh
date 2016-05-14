#!/usr/bin/env sh

set -e

DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

echo -e "\n[*] Run PHPUnit tests.\n"
sh $DIR/tests.sh
echo -e "\n[*] Run PHP Code Sniffer.\n"
sh $DIR/phpcs.sh
echo -e "\n[*] Run PHP Mess Detector.\n"
sh $DIR/phpmd.sh