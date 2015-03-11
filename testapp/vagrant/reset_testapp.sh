#!/bin/bash
ROOTDIR="/jelixapp"
APPNAME="testapp"
APPDIR="$ROOTDIR/$APPNAME"
VAGRANTDIR="$APPDIR/vagrant"

source $VAGRANTDIR/jelixapp/reset_app.sh

sudo -u postgres -- psql -d testapp -c "drop table if exists jacl2_subject_group cascade;drop table if exists jacl2_user_group cascade;drop table if exists jacl2_group cascade;drop table if exists jacl2_rights cascade;drop table if exists jacl2_subject;drop table if exists jsessions;drop table if exists labels1_tests;drop table if exists labels_tests;drop table if exists product_tags_test;drop table if exists product_test;drop table if exists products;drop table if exists testkvdb;"

if [ -f $APPDIR/var/db/sqlite/tests.sqlite.bak ]; then
    cp -a $APPDIR/var/db/sqlite/tests.sqlite.bak $APPDIR/var/db/sqlite/tests.sqlite
fi
if [ -f $APPDIR/var/db/sqlite3/tests.sqlite3.bak ]; then
    cp -a $APPDIR/var/db/sqlite3/tests.sqlite3.bak $APPDIR/var/db/sqlite3/tests.sqlite3
fi

php $APPDIR/install/installer.php

touch $APPDIR/temp/.dummy