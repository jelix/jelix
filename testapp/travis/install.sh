#!/bin/bash
set -e

cp -a testapp/var/config/profiles.ini.php.dist testapp/var/config/profiles.ini.php
cp -a testapp/var/config/localconfig.ini.php.dist testapp/var/config/localconfig.ini.php
cp -a testapp/adminapp/var/config/profiles.ini.php.dist testapp/adminapp/var/config/profiles.ini.php
cp -a testapp/adminapp/var/config/localconfig.ini.php.dist testapp/adminapp/var/config/localconfig.ini.php

if [ ! -d testapp/temp ]; then
    mkdir testapp/temp
fi
if [ ! -d testapp/adminapp/temp ]; then
    mkdir testapp/adminapp/temp
fi

composer install --prefer-dist
cd testapp
composer install --prefer-dist

php install/installer.php

cd adminapp/install
php installer.php

