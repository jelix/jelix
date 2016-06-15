#!/bin/bash

cd testapp/tests-jelix/
../vendor/bin/phpunit -v -d xdebug.overload_var_dump=0
EXITCODE=$?

if [ $EXITCODE != 0 ]; then
    echo "Tests failed. See errors log:"
    if [ -f ../var/log/errors.log ]; then
        cat ../var/log/errors.log
    else
        echo "/!\\ no errors.log file"
        #echo "--------------------------------------------- index"
        #curl http://testapp.local/index.php
    fi
    #echo "--------------------------------------------- info"
    #curl http://testapp.local/info.php

    echo ""
    echo "messages.log:"
    if [ -f ../var/log/messages.log ]; then
        cat ../var/log/messages.log
    else
        echo "/!\\ no messages.log file"
    fi

fi
exit $EXITCODE
