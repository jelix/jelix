#!/bin/bash

ROOTDIR="/jelixapp"
MYSQL_VERSION="5.5"
PHP_VERSION="5"
APPNAME="testapp"
APPDIR="$ROOTDIR/$APPNAME"
VAGRANTDIR="$APPDIR/vagrant"
APPHOSTNAME="testapp17.local"
APPHOSTNAME2=""
FPM_SOCK="php5-fpm.sock"
POSTGRESQL_VERSION=9.4

source $VAGRANTDIR/common_provision.sh
