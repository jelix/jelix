#!/bin/bash

TESTAPPDIR="/jelixapp"


# create hostname
HOST=`grep testapp /etc/hosts`
if [ "$HOST" == "" ]; then
    echo "127.0.0.1 testapp.local" >> /etc/hosts
fi
hostname testapp.local
echo "testapp.local" > /etc/hostname

# activate multiverse repository to have libapache2-mod-fastcgi
sed -i "/^# deb.*multiverse/ s/^# //" /etc/apt/sources.list

# install all packages
apt-get update
apt-get -y upgrade
apt-get -y install debconf-utils
echo "mysql-server-5.5 mysql-server/root_password password jelix" | debconf-set-selections
echo "mysql-server-5.5 mysql-server/root_password_again password jelix" | debconf-set-selections

apt-get -y install apache2 libapache2-mod-fastcgi apache2-mpm-worker php5-fpm php5-cli php5-curl php5-gd php5-intl php5-mcrypt php5-memcache php5-memcached php5-mysql php5-pgsql php5-sqlite
apt-get -y install phpunit
apt-get -y install postgresql postgresql-client mysql-server mysql-client
apt-get -y install redis-server memcached memcachedb

# create a database into mysql + users
if [ ! -d /var/lib/mysql/testapp/ ]; then
    echo "setting mysql database.."
    mysql -u root -pjelix -e "CREATE DATABASE IF NOT EXISTS testapp CHARACTER SET utf8;CREATE USER testapp IDENTIFIED BY 'testapp';GRANT ALL ON testapp.* TO testapp;FLUSH PRIVILEGES;"
fi

# create a database into pgsql + users
su postgres -c /jelixapp/testapp/vagrant/create_pgsql_db.sh
echo "host    testapp,postgres         +test_group         0.0.0.0           0.0.0.0           md5" >> /etc/postgresql/9.3/main/pg_hba.conf
service postgresql restart

# install default vhost for apache
cp $TESTAPPDIR/testapp/vagrant/testapp.conf /etc/apache2/sites-available/

if [ ! -f "/etc/apache2/sites-enabled/010-testapp.conf" ]; then
    ln -s /etc/apache2/sites-available/testapp.conf /etc/apache2/sites-enabled/010-testapp.conf
fi
if [ ! -f "/etc/apache2/sites-enabled/000-default.conf" ]; then
    rm -f "/etc/apache2/sites-enabled/000-default.conf"
fi

cp $TESTAPPDIR/testapp/vagrant/php5_fpm.conf /etc/apache2/conf-available/
if [ ! -f "/etc/apache2/conf-enabled/php5_fpm.conf" ]; then
    ln -s /etc/apache2/conf-available/php5_fpm.conf /etc/apache2/conf-enabled/php5_fpm.conf
fi

a2enmod actions alias fastcgi rewrite

# restart apache
service apache2 reload

# create  profiles.ini.php
if [ ! -f $TESTAPPDIR/testapp/var/config/profiles.ini.php ]; then
    cp -a $TESTAPPDIR/testapp/var/config/profiles.ini.php.dist $TESTAPPDIR/testapp/var/config/profiles.ini.php
fi

# touch localconfig.ini.php
touch $TESTAPPDIR/testapp/var/config/localconfig.ini.php

# create temp directory
if [ ! -d $TESTAPPDIR/temp/testapp ]; then
    mkdir $TESTAPPDIR/temp/testapp
fi

# set rights
WRITABLEDIRS="$TESTAPPDIR/temp/testapp/ $TESTAPPDIR/testapp/var/log/ $TESTAPPDIR/testapp/var/mails $TESTAPPDIR/testapp/var/db"
chown -R www-data:www-data $WRITABLEDIRS
chmod -R g+w $WRITABLEDIRS

if [ ! -f /usr/local/bin/composer ]; then
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
fi

