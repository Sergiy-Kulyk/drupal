FROM php:7.4.9-apache

# Packages
RUN apt-get update
RUN apt-get -y install libmcrypt-dev
RUN apt-get -y install git
RUN apt-get -y install unzip
#RUN apt-get -y install libxml2-dev
#RUN apt-get -y install zlib1g-dev
RUN apt-get -y install default-mysql-client

## PHP extentions
#RUN docker-php-ext-install mysqli
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install pcntl
#RUN docker-php-ext-install json
#RUN docker-php-ext-install soap

# PECL extensions
RUN pecl channel-update pecl.php.net
RUN pecl install xdebug-2.9.0 && docker-php-ext-enable xdebug

## Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php && \
    rm composer-setup.php && \
    mv composer.phar /usr/bin/composer

## Config
COPY vhost.conf /etc/apache2/sites-available/000-default.conf
COPY apache2.conf /etc/apache2/apache2.conf
COPY php.ini /usr/local/etc/php/conf.d/php.ini
RUN a2enmod rewrite
#RUN a2dismod -f deflate

WORKDIR /var/www/phpunit

