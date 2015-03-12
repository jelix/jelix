#!/bin/bash

function initsystem () {
    # create hostname
    HOST=`grep $APPHOSTNAME /etc/hosts`
    if [ "$HOST" == "" ]; then
        echo "127.0.0.1 $APPHOSTNAME $APPHOSTNAME2" >> /etc/hosts
    fi
    hostname $APPHOSTNAME
    echo "$APPHOSTNAME" > /etc/hostname
    
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
    apt-get -y install mysql-server mysql-client
    apt-get -y install git phpmyadmin
    
    # create a database into mysql + users
    if [ ! -d /var/lib/mysql/$APPNAME/ ]; then
        echo "setting mysql database.."
        mysql -u root -pjelix -e "CREATE DATABASE IF NOT EXISTS $APPNAME CHARACTER SET utf8;CREATE USER test_user IDENTIFIED BY 'jelix';GRANT ALL ON $APPNAME.* TO test_user;FLUSH PRIVILEGES;"
    fi
    
    # install default vhost for apache
    cp $VAGRANTDIR/jelixapp/app.conf /etc/apache2/sites-available/$APPNAME.conf
    
    sed -i -- s/__APPHOSTNAME__/$APPHOSTNAME/g /etc/apache2/sites-available/$APPNAME.conf
    sed -i -- "s/__ALIAS_APPHOSTNAME2__/ServerAlias $APPHOSTNAME2/g" /etc/apache2/sites-available/$APPNAME.conf
    sed -i -- "s!__APPDIR__!$APPDIR!g" /etc/apache2/sites-available/$APPNAME.conf
    sed -i -- "s!__ROOTDIR__!$ROOTDIR!g" /etc/apache2/sites-available/$APPNAME.conf
    sed -i -- s/__APPNAME__/$APPNAME/g /etc/apache2/sites-available/$APPNAME.conf
    
    if [ ! -f /etc/apache2/sites-enabled/010-$APPNAME.conf ]; then
        ln -s /etc/apache2/sites-available/$APPNAME.conf /etc/apache2/sites-enabled/010-$APPNAME.conf
    fi
    if [ -f "/etc/apache2/sites-enabled/000-default.conf" ]; then
        rm -f "/etc/apache2/sites-enabled/000-default.conf"
    fi
    
    cp $VAGRANTDIR/jelixapp/php5_fpm.conf /etc/apache2/conf-available/
    # to avoid bug https://github.com/mitchellh/vagrant/issues/351
    echo "EnableSendfile Off" > /etc/apache2/conf-available/sendfileoff.conf
    
    a2enconf php5_fpm sendfileoff
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
}


function resetJelixMysql() {
    local base="$1"
    local login="$2"
    local pass="$3"
    local prefix="$4"
    mysql -u $login -p$pass -e "drop table if exists ${prefix}jacl2_group;drop table if exists ${prefix}jacl2_rights;drop table if exists ${prefix}jacl2_subject;drop table if exists ${prefix}jacl2_subject_group;drop table if exists ${prefix}jlx_cache;drop table if exists ${prefix}jlx_user;drop table if exists ${prefix}jsessions;drop table if exists ${prefix}jacl2_user_group;" $base;
}

function resetJelixTemp() {
    local appdir="$1"
    if [ ! -d $appdir/temp/ ]; then
        mkdir $appdir/temp/
    else
        rm -rf $appdir/temp/*
    fi
    touch $appdir/temp/.dummy
}

function resetJelixInstall() {
    local appdir="$1"

    if [ -f $appdir/var/config/CLOSED ]; then
        rm -f $appdir/var/config/CLOSED
    fi

    if [ ! -d $appdir/var/log ]; then
        mkdir $appdir/var/log
    fi

    if [ -f $appdir/var/config/profiles.ini.php.dist ]; then
        cp -a $appdir/var/config/profiles.ini.php.dist $appdir/var/config/profiles.ini.php
    fi
    if [ -f $appdir/var/config/localconfig.ini.php.dist ]; then
        cp -a $appdir/var/config/localconfig.ini.php.dist $appdir/var/config/localconfig.ini.php
    fi
    if [ -f $appdir/var/config/installer.ini.php ]; then
        rm -f $appdir/var/config/installer.ini.php
    fi
}

function runComposer() {
    cd $1
    composer install
}

function resetComposer() {
    cd $1
    if [ -f composer.lock ]; then
        rm -f composer.lock
    fi
    composer install
}

function initapp() {
    php $1/install/installer.php
}

