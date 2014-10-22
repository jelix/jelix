#!/bin/bash

CURRENTDIR=$(dirname $0)
BASEDIR=$CURRENTDIR/..
cd $BASEDIR
php build/buildjelix.php dev/jelix.ini
php build/buildapp.php dev/testapp.ini

cd $CURRENTDIR
