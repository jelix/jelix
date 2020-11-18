FROM debian:buster-slim

ARG composer_version=2.0.3

ARG FPM_USER_ID=1000
ARG FPM_GROUP_ID=1000
ENV PHP_VERSION=7.4

RUN set -eux; \
	apt-get update; \
    apt-get -y install apt-transport-https lsb-release ca-certificates curl wget git; \
    wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg; \
    sh -c 'echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list'; \
    apt-get update;

RUN apt-get -y install \
    php${PHP_VERSION}-bcmath \
    php${PHP_VERSION}-cli \
    php${PHP_VERSION}-curl \
    php${PHP_VERSION}-dba \
    php${PHP_VERSION}-fpm \
    php${PHP_VERSION}-gd \
    php${PHP_VERSION}-intl \
    php${PHP_VERSION}-json \
    php${PHP_VERSION}-ldap \
    php${PHP_VERSION}-mbstring \
    php${PHP_VERSION}-mysql \
    php${PHP_VERSION}-odbc \
    php${PHP_VERSION}-pgsql \
    php${PHP_VERSION}-soap \
    php${PHP_VERSION}-sqlite3 \
    php${PHP_VERSION}-xml \
    php${PHP_VERSION}-xmlrpc \
    php${PHP_VERSION}-zip \
    php-redis \
    ldap-utils \
    php-xdebug \
    php-memcache \
    php-memcached \
    openssl \
    wget \
    postgresql-client \
    mariadb-client \
    unzip \
    ; apt-get clean

## Install Composer
RUN wget -O /bin/composer https://getcomposer.org/download/$composer_version/composer.phar \
    && chmod +x /bin/composer


RUN set -eux; \
    sed -i "/^display_errors =/c\display_errors = On" /etc/php/${PHP_VERSION}/fpm/php.ini; \
    sed -i "/^display_errors =/c\display_errors = On" /etc/php/${PHP_VERSION}/cli/php.ini; \
    addgroup --gid $FPM_GROUP_ID grouptest; \
    adduser --uid $FPM_USER_ID --gid $FPM_GROUP_ID usertest; \
    ln -s /usr/sbin/php-fpm${PHP_VERSION} /usr/sbin/php-fpm7; \
    mkdir -p /run/php/; \
    mkdir -p /etc/openldap/ /etc/ssl/ldap/; \
    chmod 755 /etc/openldap/ /etc/ssl/ldap/;


COPY profile.start /etc/profile.d/start
COPY www.conf /etc/php/${PHP_VERSION}/fpm/pool.d/www.conf
COPY ldap.conf /etc/openldap/ldap.conf
COPY entrypoint.sh /bin/
COPY appctl.sh /bin/
COPY phpunit_bootstrap.php /srv/phpunit_bootstrap.php
RUN chmod 755 /bin/entrypoint.sh /bin/appctl.sh

WORKDIR /jelixapp/testapp/
ENTRYPOINT ["/bin/entrypoint.sh"]
CMD ["/usr/sbin/php-fpm7", "-F", "-O"]
