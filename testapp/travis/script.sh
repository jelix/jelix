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
    fi
fi
exit $EXITCODE
