#!/usr/bin/env bash

DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

echo -e "\n[*] Run PHPUnit tests.\n"
bash $DIR/tests.sh
echo -e "\n[*] Run PHP Code Sniffer.\n"
bash $DIR/phpcs.sh
echo -e "\n[*] Run PHP Mess Detector.\n"
bash $DIR/phpmd.sh