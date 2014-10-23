#!/bin/bash

CURRENTDIR=$(dirname $0)
BASEDIR=$CURRENTDIR/..
cd $BASEDIR
php build/buildjelix.php dev/jelix.ini
php build/buildapp.php dev/testapp.ini

cd dev/

if [ -d _build/temp/testapp/www/ ]; then
    rm -rf _build/temp/testapp/www/*
fi
