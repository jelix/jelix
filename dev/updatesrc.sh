#!/bin/bash

CURRENTDIR=$(dirname $0)
BASEDIR=$CURRENTDIR/..
cd $BASEDIR
php build/buildjelix.php -D MAIN_TARGET_PATH=dev/php56/_build dev/jelix.ini
php build/buildapp.php -D MAIN_TARGET_PATH=dev/php56/_build dev/testapp.ini
php build/buildjelix.php -D MAIN_TARGET_PATH=dev/php7/_build dev/jelix.ini
php build/buildapp.php -D MAIN_TARGET_PATH=dev/php7/_build dev/testapp.ini

cd dev/

#if [ -d _build/temp/testapp/www/ ]; then
#    rm -rf _build/temp/testapp/www/*
#fi
