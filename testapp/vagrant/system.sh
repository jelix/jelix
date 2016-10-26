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

    # install all packages
    apt-get update
    apt-get -y upgrade
    apt-get -y install debconf-utils
    export DEBIAN_FRONTEND=noninteractive
    echo "mysql-server-$MYSQL_VERSION mysql-server/root_password password jelix" | debconf-set-selections
    echo "mysql-server-$MYSQL_VERSION mysql-server/root_password_again password jelix" | debconf-set-selections
    echo "phpmyadmin phpmyadmin/dbconfig-install boolean true" | debconf-set-selections
    echo "phpmyadmin phpmyadmin/reconfigure-webserver multiselect apache2" | debconf-set-selections
    echo "phpmyadmin phpmyadmin/mysql/admin-pass password jelix" | debconf-set-selections
    echo "phpmyadmin phpmyadmin/app-password-confirm password jelix" | debconf-set-selections
    echo "phpmyadmin phpmyadmin/mysql/app-pass password jelix" | debconf-set-selections
    echo "phpmyadmin phpmyadmin/password-confirm password jelix" | debconf-set-selections
    echo "phpmyadmin phpmyadmin/setup-password password jelix" | debconf-set-selections
    
    apt-get -y install nginx
    if [ "$PHP_VERSION" == "5" ]; then
        apt-get -y install php5-fpm php5-cli php5-curl php5-gd php5-intl php5-mcrypt php5-memcache php5-memcached php5-mysql php5-pgsql php5-sqlite
    else
        apt-get -y install php7.0-fpm php7.0-cli php7.0-curl php7.0-gd php7.0-intl php7.0-mcrypt php-memcached php7.0-mysql php7.0-pgsql php7.0-sqlite3 php7.0-soap php7.0-dba
    fi
    apt-get -y install mysql-server mysql-client
    apt-get -y install git phpmyadmin vim unzip curl

    # create a database into mysql + users
    if [ ! -d /var/lib/mysql/$APPNAME/ ]; then
        echo "setting mysql database.."
        mysql -u root -pjelix -e "CREATE DATABASE IF NOT EXISTS $APPNAME CHARACTER SET utf8;CREATE USER test_user IDENTIFIED BY 'jelix';GRANT ALL ON $APPNAME.* TO test_user;FLUSH PRIVILEGES;"
    fi
    
    # install default vhost for apache
    cp $VAGRANTDIR/vhost /etc/nginx/sites-available/$APPNAME.conf
    sed -i -- s/__APPHOSTNAME__/$APPHOSTNAME/g /etc/nginx/sites-available/$APPNAME.conf
    sed -i -- s/__APPHOSTNAME2__/$APPHOSTNAME2/g /etc/nginx/sites-available/$APPNAME.conf
    sed -i -- "s!__APPDIR__!$APPDIR!g" /etc/nginx/sites-available/$APPNAME.conf
    sed -i -- "s!__ROOTDIR__!$ROOTDIR!g" /etc/nginx/sites-available/$APPNAME.conf
    sed -i -- s/__APPNAME__/$APPNAME/g /etc/nginx/sites-available/$APPNAME.conf
    sed -i -- s/__FPM_SOCK__/$FPM_SOCK/g /etc/nginx/sites-available/$APPNAME.conf

    if [ ! -f /etc/nginx/sites-enabled/010-$APPNAME.conf ]; then
        ln -s /etc/nginx/sites-available/$APPNAME.conf /etc/nginx/sites-enabled/010-$APPNAME.conf
    fi
    if [ -f "/etc/nginx/sites-enabled/default" ]; then
        rm -f "/etc/nginx/sites-enabled/default"
    fi

    if [ "$PHP_VERSION" == "5" ]; then
        sed -i "/^user = www-data/c\user = vagrant" /etc/php5/fpm/pool.d/www.conf
        sed -i "/^group = www-data/c\group = vagrant" /etc/php5/fpm/pool.d/www.conf
        sed -i "/display_errors = Off/c\display_errors = On" /etc/php5/fpm/php.ini
        sed -i "/display_errors = Off/c\display_errors = On" /etc/php5/cli/php.ini
    else
        sed -i "/^user = www-data/c\user = vagrant" /etc/php/7.0/fpm/pool.d/www.conf
        sed -i "/^group = www-data/c\group = vagrant" /etc/php/7.0/fpm/pool.d/www.conf
        sed -i "/display_errors = Off/c\display_errors = On" /etc/php/7.0/fpm/php.ini
        sed -i "/display_errors = Off/c\display_errors = On" /etc/php/7.0/cli/php.ini
    fi

    if [ "$PHP_VERSION" == "5" ]; then
        service php5-fpm restart
    else
        service php7.0-fpm restart
    fi

    # restart nginx
    service nginx reload
    
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
    su -c "composer install" vagrant
}

function resetComposer() {
    cd $1
    if [ -f composer.lock ]; then
        rm -f composer.lock
    fi
    su -c "composer install" vagrant
}

function initapp() {
    php $1/install/installer.php
}

