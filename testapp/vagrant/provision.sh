#!/bin/bash
ROOTDIR="/jelixapp"
APPNAME="testapp"
APPDIR="$ROOTDIR/$APPNAME"
VAGRANTDIR="$APPDIR/vagrant"

# create hostname
HOST=`grep testapp20 /etc/hosts`
if [ "$HOST" == "" ]; then
    echo "127.0.0.1 testapp.local testapp20.local" >> /etc/hosts
fi
hostname testapp.local
echo "testapp20.local" > /etc/hostname

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
apt-get -y install git phpmyadmin

# create a database into mysql + users
if [ ! -d /var/lib/mysql/$APPNAME/ ]; then
    echo "setting mysql database.."
    mysql -u root -pjelix -e "CREATE DATABASE IF NOT EXISTS testapp CHARACTER SET utf8;CREATE USER test_user IDENTIFIED BY 'jelix';GRANT ALL ON testapp.* TO test_user;FLUSH PRIVILEGES;"
fi

# create a database into pgsql + users
su postgres -c /jelixapp/testapp/vagrant/create_pgsql_db.sh
echo "host    testapp,postgres         +test_group         0.0.0.0           0.0.0.0           md5" >> /etc/postgresql/9.3/main/pg_hba.conf
service postgresql restart

# install default vhost for apache
cp $VAGRANTDIR/$APPNAME.conf /etc/apache2/sites-available/

if [ ! -f /etc/apache2/sites-enabled/010-$APPNAME.conf ]; then
    ln -s /etc/apache2/sites-available/$APPNAME.conf /etc/apache2/sites-enabled/010-$APPNAME.conf
fi
if [ -f "/etc/apache2/sites-enabled/000-default.conf" ]; then
    rm -f "/etc/apache2/sites-enabled/000-default.conf"
fi


cp $VAGRANTDIR/php5_fpm.conf /etc/apache2/conf-available/
cp $VAGRANTDIR/otherport.conf /etc/apache2/conf-available/
# to avoid bug https://github.com/mitchellh/vagrant/issues/351
echo "EnableSendfile Off" > /etc/apache2/conf-available/sendfileoff.conf

a2enconf php5_fpm otherport sendfileoff
a2enmod actions alias fastcgi rewrite

sed -i "/^user = www-data/c\user = vagrant" /etc/php5/fpm/pool.d/www.conf
sed -i "/^group = www-data/c\group = vagrant" /etc/php5/fpm/pool.d/www.conf
sed -i "/display_errors = Off/c\display_errors = On" /etc/php5/fpm/php.ini
sed -i "/display_errors = Off/c\display_errors = On" /etc/php5/cli/php.ini

service php5-fpm restart

# restart apache
service apache2 reload

echo "Install composer.."
if [ ! -f /usr/local/bin/composer ]; then
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
fi

echo "Install testapp configuration file"
cp -a $APPDIR/var/config/profiles.ini.php.dist $APPDIR/var/config/profiles.ini.php
cp -a $APPDIR/var/config/localconfig.ini.php.dist $APPDIR/var/config/localconfig.ini.php
if [ -f $APPDIR/var/config/installer.ini.php ]; then
    rm -f $APPDIR/var/config/installer.ini.php
fi

# create temp directory
if [ ! -d $ROOTDIR/temp/$APPNAME ]; then
    mkdir $ROOTDIR/temp/$APPNAME
else
    rm -rf $ROOTDIR/temp/$APPNAME/*
    touch $ROOTDIR/temp/$APPNAME/.dummy
fi
if [ ! -d $APPDIR/var/log ]; then
    mkdir $APPDIR/var/log
fi

if [ ! -f $APPDIR/var/db/sqlite/tests.sqlite.bak ]; then
    cp -a $APPDIR/var/db/sqlite/tests.sqlite $APPDIR/var/db/sqlite/tests.sqlite.bak
fi
if [ ! -f $APPDIR/var/db/sqlite3/tests.sqlite3.bak ]; then
    cp -a $APPDIR/var/db/sqlite3/tests.sqlite3 $APPDIR/var/db/sqlite3/tests.sqlite3.bak
fi

# install phpunit
cd $APPDIR
composer install
if [ ! -f /usr/bin/phpunit ]; then
    ln -s $APPDIR/vendor/bin/phpunit  /usr/bin/phpunit
fi

# install the application
cd $APPDIR/install
php installer.php

echo "Done."
