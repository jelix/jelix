ARG php_version=8.1

FROM 3liz/liz-php-fpm:${php_version}

ARG php_version
ARG DEBIAN_FRONTEND=noninteractive
ENV PHP_VERSION=${php_version}

RUN set -e; apt-get update; \
    apt-get -y -o Dpkg::Options::='--force-confdef' -o Dpkg::Options::='--force-confold' upgrade; \
    apt-get -y -o Dpkg::Options::='--force-confdef' -o Dpkg::Options::='--force-confold' install \
    php${PHP_VERSION}-odbc \
    php${PHP_VERSION}-xdebug \
    apt-utils \
    ldap-utils \
    openssl \
    vim \
    postgresql-client \
    mariadb-client \
    ; if [ "$PHP_VERSION" != "8.0" -a "$PHP_VERSION" != "8.1" ]; then \
        apt-get -y install \
        php${PHP_VERSION}-xmlrpc \
    ; fi \
    ; \
    apt-get clean


RUN set -eux; \
    mkdir -p /etc/openldap/ /etc/ssl/ldap/; \
    chmod 755 /etc/openldap/ /etc/ssl/ldap/;

COPY xdebug_fpm.ini  /etc/php/${PHP_VERSION}/fpm/conf.d/90-xdebug.ini
COPY xdebug_cli.ini  /etc/php/${PHP_VERSION}/cli/conf.d/90-xdebug.ini
COPY profile.start /etc/profile.d/start
COPY ldap.conf /etc/openldap/ldap.conf
COPY jelix_entrypoint.sh /bin/entrypoint.d/
COPY appctl.sh /bin/
COPY phpunit_bootstrap.php /srv/phpunit_bootstrap.php
RUN chmod 755 /bin/entrypoint.d/jelix_entrypoint.sh /bin/appctl.sh

WORKDIR /jelixapp/testapp/
