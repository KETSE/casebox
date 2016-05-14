#!/usr/bin/env bash
set -e
DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
ROOT=$(realpath "$DIR/../..")
echo -e "\n[*] Run provision...\n"
/usr/bin/ansible-playbook -i "localhost," -c local $DIR/provision.yml --extra-vars="casebox_core='test' casebox_server_name='development.ci.casebox.org' casebox_root_dir='/var/lib/jenkins/workspace/casebox/development'"
echo -e "\n[*] Run tests and sniffers...\n"
bash $ROOT/tests/run.sh