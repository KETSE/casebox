#!/usr/bin/env bash
set -e
DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
ROOT=$(realpath "$DIR/../..")
echo -e "\n[*] Run provision...\n"
/usr/bin/ansible-playbook -i "localhost," -c local $DIR/../jenkins/provision.yml --extra-vars="casebox_core='default' casebox_server_name='_' casebox_root_dir='/var/www/casebox' os_user='vagrant'"
echo -e "\n[*] Run tests and sniffers...\n"
bash $ROOT/tests/run.sh