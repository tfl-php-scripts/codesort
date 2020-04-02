FROM php:7.4-apache

MAINTAINER Robotess.net

WORKDIR /app

RUN apt-get update && \
    apt-get install -y libfreetype6-dev libjpeg62-turbo-dev && \
    docker-php-ext-install pdo pdo_mysql mysqli && \
    docker-php-ext-configure gd --with-freetype --with-jpeg  &&  \
    docker-php-ext-install gd

# mailhog
RUN apt-get update &&\
    apt-get install --no-install-recommends --assume-yes --quiet ca-certificates curl git &&\
    rm -rf /var/lib/apt/lists/*
RUN curl -Lsf 'https://storage.googleapis.com/golang/go1.8.3.linux-amd64.tar.gz' | tar -C '/usr/local' -xvzf -
ENV PATH /usr/local/go/bin:$PATH
RUN go get github.com/mailhog/mhsendmail
RUN cp /root/go/bin/mhsendmail /usr/bin/mhsendmail

RUN touch /usr/local/etc/php/php.ini
RUN echo 'sendmail_path = /usr/bin/mhsendmail --smtp-addr mailhog:1025' >> /usr/local/etc/php/php.ini
RUN echo 'display_errors = on' >> /usr/local/etc/php/php.ini \
    && echo 'log_errors = off' >> /usr/local/etc/php/php.ini

RUN chown -R www-data:www-data /app
RUN mkdir -p app/samples/images
RUN chmod -R 777 app/samples/images
RUN a2enmod rewrite
RUN service apache2 restart
