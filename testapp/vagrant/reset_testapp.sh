#!/bin/bash
ROOTDIR="/jelixapp"
APPNAME="testapp"
APPDIR="$ROOTDIR/$APPNAME"

mysql -u root -pjelix -e "drop table jacl2_group;drop table jacl2_rights;drop table jacl2_subject;drop table jacl2_subject_group;drop table jlx_cache;drop table jlx_user;drop table jsessions;drop table labels1_test;drop table labels_test;drop table myconfig;drop table product_tags_test;drop table product_test;drop table jacl2_user_group;drop table products;drop table towns;drop table testkvdb;" testapp;
sudo -u postgres -- psql -d testapp -c "drop table jacl2_subject_group cascade;drop table jacl2_user_group cascade;drop table jacl2_group cascade;drop table jacl2_rights cascade;drop table jacl2_subject;drop table jsessions;drop table labels1_tests;drop table labels_tests;drop table product_tags_test;drop table product_test;drop table products;drop table testkvdb;"

if [ -f $APPDIR/var/config/CLOSED ]; then
    rm -f $APPDIR/var/config/CLOSED
fi

rm -rf $ROOTDIR/temp/$APPNAME/*
touch $ROOTDIR/temp/$APPNAME/.dummy
rm -f $APPDIR/var/config/installer.ini.php
cp -a $APPDIR/var/config/profiles.ini.php.dist $APPDIR/var/config/profiles.ini.php
cp -a $APPDIR/var/config/localconfig.ini.php.dist $APPDIR/var/config/localconfig.ini.php


if [ -f $APPDIR/var/db/sqlite/tests.sqlite.bak ]; then
    cp -a $APPDIR/var/db/sqlite/tests.sqlite.bak $APPDIR/var/db/sqlite/tests.sqlite
fi
if [ -f $APPDIR/var/db/sqlite3/tests.sqlite3.bak ]; then
    cp -a $APPDIR/var/db/sqlite3/tests.sqlite3.bak $APPDIR/var/db/sqlite3/tests.sqlite3
fi

php $APPDIR/install/installer.php
