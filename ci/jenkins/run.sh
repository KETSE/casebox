#!/usr/bin/env sh

DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
ROOT=$(realpath "$DIR/../..")

echo -e "\n[*] Run provision...\n"
sh ansible-playbook -i "localhost," -c local $DIR/provision.yml --extra-vars="casebox_core='cbtest' casebox_server_name='development.ci.casebox.org' casebox_root_dir='/var/lib/jenkins/workspace/casebox/development' os_user='jenkins'"

echo -e "\n[*] Run tests and sniffers...\n"
sh $ROOT/tests/run.sh