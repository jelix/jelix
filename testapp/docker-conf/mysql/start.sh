#!/bin/sh

    # create a database into mysql + users
    if [ ! -d /var/lib/mysql/$APPNAME/ ]; then
        echo "setting mysql database.."
        mysql -u root -pjelix -e "CREATE DATABASE IF NOT EXISTS $APPNAME CHARACTER SET utf8;CREATE USER test_user IDENTIFIED BY 'jelix';GRANT ALL ON $APPNAME.* TO test_user;FLUSH PRIVILEGES;"
    fi


#sed -i -- s/bind-address\s+127\.0\.0\.1/bind-address 0.0.0.0/g /etc/mysql/my.cnf


