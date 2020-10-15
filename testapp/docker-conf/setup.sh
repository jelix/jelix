#!/bin/bash

./ssl.sh createCA tests.jelix
./ssl.sh createCert ldap.jelix tests.jelix

#cp certs/ldap.* openldap/certs/
#cp certs/tests.jelix-CA.* openldap/certs/
