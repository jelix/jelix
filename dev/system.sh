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
    apt-get install -y software-properties-common apt-transport-https
    if [ "$PHP53" != "yes" ]; then
        apt-key adv --keyserver keyserver.ubuntu.com --recv-keys AC0E47584A7A714D
        echo "deb https://packages.sury.org/php jessie main" > /etc/apt/sources.list.d/sury_php.list
    fi

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
    if [ "$PHP53" != "yes" ]; then
        apt-get -y install  php${PHP_VERSION}-fpm \
                            php${PHP_VERSION}-cli \
                            php${PHP_VERSION}-curl \
                            php${PHP_VERSION}-gd \
                            php${PHP_VERSION}-intl \
                            php${PHP_VERSION}-mysql \
                            php${PHP_VERSION}-pgsql \
                            php${PHP_VERSION}-sqlite3 \
                            php${PHP_VERSION}-soap \
                            php${PHP_VERSION}-dba \
                            php${PHP_VERSION}-xml \
                            php${PHP_VERSION}-mbstring \
                            php-memcache \
                            php-memcached \
                            php-redis
        sed -i "/^user = www-data/c\user = vagrant" /etc/php/$PHP_VERSION/fpm/pool.d/www.conf
        sed -i "/^group = www-data/c\group = vagrant" /etc/php/$PHP_VERSION/fpm/pool.d/www.conf
        sed -i "/display_errors = Off/c\display_errors = On" /etc/php/$PHP_VERSION/fpm/php.ini
        sed -i "/display_errors = Off/c\display_errors = On" /etc/php/$PHP_VERSION/cli/php.ini
        service php${PHP_VERSION}-fpm restart
    else
        apt-get -y install  php5-fpm \
                            php5-cli \
                            php5-curl \
                            php5-gd \
                            php5-intl \
                            php5-mysql \
                            php5-pgsql \
                            php5-sqlite \
                            php5-mcrypt \
                            php5-memcache \
                            php5-memcached
        sed -i "/listen = 127.0.0.1:9000/c\listen = \\/var\\/run\\/php5-fpm.sock" /etc/php5/fpm/pool.d/www.conf
        sed -i "/^user = www-data/c\user = vagrant" /etc/php5/fpm/pool.d/www.conf
        sed -i "/^group = www-data/c\group = vagrant" /etc/php5/fpm/pool.d/www.conf
        sed -i "/^;listen.owner = www-data/c\listen.owner = www-data" /etc/php5/fpm/pool.d/www.conf
        sed -i "/^;listen.group = www-data/c\listen.group = www-data" /etc/php5/fpm/pool.d/www.conf
        sed -i "/^;listen.mode = 0660/c\listen.mode = 0660" /etc/php5/fpm/pool.d/www.conf
        sed -i "/display_errors = Off/c\display_errors = On" /etc/php5/fpm/php.ini
        sed -i "/display_errors = Off/c\display_errors = On" /etc/php5/cli/php.ini
        service php5-fpm restart
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

    if [ "$PHP53" == "yes" ]; then
        sed -i -- s/fastcgi.conf/fastcgi_params/g /etc/nginx/sites-available/$APPNAME.conf
    fi

    if [ ! -f /etc/nginx/sites-enabled/010-$APPNAME.conf ]; then
        ln -s /etc/nginx/sites-available/$APPNAME.conf /etc/nginx/sites-enabled/010-$APPNAME.conf
    fi
    if [ -f "/etc/nginx/sites-enabled/default" ]; then
        rm -f "/etc/nginx/sites-enabled/default"
    fi

    # restart nginx
    service nginx restart
    
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
    if [ ! -d $appdir/../temp/testapp ]; then
        mkdir -p $appdir/../temp/testapp
    else
        rm -rf $appdir/../temp/testapp/*
    fi
    touch $appdir/../temp/testapp/.dummy
}

function resetJelixInstall() {
    local appdir="$1"

    if [ -f $appdir/var/config/CLOSED ]; then
        rm -f $appdir/var/config/CLOSED
    fi

    if [ ! -d $appdir/var/log ]; then
        mkdir $appdir/var/log
    fi
    phpv="${PHP_VERSION:0:1}"
    echo "PHPV: "$phpv
    if [ -f $appdir/var/config/profiles.ini.php${phpv}.dist ]; then
        cp -a $appdir/var/config/profiles.ini.php${phpv}.dist $appdir/var/config/profiles.ini.php
    fi
    if [ -f $appdir/var/config/localconfig.ini.php.dist ]; then
        cp -a $appdir/var/config/localconfig.ini.php.dist $appdir/var/config/localconfig.ini.php
    fi
    if [ -f $appdir/var/config/installer.ini.php ]; then
        rm -f $appdir/var/config/installer.ini.php
    fi
    resetJelixTemp $appdir
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

