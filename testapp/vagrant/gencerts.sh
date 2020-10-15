#!/bin/bash

EXTRA_DOMAIN=testapp16.local

if [ "$1" == "--reset" ]; then
  rm -f /etc/ssl/private/jelix_tests_CA.key
  rm -f /etc/ssl/certs/jelix_tests_CA.crt
  rm -f /etc/ssl/private/testapp.local.key
  rm -f /etc/ssl/certs/testapp.local.crt
  rm -f /etc/ssl/certs/testapp.local.csr

  echo "Existing certificates and keys, removed"
  echo ""
fi


# creates the private key for the CA
if [ ! -f /etc/ssl/private/jelix_tests_CA.key ]; then
  openssl genrsa -passout pass:jelix -out /etc/ssl/private/jelix_tests_CA.key 2048
  chown root:ssl-cert /etc/ssl/private/jelix_tests_CA.key
  chmod 0640 /etc/ssl/private/jelix_tests_CA.key
  echo "private key for the CA created"
  echo ""
fi

REGEN_CRT=0

# Creates and self-sign the certficate for the CA
if [ ! -f /etc/ssl/certs/jelix_tests_CA.crt ]; then
  openssl req -x509 -new -nodes \
      -key /etc/ssl/private/jelix_tests_CA.key \
      -sha256 -days 3650 \
      -out /etc/ssl/certs/jelix_tests_CA.crt \
      --passin pass:jelix  \
      -subj "/C=FR/ST=France/L=Paris/O=Jelix/OU=dev/CN=testapp.local"

  chown root:ssl-cert /etc/ssl/certs/jelix_tests_CA.crt
  chmod 0644 /etc/ssl/certs/jelix_tests_CA.crt
  REGEN_CRT=1
  echo "certficate for the CA created and self-signed"
  echo ""
fi


#Generates the key of the cert for the web/ldap
if [ ! -f /etc/ssl/private/testapp.local.key ]; then
  openssl genrsa -out /etc/ssl/private/testapp.local.key -passout pass:  2048
  chown root:ssl-cert /etc/ssl/private/testapp.local.key
  chmod 0640 /etc/ssl/private/testapp.local.key
  REGEN_CRT=1
  echo "key of the cert for the web/ldap created"
  echo ""
fi

# create the CSR for the cert for the web/ldap
if [ ! -f /etc/ssl/certs/testapp.local.crt -o "$REGEN_CRT" == "1" ]; then

  # create configuration file. We need it for both CSR and CRT
  cp /etc/ssl/openssl.cnf /etc/ssl/testapp.local.cnf
  (printf "[SAN]\nextendedKeyUsage=serverAuth,clientAuth,codeSigning\nbasicConstraints=CA:FALSE\nkeyUsage=nonRepudiation,digitalSignature,keyEncipherment,dataEncipherment\nnsCertType=client,server\nsubjectAltName=DNS:tests.jelix,DNS:$EXTRA_DOMAIN") >> /etc/ssl/testapp.local.cnf

  openssl req -new -sha256 \
    -key /etc/ssl/private/testapp.local.key \
    -out /etc/ssl/private/testapp.local.csr \
    -subj "/C=FR/ST=France/L=Paris/O=Jelix/OU=dev/CN=testapp.local" \
    -reqexts SAN \
    -config /etc/ssl/testapp.local.cnf

  chown root:ssl-cert /etc/ssl/private/testapp.local.csr
  chmod 0644 /etc/ssl/private/testapp.local.csr

  echo "CSR of the cert for the web/ldap created"
  echo ""

  # verifie the CSR
  #openssl req -noout -text -in /etc/ssl/private/testapp.local.csr

  # Generate the certificate using the testapp.local csr and key along with the CA Root key
  openssl x509 -req -in /etc/ssl/private/testapp.local.csr \
    -CA /etc/ssl/certs/jelix_tests_CA.crt \
    -CAkey /etc/ssl/private/jelix_tests_CA.key \
    -CAcreateserial \
    -passin pass:jelix \
    -extfile /etc/ssl/testapp.local.cnf \
    -extensions SAN \
    -out /etc/ssl/certs/testapp.local.crt \
    -days 500 -sha256

  chown root:ssl-cert /etc/ssl/certs/testapp.local.crt
  chmod 0644 /etc/ssl/certs/testapp.local.crt

  echo "Cert for the web/ldap created"
  echo ""
  # Verify the certificate
  #openssl x509 -text -noout -in /etc/ssl/certs/testapp.local.crt
fi

mkdir -p /etc/ssl/ldap/
cp /etc/ssl/certs/testapp.local.crt  /etc/ssl/ldap/
cp /etc/ssl/private/testapp.local.key  /etc/ssl/ldap/
chown vagrant:ssl-cert /etc/ssl/ldap/testapp.local.crt
chown vagrant:ssl-cert /etc/ssl/ldap/testapp.local.key
chmod 0444 /etc/ssl/ldap/testapp.local.crt
chmod 0440 /etc/ssl/ldap/testapp.local.key


