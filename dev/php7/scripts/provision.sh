#!/bin/bash

ROOTDIR="/jelixapp"
TESTAPPDIR="$ROOTDIR/_build"
VAGRANTDIR="/vagrantscripts"

if [ ! -d "$TESTAPPDIR" -o ! -d "$TESTAPPDIR/testapp" ]; then
    >&2 echo "ERROR: you should run updatesrc.sh to generate a build of Jelix first."
    exit 1
fi

# create hostname
HOST=`grep testapp16 /etc/hosts`
if [ "$HOST" == "" ]; then
    echo "127.0.0.1 testapp.local testapp16.local" >> /etc/hosts
fi
hostname testapp.local
echo "testapp16.local" > /etc/hostname

# local time
echo "Europe/Paris" > /etc/timezone
cp /usr/share/zoneinfo/Europe/Paris /etc/localtime
locale-gen fr_FR.UTF-8
update-locale LC_ALL=fr_FR.UTF-8

# install all packages
apt-get update
#apt-get -y upgrade
apt-get -y install debconf-utils
export DEBIAN_FRONTEND=noninteractive
echo "mysql-server-5.7 mysql-server/root_password password jelix" | debconf-set-selections
echo "mysql-server-5.7 mysql-server/root_password_again password jelix" | debconf-set-selections
echo "phpmyadmin phpmyadmin/dbconfig-install boolean true" | debconf-set-selections
echo "phpmyadmin phpmyadmin/reconfigure-webserver multiselect nginx" | debconf-set-selections
echo "phpmyadmin phpmyadmin/mysql/admin-pass password jelix" | debconf-set-selections
echo "phpmyadmin phpmyadmin/app-password-confirm password jelix" | debconf-set-selections
echo "phpmyadmin phpmyadmin/mysql/app-pass password jelix" | debconf-set-selections
echo "phpmyadmin phpmyadmin/password-confirm password jelix" | debconf-set-selections
echo "phpmyadmin phpmyadmin/setup-password password jelix" | debconf-set-selections


apt-get -y install nginx
apt-get -y install php7.0-fpm php7.0-cli php7.0-curl php7.0-gd php7.0-intl php7.0-mcrypt php-memcached php7.0-mysql php7.0-pgsql php7.0-sqlite3 php7.0-soap php7.0-dba
apt-get -y install postgresql postgresql-client mysql-server mysql-client
apt-get -y install redis-server memcached memcachedb
apt-get -y install phpmyadmin git vim

# create a database into mysql + users
if [ ! -d /var/lib/mysql/testapp/ ]; then
    echo "setting mysql database.."
    mysql -u root -pjelix -e "CREATE DATABASE IF NOT EXISTS testapp CHARACTER SET utf8;CREATE USER test_user IDENTIFIED BY 'jelix';GRANT ALL ON testapp.* TO test_user;FLUSH PRIVILEGES;"
fi

# create a database into pgsql + users
su postgres -c $VAGRANTDIR/create_pgsql_db.sh
echo "host    testapp,postgres         +test_group         0.0.0.0           0.0.0.0           md5" >> /etc/postgresql/9.5/main/pg_hba.conf
service postgresql restart

# install default vhost for nginx
cp $VAGRANTDIR/testapp.conf /etc/nginx/sites-available/

if [ ! -f "/etc/nginx/sites-enabled/010-testapp.conf" ]; then
    ln -s /etc/nginx/sites-available/testapp.conf /etc/nginx/sites-enabled/010-testapp.conf
fi
if [ -f "/etc/nginx/sites-enabled/default" ]; then
    rm -f "/etc/nginx/sites-enabled/default"
fi

sed -i "/^user = www-data/c\user = vagrant" /etc/php/7.0/fpm/pool.d/www.conf
sed -i "/^group = www-data/c\group = vagrant" /etc/php/7.0/fpm/pool.d/www.conf
sed -i "/display_errors = Off/c\display_errors = On" /etc/php/7.0/fpm/php.ini
sed -i "/display_errors = Off/c\display_errors = On" /etc/php/7.0/cli/php.ini

service php7.0-fpm restart

service nginx reload

echo "Install composer.."
if [ ! -f /usr/local/bin/composer ]; then
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
fi

echo "Install testapp configuration file"
# create  profiles.ini.php
if [ ! -f $TESTAPPDIR/testapp/var/config/profiles.ini.php ]; then
    cp -a $TESTAPPDIR/testapp/var/config/profiles.ini.php7.dist $TESTAPPDIR/testapp/var/config/profiles.ini.php
fi

# touch localconfig.ini.php
touch $TESTAPPDIR/testapp/var/config/localconfig.ini.php

# create temp directory
if [ ! -d $TESTAPPDIR/temp/testapp ]; then
    mkdir $TESTAPPDIR/temp/testapp
fi

# set rights
#WRITABLEDIRS="$TESTAPPDIR/temp/testapp/ $TESTAPPDIR/testapp/var/log/ $TESTAPPDIR/testapp/var/mails $TESTAPPDIR/testapp/var/db"
#chown -R www-data:www-data $WRITABLEDIRS
#chmod -R g+w $WRITABLEDIRS

# install phpunit
cd $TESTAPPDIR/testapp/
su -c "composer install" vagrant

if [ ! -f /usr/bin/phpunit ]; then
    ln -s $TESTAPPDIR/testapp/vendor/bin/phpunit  /usr/bin/phpunit
fi

# install the application
cd $TESTAPPDIR/testapp/install
php installer.php

echo "Done."
