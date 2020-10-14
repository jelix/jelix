FROM alpine:3.9
# 3.12: php 7.3
# 3.9: php 7.2
# 3.8: php 5.6.40
# 3.7: php 7.1
# 3.5: php 7.0

ARG composer_version=1.8.6

ARG FPM_USER_ID=1000
ARG FPM_GROUP_ID=1000

RUN apk update && apk upgrade
RUN apk --no-cache add git php7 php7-fpm \
    php7-tokenizer \
    php7-opcache \
    php7-session \
    php7-iconv \
    php7-intl \
    php7-mbstring \
    php7-openssl \
    php7-fileinfo \
    php7-curl \
    php7-json \
    php7-sqlite3 \
    php7-gd \
    php7-dom \
    php7-xml \
    php7-xmlrpc \
    php7-xmlreader \
    php7-xmlwriter \
    php7-simplexml \
    php7-zip \
    php7-phar \
    php7-gettext \
    php7-ctype \
    php7-ldap \
    php7-redis \
    php7-mysqli \
    php7-pgsql \
    php7-soap \
    php7-dba \
    php7-memcached \
    php7-pdo \
    php7-pdo_mysql \
    php7-pdo_pgsql \
    php7-pdo_sqlite \
    openssl \
    wget \
    postgresql-client \
    mysql-client

RUN set -eux; \
    sed -i "/^display_errors =/c\display_errors = On" /etc/php7/php.ini; \
    addgroup -g $FPM_GROUP_ID grouptest; \
    adduser -D -u $FPM_USER_ID -G grouptest usertest; \
    mkdir -p /etc/openldap/ /etc/ssl/ldap/; \
    chmod 755 /etc/openldap/ /etc/ssl/ldap/;


## Install Composer
RUN wget -O /bin/composer https://getcomposer.org/download/$composer_version/composer.phar \
    && chmod +x /bin/composer

COPY profile.start /etc/profile.d/start
COPY www.conf /etc/php7/php-fpm.d/www.conf
COPY ldap.conf /etc/openldap/ldap.conf
COPY entrypoint.sh /bin/
COPY appctl.sh /bin/
COPY phpunit_bootstrap.php /srv/phpunit_bootstrap.php
RUN chmod 755 /bin/entrypoint.sh /bin/appctl.sh

ENV PHP_INI_DIR /etc/php7

WORKDIR /jelixapp/
ENTRYPOINT ["/bin/entrypoint.sh"]
CMD ["/usr/sbin/php-fpm7", "-F", "-O"]
