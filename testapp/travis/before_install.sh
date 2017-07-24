#!/bin/bash

# local time
echo "Europe/Paris" > /etc/timezone
cp /usr/share/zoneinfo/Europe/Paris /etc/localtime
locale-gen fr_FR.UTF-8
update-locale LC_ALL=fr_FR.UTF-8

#package
apt-get -y upgrade
apt-get -y install debconf-utils
apt-get install apache2 libapache2-mod-fastcgi
a2enmod rewrite actions fastcgi alias


echo "slapd slapd/internal/adminpw password passjelix" | debconf-set-selections
echo "slapd slapd/password1 password passjelix" | debconf-set-selections
echo "slapd slapd/password2 password passjelix" | debconf-set-selections
echo "slapd slapd/internal/generated_adminpw password passjelix" | debconf-set-selections
echo "slapd shared/organization string orgjelix" | debconf-set-selections
echo "slapd slapd/domain string testapp17.local" | debconf-set-selections

apt-get -y install slapd ldap-utils

ldapadd -x -D cn=admin,dc=testapp17,dc=local -w passjelix -f testapp/vagrant/ldap_conf.ldif

# php-fpm

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
mysql -u root -e "CREATE DATABASE IF NOT EXISTS testapp CHARACTER SET utf8;CREATE USER test_user IDENTIFIED BY 'jelix';GRANT ALL ON testapp.* TO test_user;FLUSH PRIVILEGES;"
