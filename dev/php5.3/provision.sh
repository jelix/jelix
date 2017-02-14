#!/bin/bash

ROOTDIR="/jelixapp"
MYSQL_VERSION="5.3"
PHP_VERSION="5.3"
PHP53="yes"
APPNAME="testapp"
APPDIR="$ROOTDIR/$APPNAME"
VAGRANTDIR="/vagrantscripts"
APPHOSTNAME="testapp16.local"
APPHOSTNAME2=""
FPM_SOCK="php5-fpm.sock"
POSTGRESQL_VERSION=9.1

source $VAGRANTDIR/common_provision.sh
