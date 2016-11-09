#!/bin/bash

source $VAGRANTDIR/system.sh

initsystem

apt-get -y install postgresql postgresql-client
apt-get -y install redis-server memcached memcachedb

# create a database into pgsql + users
su postgres -c $VAGRANTDIR/create_pgsql_db.sh
echo "host    testapp,postgres         +test_group         0.0.0.0           0.0.0.0           md5" >> /etc/postgresql/$POSTGRESQL_VERSION/main/pg_hba.conf
service postgresql restart

if [ "$PHP_VERSION" == "5" ]; then
    apt-get -y install php5-xdebug
    cp $VAGRANTDIR/xdebug.ini /etc/php5/mods-available/
    service php5-fpm restart
else
    apt-get -y install php7.0-xdebug
    cp $VAGRANTDIR/xdebug.ini /etc/php5/mods-available/
    service php7.0-fpm restart
fi

resetComposer $ROOTDIR

resetComposer $APPDIR

# install phpunit
if [ ! -f /usr/bin/phpunit ]; then
    ln -s $APPDIR/vendor/bin/phpunit  /usr/bin/phpunit
fi

source $VAGRANTDIR/reset_testapp.sh

echo "Done."
