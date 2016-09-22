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

# activate multiverse repository to have libapache2-mod-fastcgi
sed -i "/^# deb.*multiverse/ s/^# //" /etc/apt/sources.list

# install all packages
apt-get update
apt-get -y upgrade
apt-get -y install debconf-utils
export DEBIAN_FRONTEND=noninteractive
echo "mysql-server-5.5 mysql-server/root_password password jelix" | debconf-set-selections
echo "mysql-server-5.5 mysql-server/root_password_again password jelix" | debconf-set-selections
echo "phpmyadmin phpmyadmin/dbconfig-install boolean true" | debconf-set-selections
echo "phpmyadmin phpmyadmin/reconfigure-webserver multiselect apache2" | debconf-set-selections
echo "phpmyadmin phpmyadmin/mysql/admin-pass password jelix" | debconf-set-selections
echo "phpmyadmin phpmyadmin/app-password-confirm password jelix" | debconf-set-selections
echo "phpmyadmin phpmyadmin/mysql/app-pass password jelix" | debconf-set-selections
echo "phpmyadmin phpmyadmin/password-confirm password jelix" | debconf-set-selections
echo "phpmyadmin phpmyadmin/setup-password password jelix" | debconf-set-selections

apt-get -y install apache2 libapache2-mod-fastcgi apache2-mpm-worker php5-fpm php5-cli php5-curl php5-gd php5-intl php5-mcrypt php5-memcache php5-memcached php5-mysql php5-pgsql php5-sqlite
apt-get -y install postgresql postgresql-client mysql-server mysql-client
apt-get -y install redis-server memcached memcachedb
apt-get -y install phpmyadmin git

# create a database into mysql + users
if [ ! -d /var/lib/mysql/testapp/ ]; then
    echo "setting mysql database.."
    mysql -u root -pjelix -e "CREATE DATABASE IF NOT EXISTS testapp CHARACTER SET utf8;CREATE USER test_user IDENTIFIED BY 'jelix';GRANT ALL ON testapp.* TO test_user;FLUSH PRIVILEGES;"
fi

# create a database into pgsql + users
su postgres -c $VAGRANTDIR/create_pgsql_db.sh
echo "host    testapp,postgres         +test_group         0.0.0.0           0.0.0.0           md5" >> /etc/postgresql/9.3/main/pg_hba.conf
service postgresql restart

# install default vhost for apache
cp $VAGRANTDIR/testapp.conf /etc/apache2/sites-available/

if [ ! -f "/etc/apache2/sites-enabled/010-testapp.conf" ]; then
    ln -s /etc/apache2/sites-available/testapp.conf /etc/apache2/sites-enabled/010-testapp.conf
fi
if [ -f "/etc/apache2/sites-enabled/000-default.conf" ]; then
    rm -f "/etc/apache2/sites-enabled/000-default.conf"
fi

if [ -d /etc/apache2/conf.d ]; then
    cp $VAGRANTDIR/php5_fpm.conf /etc/apache2/conf.d
else
    if [ -d /etc/apache2/conf-available/ ]; then
        cp $VAGRANTDIR/php5_fpm.conf /etc/apache2/conf-available/
        if [ ! -f "/etc/apache2/conf-enabled/php5_fpm.conf" ]; then
            ln -s /etc/apache2/conf-available/php5_fpm.conf /etc/apache2/conf-enabled/php5_fpm.conf
        fi
    else
        echo "------------- WARNING! php-fpm is not configured into apache"
    fi
fi

a2enmod actions alias fastcgi rewrite

sed -i "/^user = www-data/c\user = vagrant" /etc/php5/fpm/pool.d/www.conf
sed -i "/^group = www-data/c\group = vagrant" /etc/php5/fpm/pool.d/www.conf
sed -i "/display_errors = Off/c\display_errors = On" /etc/php5/fpm/php.ini

service php5-fpm restart

# to avoid bug https://github.com/mitchellh/vagrant/issues/351
if [ -d /etc/apache2/conf.d ]; then
    echo "EnableSendfile Off" > /etc/apache2/conf-d/sendfileoff.conf
else
    echo "EnableSendfile Off" > /etc/apache2/conf-available/sendfileoff.conf
    if [ ! -f "/etc/apache2/conf-enabled/sendfileoff.conf" ]; then
        ln -s /etc/apache2/conf-available/sendfileoff.conf /etc/apache2/conf-enabled/sendfileoff.conf
    fi
fi

# restart apache
service apache2 reload

echo "Install composer.."
if [ ! -f /usr/local/bin/composer ]; then
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
fi

echo "Install testapp configuration file"
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
#WRITABLEDIRS="$TESTAPPDIR/temp/testapp/ $TESTAPPDIR/testapp/var/log/ $TESTAPPDIR/testapp/var/mails $TESTAPPDIR/testapp/var/db"
#chown -R www-data:www-data $WRITABLEDIRS
#chmod -R g+w $WRITABLEDIRS

# install phpunit
cd $TESTAPPDIR/testapp/
composer install
if [ ! -f /usr/bin/phpunit ]; then
    ln -s $TESTAPPDIR/testapp/vendor/bin/phpunit  /usr/bin/phpunit
fi

# install the application
cd $TESTAPPDIR/testapp/install
php installer.php

echo "Done."
