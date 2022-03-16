#!/bin/bash

CURRENTDIR=$(dirname $0)
BASEDIR=$CURRENTDIR/../..
cd $BASEDIR
php build/buildjelix.php -D MAIN_TARGET_PATH=dev/php8.1/_build dev/jelix.ini
php build/buildapp.php -D MAIN_TARGET_PATH=dev/php8.1/_build dev/testapp.ini

cd dev/php8.1/

