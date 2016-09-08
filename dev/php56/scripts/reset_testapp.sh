#!/bin/bash

ROOTDIR="/jelixapp"
TESTAPPDIR="$ROOTDIR/_build"

mysql -u root -pjelix -e "drop table jacl2_group;drop table jacl2_rights;drop table jacl2_subject;drop table jacl2_subject_group;drop table jlx_cache;drop table jlx_user;drop table jsessions;drop table labels1_test;drop table labels_test;drop table myconfig;drop table product_tags_test;drop table product_test;drop table jacl2_user_group;drop table products;drop table towns;drop table testkvdb;" testapp;
sudo -u postgres -- psql -d testapp -c "drop table jacl2_subject_group cascade;drop table jacl2_user_group cascade;drop table jacl2_group cascade;drop table jacl2_rights cascade;drop table jacl2_subject;drop table jsessions;drop table labels1_tests;drop table labels_tests;drop table product_tags_test;drop table product_test;drop table products;drop table testkvdb;"
rm -rf $TESTAPPDIR/temp/testapp/*
rm -f $TESTAPPDIR/testapp/var/config/installer.ini.php
cp -a $TESTAPPDIR/testapp/var/config/profiles.ini.php.dist $TESTAPPDIR/testapp/var/config/profiles.ini.php

php $TESTAPPDIR/testapp/install/installer.php
