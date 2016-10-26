#!/bin/bash
ROOTDIR="/jelixapp"
APPNAME="testapp"
APPDIR="$ROOTDIR/$APPNAME"
VAGRANTDIR="$APPDIR/vagrant"

source $VAGRANTDIR/system.sh


# --- testapp
resetJelixMysql testapp root jelix
resetJelixInstall $APPDIR

mysql -u root -pjelix -e "drop table if exists labels1_test;drop table if exists labels_test;drop table if exists myconfig;drop table if exists product_tags_test;drop table if exists product_test;drop table if exists products;drop table if exists towns;drop table if exists testkvdb;" testapp;
sudo -u postgres -- psql -d testapp -c "drop table if exists jacl2_subject_group cascade;drop table if exists jacl2_user_group cascade;drop table if exists jacl2_group cascade;drop table if exists jacl2_rights cascade;drop table if exists jacl2_subject;drop table if exists jsessions;drop table if exists labels1_tests;drop table if exists labels_tests;drop table if exists product_tags_test;drop table if exists product_test;drop table if exists products;drop table if exists testkvdb;"

if [ -f $APPDIR/var/db/sqlite3/tests.sqlite3.bak ]; then
    cp -a $APPDIR/var/db/sqlite3/tests.sqlite3.bak $APPDIR/var/db/sqlite3/tests.sqlite3
fi

initapp $APPDIR

# --- adminapp
resetJelixInstall $APPDIR/adminapp
resetJelixMysql testapp root jelix admin_
initapp $APPDIR/adminapp

