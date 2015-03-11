#!/bin/bash
ROOTDIR="/jelixapp"
APPNAME="testapp"
APPDIR="$ROOTDIR/$APPNAME"
VAGRANTDIR="$APPDIR/vagrant"
APPHOSTNAME="testapp.local"
APPHOSTNAME2="testapp20.local"

source $VAGRANTDIR/jelixapp/system.sh

initsystem

apt-get -y install postgresql postgresql-client
apt-get -y install redis-server memcached memcachedb

# create a database into pgsql + users
su postgres -c /jelixapp/testapp/vagrant/create_pgsql_db.sh
echo "host    testapp,postgres         +test_group         0.0.0.0           0.0.0.0           md5" >> /etc/postgresql/9.3/main/pg_hba.conf
service postgresql restart

#cp $VAGRANTDIR/otherport.conf /etc/apache2/conf-available/
#a2enconf otherport

runComposer $ROOTDIR

runComposer $APPDIR

# install phpunit
if [ ! -f /usr/bin/phpunit ]; then
    ln -s $APPDIR/vendor/bin/phpunit  /usr/bin/phpunit
fi

source $VAGRANTDIR/reset_testapp.sh

echo "Done."
