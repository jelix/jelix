#!/bin/bash

source $VAGRANTDIR/system.sh

initsystem

source $VAGRANTDIR/gencerts.sh

apt-get -y install postgresql postgresql-client
apt-get -y install redis-server memcached

if [ "$DISTRO" == "jessie" ]; then
  apt-get -y install memcacheddb
fi

# create a database into pgsql + users
su postgres -c $VAGRANTDIR/create_pgsql_db.sh
echo "host    testapp,postgres         +test_group         0.0.0.0           0.0.0.0           md5" >> /etc/postgresql/$POSTGRESQL_VERSION/main/pg_hba.conf
service postgresql restart

source $VAGRANTDIR/setup_ldap.sh

apt-get -y install php-xdebug
cp $VAGRANTDIR/xdebug.ini /etc/php/$PHP_VERSION/mods-available/
service php${PHP_VERSION}-fpm restart

resetComposer $ROOTDIR

# install phpunit
if [ ! -f /usr/bin/phpunit ]; then
    ln -s $APPDIR/vendor/bin/phpunit  /usr/bin/phpunit
fi

source $VAGRANTDIR/reset_testapp.sh

echo "Done."
