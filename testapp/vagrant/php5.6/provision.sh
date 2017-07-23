#!/bin/bash

ROOTDIR="/jelixapp"
MYSQL_VERSION="5.5"
PHP_VERSION="5.6"
APPNAME="testapp"
APPDIR="$ROOTDIR/$APPNAME"
VAGRANTDIR="$APPDIR/vagrant"
APPHOSTNAME="testapp17.local"
APPHOSTNAME2=""
LDAPCN="testapp17"
FPM_SOCK="php\\/php5.6-fpm.sock"
POSTGRESQL_VERSION=9.4

source $VAGRANTDIR/common_provision.sh

