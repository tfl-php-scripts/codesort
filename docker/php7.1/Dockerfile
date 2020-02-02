FROM php:7.1-apache

MAINTAINER Robotess.net

WORKDIR /app
RUN docker-php-ext-install pdo pdo_mysql
RUN chown www-data:www-data -R /app
RUN a2enmod rewrite
RUN service apache2 restart
