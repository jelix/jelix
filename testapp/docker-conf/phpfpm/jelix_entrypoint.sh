#!/bin/bash

set -e
set -x

if [ -n "$TLS_CA_CRT_FILENAME" ]; then
    cp -a /customcerts/$TLS_CA_CRT_FILENAME /etc/ssl/certs/tests_CA.crt
    chown root:groupphp /etc/ssl/certs/tests_CA.crt
    chmod 0444 /etc/ssl/certs/tests_CA.crt
fi

if [ -n "$LDAP_TLS_CRT_FILENAME" ]; then
    cp -a /customcerts/$LDAP_TLS_KEY_FILENAME /etc/ssl/ldap/ldap.key
    cp -a /customcerts/$LDAP_TLS_CRT_FILENAME /etc/ssl/ldap/ldap.crt
    chown root:groupphp /etc/ssl/ldap/ldap.key
    chown root:groupphp /etc/ssl/ldap/ldap.crt
    chmod 0444 /etc/ssl/ldap/ldap.crt
    chmod 0440 /etc/ssl/ldap/ldap.key
fi

APPDIR="/jelixapp/testapp"

if [ ! -f $APPDIR/var/config/profiles.ini.php ]; then
    echo "It seems databases and testapp are not configured yet. Please execute"
    echo "   ./app-ctl reset"
    echo "in order to setup databases and testapp, after containers will be ready."
fi
