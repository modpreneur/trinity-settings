FROM php:7-apache

MAINTAINER Martin Kolek <kolek@modpreneur.com>

RUN apt-get update && apt-get -y install \
    apt-utils \
    libcurl4-openssl-dev \
    curl \
    wget\
    zlib1g-dev \
    git \
    nano

#add Debian servers up-to-date packages
RUN echo "deb http://packages.dotdeb.org jessie all" >> /etc/apt/sources.list \
    && echo "deb-src http://packages.dotdeb.org jessie all" >> /etc/apt/sources.list \
    && wget https://www.dotdeb.org/dotdeb.gpg \
    && apt-key add dotdeb.gpg \
    && apt-get update

RUN apt-get -y install \
     sqlite3 \
     libsqlite3-dev \
     php7.0-sqlite3 \
     phpunit

RUN docker-php-ext-install curl zip mbstring opcache pdo_sqlite

# Install apcu
RUN pecl install -o -f apcu-5.1.3 apcu_bc-beta \
    && rm -rf /tmp/pear \
    && echo "extension=apcu.so" > /usr/local/etc/php/conf.d/apcu.ini \
    && echo "extension=apc.so" >> /usr/local/etc/php/conf.d/apcu.ini \
    && docker-php-ext-configure bcmath \
    && docker-php-ext-install bcmath

ADD docker/php.ini /usr/local/etc/php/

# Install composer
RUN curl -sS https://getcomposer.org/installer | php \
    && cp composer.phar /usr/bin/composer

# Install app
RUN rm -rf /var/app/*
ADD . /var/app

# terminal choose for nano
RUN echo "export TERM=xterm" >> /etc/bash.bashrc

WORKDIR /var/app

RUN chmod +x entrypoint.sh
ENTRYPOINT ["sh", "entrypoint.sh"]