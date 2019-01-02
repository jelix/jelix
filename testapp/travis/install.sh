#!/bin/bash

cp -a testapp/var/config/profiles.ini.php.dist testapp/var/config/profiles.ini.php
cp -a testapp/var/config/localconfig.ini.php.dist testapp/var/config/localconfig.ini.php
cp -a testapp/adminapp/var/config/profiles.ini.php.dist testapp/adminapp/var/config/profiles.ini.php
cp -a testapp/adminapp/var/config/localconfig.ini.php.dist testapp/adminapp/var/config/localconfig.ini.php
cp -a testapp/app/system/auth_ldap.coord.ini.php.dist testapp/app/system/auth_ldap.coord.ini.php

if [ ! -d testapp/temp ]; then
    mkdir testapp/temp
fi
if [ ! -d testapp/adminapp/temp ]; then
    mkdir testapp/adminapp/temp
fi

cd testapp
composer install --prefer-dist --no-suggest --no-interaction

php install/configure.php --no-interactive
php install/installer.php

cd adminapp/install
php installer.php

