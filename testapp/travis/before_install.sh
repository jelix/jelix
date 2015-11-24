#!/bin/bash

# local time
echo "Europe/Paris" > /etc/timezone
cp /usr/share/zoneinfo/Europe/Paris /etc/localtime
locale-gen fr_FR.UTF-8
update-locale LC_ALL=fr_FR.UTF-8

#package
apt-get update
apt-get install apache2 libapache2-mod-fastcgi
a2enmod rewrite actions fastcgi alias

# php-fpm
cp ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.conf.default ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.conf
~/.phpenv/versions/$(phpenv version-name)/sbin/php-fpm

# configure apache virtual hosts
cp -f testapp/travis/vhost.conf /etc/apache2/sites-available/default
sed -e "s?%TRAVIS_BUILD_DIR%?$(pwd)?g" --in-place /etc/apache2/sites-available/default
service apache2 restart

# prepare postgresql base
createuser -U postgres test_user --no-createdb --no-createrole --no-superuser
createdb -U postgres -E UTF8 -O test_user testapp

psql -d template1 -U postgres -c "ALTER USER test_user WITH ENCRYPTED PASSWORD 'jelix'"
psql -d testapp -U postgres -c "GRANT ALL PRIVILEGES ON DATABASE testapp TO test_user;"
psql -d testapp -U postgres -c "GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO test_user;"
psql -d testapp -U postgres -c "GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO test_user;"
psql -d testapp -U postgres -c "GRANT ALL PRIVILEGES ON ALL FUNCTIONS IN SCHEMA public TO test_user;"

# prepare mysql base
mysql -u root -e "CREATE DATABASE IF NOT EXISTS testapp CHARACTER SET utf8;CREATE USER test_user IDENTIFIED BY 'jelix';GRANT ALL ON $APPNAME.* TO test_user;FLUSH PRIVILEGES;"

chown -R :www-data testapp/var/log/ testapp/temp/ testapp/var/