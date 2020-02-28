#!/bin/bash

ROOTDIR="/jelixapp"
MYSQL_VERSION=""
PHP_VERSION="7.2"
APPNAME="testapp"
APPDIR="$ROOTDIR/$APPNAME"
VAGRANTDIR="/vagrantscripts"
APPHOSTNAME="testapp16.local"
APPHOSTNAME2=""
LDAPCN="testapp16"
FPM_SOCK="php\\/php7.2-fpm.sock"
POSTGRESQL_VERSION=9.6

source $VAGRANTDIR/common_provision.sh

