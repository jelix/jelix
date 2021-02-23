#!/bin/bash
APPDIR="/jelixapp/testapp"
APP_USER=usertest
APP_GROUP=grouptest

COMMAND="$1"
shift

if [ "$COMMAND" == "" ]; then
    echo "Error: command is missing"
    exit 1;
fi

function resetJelixTemp() {
    echo "--- Reset testapp temp files in $1"
    local appdir="$1"
    if [ ! -d $appdir/var/log ]; then
        mkdir $appdir/var/log
        chown $APP_USER:$APP_GROUP $appdir/var/log
    fi
    if [ ! -d $appdir/temp/ ]; then
        mkdir $appdir/temp/
        chown $APP_USER:$APP_GROUP $appdir/temp
    else
        rm -rf $appdir/temp/*
    fi
    touch $appdir/temp/.dummy
    chown $APP_USER:$APP_GROUP $appdir/temp/.dummy
}

function resetApp() {
    echo "--- Reset testapp configuration files in $1"
    local appdir="$1"
    if [ -f $appdir/var/config/CLOSED ]; then
        rm -f $appdir/var/config/CLOSED
    fi

    for vardir in log db/sqlite3 mails uploads; do
      if [ ! -d $appdir/var/$vardir ]; then
          mkdir $appdir/var/$vardir
      else
          rm -rf $appdir/var/$vardir/*
      fi
      touch $appdir/var/$vardir/.dummy
    done

    if [ -f $appdir/app/system/auth_ldap.coord.ini.php.dist ]; then
        cp $appdir/app/system/auth_ldap.coord.ini.php.dist $appdir/app/system/auth_ldap.coord.ini.php
        chown -R $APP_USER:$APP_GROUP $appdir/app/system/auth_ldap.coord.ini.php
    fi
    if [ -f $appdir/var/config/profiles.ini.php.dist ]; then
        cp $appdir/var/config/profiles.ini.php.dist $appdir/var/config/profiles.ini.php
    fi
    if [ -f $appdir/var/config/localconfig.ini.php.dist ]; then
        cp $appdir/var/config/localconfig.ini.php.dist $appdir/var/config/localconfig.ini.php
    fi
    chown -R $APP_USER:$APP_GROUP $appdir/var/config/profiles.ini.php $appdir/var/config/localconfig.ini.php

    if [ -f $appdir/var/config/installer.ini.php ]; then
        rm -f $appdir/var/config/installer.ini.php
    fi
    if [ -f $appdir/var/config/liveconfig.ini.php ]; then
        rm -f $appdir/var/config/liveconfig.ini.php
    fi

    setRights $appdir
    launchInstaller $appdir
}

function resetSqlite3() {
  echo "--- Reset sqlite3 database in $1"
  local appdir="$1"
  if [ -f $appdir/var/db/sqlite3/tests.sqlite3 ]; then
    rm -f $appdir/var/db/sqlite3/tests.sqlite3
  fi

}

function resetMysql() {
    echo "--- Reset mysql database for database $1, prefix $4"
    local base="$1"
    local login="$2"
    local pass="$3"
    local prefix="$4"
    mysql -h mysql -u $login -p$pass -e "drop table if exists ${prefix}jacl2_group;drop table if exists ${prefix}jacl2_rights;drop table if exists ${prefix}jacl2_subject;drop table if exists ${prefix}jacl2_subject_group;drop table if exists ${prefix}jlx_cache;drop table if exists ${prefix}jlx_user;drop table if exists ${prefix}jsessions;drop table if exists ${prefix}jacl2_user_group;" $base;

    MYSQLTABLES="labels1_test labels_test myconfig product_tags_test product_test products towns testkvdb"
    for TABLE in $MYSQLTABLES
    do
        mysql -h mysql -u $login -p$pass  -e "drop table if exists $TABLE;" testapp;
    done

}

function resetPostgresql() {
  echo "--- Reset Postgresql database"
  PGTABLES="jacl2_group jacl2_rights jacl2_subject jacl2_subject_group jacl2_user_group jsessions labels1_tests labels_tests product_tags_test product_test products testkvdb"
  for TABLE in $PGTABLES
  do
      PGPASSWORD=jelix psql -h pgsql -U test_user -d testapp -c "drop table if exists $TABLE cascade;"
  done

}


function launchInstaller() {
    echo "--- Launch testapp installer in $1"
    local appdir="$1"
    su $APP_USER -c "php $appdir/install/installer.php --verbose"
}

function setRights() {
    echo "--- Set rights on directories and files in $1"
    local appdir="$1"
    USER="$2"
    GROUP="$3"

    if [ "$USER" = "" ]; then
        USER="$APP_USER"
    fi

    if [ "$GROUP" = "" ]; then
        GROUP="$APP_GROUP"
    fi

    DIRS="$appdir/var/config $appdir/var/db $appdir/var/log $appdir/var/mails $appdir/var/uploads $appdir/temp/"
    for VARDIR in $DIRS; do
      if [ ! -d $VARDIR ]; then
        mkdir -p $VARDIR
      fi
      chown -R $USER:$GROUP $VARDIR
      chmod -R ug+w $VARDIR
      chmod -R o-w $VARDIR
    done

}

function composerInstall() {
    echo "--- Install Composer packages"
    if [ -f $APPDIR/composer.lock ]; then
        rm -f $APPDIR/composer.lock
    fi
    composer install --prefer-dist --no-progress --no-ansi --no-interaction --working-dir=$APPDIR
    chown -R $APP_USER:$APP_GROUP $APPDIR/vendor $APPDIR/composer.lock
}

function composerUpdate() {
    echo "--- Update Composer packages"
    if [ -f $APPDIR/composer.lock ]; then
        rm -f $APPDIR/composer.lock
    fi
    composer update --prefer-dist --no-progress --no-ansi --no-interaction --working-dir=$APPDIR
    chown -R $APP_USER:$APP_GROUP $APPDIR/vendor $APPDIR/composer.lock
}

function launch() {
    echo "--- Launch testapp setup in $1"
    local appdir="$1"
    if [ ! -f $appdir/var/config/profiles.ini.php ]; then
        cp $appdir/var/config/profiles.ini.php.dist $appdir/var/config/profiles.ini.php
    fi
    if [ ! -f $appdir/var/config/localconfig.ini.php ]; then
        cp $appdir/var/config/localconfig.ini.php.dist $appdir/var/config/localconfig.ini.php
    fi
    chown -R $APP_USER:$APP_GROUP $appdir/var/config/profiles.ini.php $APPDIR/var/config/localconfig.ini.php

    if [ ! -d $appdir/vendor ]; then
      composerInstall
    fi

    resetApp $appdir
    launchInstaller $appdir
    setRights $appdir
}


case $COMMAND in
    clean_tmp)
        resetJelixTemp $APPDIR
        resetJelixTemp $APPDIR/adminapp
        ;;
    reset)
        resetMysql testapp test_user jelix
        resetMysql testapp test_user jelix admin_
        resetPostgresql
        resetSqlite3 $APPDIR
        resetSqlite3 $APPDIR/adminapp
        resetJelixTemp $APPDIR
        resetJelixTemp $APPDIR/adminapp
        composerInstall
        resetApp $APPDIR
        resetApp $APPDIR/adminapp
        ;;
    install)
        launchInstaller $APPDIR
        launchInstaller $APPDIR/adminapp
        ;;
    rights)
        setRights $APPDIR
        setRights $APPDIR/adminapp
        ;;
    composer_install)
        composerInstall;;
    composer_update)
        composerUpdate;;
    unit-tests)
        UTCMD="cd $APPDIR/tests-jelix/ && ../vendor/bin/phpunit  $@"
        su $APP_USER -c "$UTCMD"
        ;;
    *)
        echo "wrong command"
        exit 2
        ;;
esac

