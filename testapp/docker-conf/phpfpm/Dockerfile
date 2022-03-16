ARG php_version=8.1

FROM 3liz/liz-php-fpm:${php_version}

ARG php_version
ARG DEBIAN_FRONTEND=noninteractive
ENV PHP_VERSION=${php_version}

RUN apt-get update;  \
    apt-get -y install \
    php${PHP_VERSION}-odbc \
    php${PHP_VERSION}-xdebug \
    apt-utils \
    ldap-utils \
    openssl \
    postgresql-client \
    mariadb-client \
    ; if [ "$PHP_VERSION" != "8.0" -a "$PHP_VERSION" != "8.1" ]; then apt-get -y install \
        php${PHP_VERSION}-xmlrpc \
    ; fi \
    ; \
    apt-get clean


RUN set -eux; \
    mkdir -p /etc/openldap/ /etc/ssl/ldap/; \
    chmod 755 /etc/openldap/ /etc/ssl/ldap/;


COPY profile.start /etc/profile.d/start
COPY ldap.conf /etc/openldap/ldap.conf
COPY jelix_entrypoint.sh /bin/entrypoint.d/
COPY appctl.sh /bin/
COPY phpunit_bootstrap.php /srv/phpunit_bootstrap.php
RUN chmod 755 /bin/entrypoint.d/jelix_entrypoint.sh /bin/appctl.sh

WORKDIR /jelixapp/testapp/
