#!/bin/bash
ROOTDIR="/jelixapp"
APPNAME="testapp"
APPDIR="$ROOTDIR/$APPNAME"

mysql -u root -pjelix -e "drop table if exists jacl2_group;drop table if exists jacl2_rights;drop table if exists jacl2_subject;drop table if exists jacl2_subject_group;drop table if exists jlx_cache;drop table jlx_user;drop table jsessions;drop table labels1_test;drop table labels_test;drop table myconfig;drop table product_tags_test;drop table product_test;drop table jacl2_user_group;drop table products;drop table towns;drop table testkvdb;" testapp;

if [ -f $APPDIR/var/config/CLOSED ]; then
    rm -f $APPDIR/var/config/CLOSED
fi

if [ ! -d $APPDIR/temp/ ]; then
    mkdir $APPDIR/temp/
else
    rm -rf $APPDIR/temp/*
fi
touch $APPDIR/temp/.dummy

if [ ! -d $APPDIR/var/log ]; then
    mkdir $APPDIR/var/log
fi

if [ -f $APPDIR/var/config/profiles.ini.php.dist ]; then
    cp -a $APPDIR/var/config/profiles.ini.php.dist $APPDIR/var/config/profiles.ini.php
fi
if [ -f $APPDIR/var/config/localconfig.ini.php.dist ]; then
    cp -a $APPDIR/var/config/localconfig.ini.php.dist $APPDIR/var/config/localconfig.ini.php
fi
if [ -f $APPDIR/var/config/installer.ini.php ]; then
    rm -f $APPDIR/var/config/installer.ini.php
fi

if [ -f $APPDIR/var/db/sqlite/tests.sqlite.bak ]; then
    cp -a $APPDIR/var/db/sqlite/tests.sqlite.bak $APPDIR/var/db/sqlite/tests.sqlite
fi
if [ -f $APPDIR/var/db/sqlite3/tests.sqlite3.bak ]; then
    cp -a $APPDIR/var/db/sqlite3/tests.sqlite3.bak $APPDIR/var/db/sqlite3/tests.sqlite3
fi
