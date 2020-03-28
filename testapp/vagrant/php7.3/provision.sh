#!/bin/bash

ROOTDIR="/jelixapp"
MYSQL_VERSION=""
PHP_VERSION="7.3"
APPNAME="testapp"
APPDIR="$ROOTDIR/$APPNAME"
VAGRANTDIR="$APPDIR/vagrant"
APPHOSTNAME="testapp18.local"
APPHOSTNAME2=""
LDAPCN="testapp18"
FPM_SOCK="php\\/php7.3-fpm.sock"
POSTGRESQL_VERSION=11

source $VAGRANTDIR/common_provision.sh

