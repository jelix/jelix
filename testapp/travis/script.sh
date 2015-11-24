#!/bin/bash

cd testapp/tests-jelix/
../vendor/bin/phpunit
EXITCODE=$?

if [ $EXITCODE != 0 ]; then
    if [ -f ../var/log/errors.log ]; then
        cat ../var/log/errors.log
    fi
fi
exit $EXITCODE
