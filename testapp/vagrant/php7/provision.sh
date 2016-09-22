#!/bin/bash

ROOTDIR="/jelixapp"
MYSQL_VERSION="5.7"
PHP_VERSION="7"
APPNAME="testapp"
APPDIR="$ROOTDIR/$APPNAME"
VAGRANTDIR="$APPDIR/vagrant"
APPHOSTNAME="testapp.local"
APPHOSTNAME2="testapp17.local"
FPM_SOCK="php7.0-fpm.sock"
POSTGRESQL_VERSION=9.5

source $VAGRANTDIR/common_provision.sh

