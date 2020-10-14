#!/usr/bin/env bash
ROOTDIR="/jelixapp"
APPNAME="testapp"
APPDIR="$ROOTDIR/$APPNAME"
VAGRANTDIR="$APPDIR/vagrant"
LDAPCN="testapp17"

export DEBIAN_FRONTEND=noninteractive

echo "slapd slapd/internal/adminpw password passjelix" | debconf-set-selections
echo "slapd slapd/password1 password passjelix" | debconf-set-selections
echo "slapd slapd/password2 password passjelix" | debconf-set-selections
echo "slapd slapd/internal/generated_adminpw password passjelix" | debconf-set-selections
echo "slapd shared/organization string orgjelix" | debconf-set-selections
echo "slapd slapd/domain string $APPHOSTNAME" | debconf-set-selections

apt-get -y install slapd ldap-utils

# server configuration
cp $VAGRANTDIR/ldap/default /etc/default/slapd

# client configuration
cp $VAGRANTDIR/ldap/ldap.conf /etc/ldap/

service slapd restart

# certificates have been created with gencerts.sh
adduser openldap ssl-cert

echo "configure ssl"
ldapmodify -Y EXTERNAL -H ldapi:/// -f $VAGRANTDIR/ldap/ldap_ssl.ldif
#ldapsearch -Y EXTERNAL -H ldapi:/// -b cn=config | grep TLS

echo "add default users for tests"
ldapadd -x -D cn=admin,dc=tests,dc=jelix -w passjelix -f $VAGRANTDIR/ldap/ldap_conf.ldif
#ldapsearch -x -D cn=admin,dc=tests,dc=jelix -w passjelix -b "dc=tests,dc=jelix" "(objectClass=*)"

