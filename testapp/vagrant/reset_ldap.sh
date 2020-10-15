#!/bin/bash
ROOTDIR="/jelixapp"
APPNAME="testapp"
APPDIR="$ROOTDIR/$APPNAME"
VAGRANTDIR="$APPDIR/vagrant"
LDAPCN="testapp18"

ldapdelete -x -c -D cn=admin,dc=tests,dc=jelix -w passjelix -f $VAGRANTDIR/ldap/ldap_delete.ldif
ldapadd -x -c -D cn=admin,dc=tests,dc=jelix -w passjelix -f $VAGRANTDIR/ldap/ldap_conf.ldif
