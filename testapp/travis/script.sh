#!/bin/bash

cd testapp/tests-jelix/
../vendor/bin/phpunit
EXITCODE=$?

if [ $EXITCODE != 0 ]; then
    echo "Tests failed. See errors log:"
    if [ -f ../var/log/errors.log ]; then
        cat ../var/log/errors.log
    else
        echo "/!\\ no errors.log file"
        echo "--------------------------------------------- index"
        curl http://testapp.local/index.php
        echo "--------------------------------------------- info"
        curl http://testapp.local/info.php
        echo "--------------------------------------------- vhost"
        cat /etc/apache2/sites-available/default
    fi
fi
exit $EXITCODE
