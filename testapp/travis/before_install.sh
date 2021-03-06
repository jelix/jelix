#!/bin/bash

PHPENV_VERSION_NAME=$1

if [ "$PHPENV_VERSION_NAME" == "" ]; then
    echo "error: PHP version name is missing from parameters"
    exit 1;
fi

#~/.phpenv is /home/travis/.phpenv
PHP_ROOT=~/.phpenv/versions/$PHPENV_VERSION_NAME


# ------------------ set local time
echo "Europe/Paris" > /etc/timezone
cp /usr/share/zoneinfo/Europe/Paris /etc/localtime
locale-gen fr_FR.UTF-8
update-locale LC_ALL=fr_FR.UTF-8

# ------------------- install packages
apt-get -y update
apt-get -y install debconf-utils

# --------------------- configure php-fpm

cp $PHP_ROOT/etc/php-fpm.conf.default $PHP_ROOT/etc/php-fpm.conf

# additional configuration can be put into # $PHP_ROOT/etc/php-fpm.d/*.conf

# set PHP user
cp $PHP_ROOT/etc/php-fpm.d/www.conf.default $PHP_ROOT/etc/php-fpm.d/www.conf
sed -i "/^user = nobody/c\user = travis" $PHP_ROOT/etc/php-fpm.d/www.conf
sed -i "/^group = nobody/c\group = travis" $PHP_ROOT/etc/php-fpm.d/www.conf

PHP_SOCK=$(cat $PHP_ROOT/etc/php-fpm.d/www.conf | grep "^listen *=" | cut -d"=" -f2 | sed -E 's/ //')
echo "PHP_SOCK=$PHP_SOCK"

echo "cgi.fix_pathinfo = 1" >> $PHP_ROOT/etc/php.ini
sed -i "/display_errors = Off/c\display_errors = On" $PHP_ROOT/etc/php.ini

# starts PHP fpm
$PHP_ROOT/sbin/php-fpm

cp -f testapp/travis/phpunit_bootstrap.php /srv/phpunit_bootstrap.php

chmod +x /home/travis

# ---------------------- Configure nginx
apt-get -y install nginx
cp -f testapp/travis/nginx_vhost.conf /etc/nginx/sites-available/default
sed -e "s?%TRAVIS_BUILD_DIR%?$(pwd)?g" --in-place /etc/nginx/sites-available/default
sed -e "s?%PHP_SOCK%?$PHP_SOCK?g" --in-place /etc/nginx/sites-available/default

service nginx restart

# ----------------------- ldap server
echo "slapd slapd/internal/adminpw password passjelix" | debconf-set-selections
echo "slapd slapd/password1 password passjelix" | debconf-set-selections
echo "slapd slapd/password2 password passjelix" | debconf-set-selections
echo "slapd slapd/internal/generated_adminpw password passjelix" | debconf-set-selections
echo "slapd shared/organization string orgjelix" | debconf-set-selections
echo "slapd slapd/domain string tests.jelix" | debconf-set-selections

apt-get -y install slapd ldap-utils

ldapadd -x -D cn=admin,dc=tests,dc=jelix -w passjelix -f testapp/travis/ldap_conf.ldif


# ----------------------- prepare postgresql base
createuser -U postgres test_user --no-createdb --no-createrole --no-superuser
createdb -U postgres -E UTF8 -O test_user testapp

psql -d template1 -U postgres -c "ALTER USER test_user WITH ENCRYPTED PASSWORD 'jelix'"
psql -d testapp -U postgres -c "GRANT ALL PRIVILEGES ON DATABASE testapp TO test_user;"
psql -d testapp -U postgres -c "GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO test_user;"
psql -d testapp -U postgres -c "GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO test_user;"
psql -d testapp -U postgres -c "GRANT ALL PRIVILEGES ON ALL FUNCTIONS IN SCHEMA public TO test_user;"

# ----------------------  prepare mysql base
mysql -u root -e "CREATE DATABASE IF NOT EXISTS testapp CHARACTER SET utf8;CREATE USER test_user IDENTIFIED BY 'jelix';GRANT ALL ON testapp.* TO test_user;FLUSH PRIVILEGES;"
