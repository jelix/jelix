#!/bin/bash

cp -a testapp/var/config/profiles.ini.php.dist testapp/var/config/profiles.ini.php
cp -a testapp/var/config/localconfig.ini.php.dist testapp/var/config/localconfig.ini.php


composer install --prefer-source
cd testapp
composer install --prefer-source

php install/installer.php
