FROM php:7-apache

MAINTAINER Martin Kolek <kolek@modpreneur.com>

RUN apt-get update && apt-get -y install \
    apt-utils \
    libcurl4-openssl-dev \
    curl \
    zlib1g-dev \
    git \
    nano

RUN docker-php-ext-install curl zip mbstring opcache

# Install apcu
RUN pecl install -o -f apcu-5.1.3 apcu_bc-beta \
    && rm -rf /tmp/pear \
    && echo "extension=apcu.so" > /usr/local/etc/php/conf.d/apcu.ini \
    && echo "extension=apc.so" >> /usr/local/etc/php/conf.d/apcu.ini \
    && docker-php-ext-configure bcmath \
    && docker-php-ext-install bcmath

# prepare php and apache
RUN rm -rf /etc/apache2/sites-available/* /etc/apache2/sites-enabled/*

ENV APP_DOCUMENT_ROOT /var/app/web \
    && APACHE_RUN_USER www-data \
    && APACHE_RUN_GROUP www-data \
    && APACHE_LOG_DIR /var/log/apache2

ADD docker/php.ini /usr/local/etc/php/
ADD docker/000-default.conf /etc/apache2/sites-available/000-default.conf


WORKDIR /var/app

# Install composer
RUN curl -sS https://getcomposer.org/installer | php \
    && cp composer.phar /usr/bin/composer

RUN usermod -u 1000 www-data \
    && usermod -G staff www-data

# Install app
RUN rm -rf /var/app/*
ADD . /var/app


# enable apache and mod rewrite
RUN a2ensite 000-default.conf \
    && a2enmod expires \
    && a2enmod rewrite \
    && service apache2 restart


# terminal choose for nano
RUN echo "export TERM=xterm" >> /etc/bash.bashrc


RUN chmod +x entrypoint.sh
ENTRYPOINT ["sh", "entrypoint.sh"]
