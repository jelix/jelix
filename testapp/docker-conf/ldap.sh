#!/bin/bash

if [ "$1" == "" ]; then
  echo "Usage: ldap.sh <command> <domain>"
  echo " command: reset, setup"
fi

docker exec  -t -i jelix_auth_ldap /bin/ctl.sh $1
