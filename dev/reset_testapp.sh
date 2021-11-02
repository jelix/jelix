#!/bin/bash
ROOTDIR="/jelixapp"
APPNAME="testapp"
APPDIR="$ROOTDIR/$APPNAME"
VAGRANTDIR="/vagrantscripts"
LDAPCN="testapp16"

source $VAGRANTDIR/system.sh

PHP_VERSION=$(php -r "echo phpversion();")

# --- testapp
resetJelixMysql testapp test_user jelix
resetJelixInstall $APPDIR

if [ -d $APPDIR/var/config/newep ]; then
  rm -rf  $APPDIR/var/config/newep
fi

if [ -d $APPDIR/www/newep.php ]; then
  rm -f  $APPDIR/www/newep.php
fi

if [ -f $APPDIR/var/config/auth_ldap.coord.ini.php.dist ]; then
    cp -a $APPDIR/var/config/auth_ldap.coord.ini.php.dist $APPDIR/var/config/auth_ldap.coord.ini.php
fi

mysql -u test_user -pjelix -e "drop table if exists labels1_test;drop table if exists labels_test;drop table if exists myconfig;drop table if exists product_tags_test;drop table if exists product_test;drop table if exists products;drop table if exists towns;drop table if exists testkvdb;" testapp;
sudo -u postgres -- psql -d testapp -c "drop table if exists jacl2_subject_group cascade;drop table if exists jacl2_user_group cascade;drop table if exists jacl2_group cascade;drop table if exists jacl2_rights cascade;drop table if exists jacl2_subject;drop table if exists jsessions;drop table if exists labels1_tests;drop table if exists labels_tests;drop table if exists product_tags_test;drop table if exists product_test;drop table if exists products;drop table if exists testkvdb;"

if [ -f $APPDIR/var/db/sqlite3/tests.sqlite3.bak ]; then
    cp -a $APPDIR/var/db/sqlite3/tests.sqlite3.bak $APPDIR/var/db/sqlite3/tests.sqlite3
fi

ldapdelete -x -c -D cn=admin,dc=$LDAPCN,dc=local -w passjelix -f $VAGRANTDIR/ldap/ldap_delete.ldif
ldapadd -x -c -D cn=admin,dc=$LDAPCN,dc=local -w passjelix -f $VAGRANTDIR/ldap/ldap_conf.ldif

initapp $APPDIR
