#!/bin/bash

ROOTDIR="/jelixapp"
MYSQL_VERSION=""
PHP_VERSION="8.0"
APPNAME="testapp"
APPDIR="$ROOTDIR/$APPNAME"
VAGRANTDIR="$APPDIR/vagrant"
APPHOSTNAME="testapp17.local"
APPHOSTNAME2=""
LDAPCN="testapp17"
FPM_SOCK="php\\/php8.0-fpm.sock"
POSTGRESQL_VERSION=11

source $VAGRANTDIR/common_provision.sh

