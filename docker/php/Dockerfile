# php7-fpm/Dockerfile
FROM php:5.6-apache

ENV COMPOSER_HOME=/usr/local/composer
ADD vhost.conf /etc/apache2/sites-enabled/vhost.conf

RUN apt-get update && apt-get install -y \
    git \
    unzip

RUN apt-get update && \
    apt-get install -y \
        php-pear libicu-dev curl git libmemcached-dev zlib1g-dev libncurses5-dev \
        libfreetype6-dev libjpeg62-turbo-dev libmcrypt-dev libpng12-dev libpq-dev \
        --no-install-recommends && \
    docker-php-ext-install pdo pdo_mysql zip mbstring iconv mcrypt intl bcmath && \
    a2enmod rewrite && \
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php && \
    php -r "unlink('composer-setup.php');" && \
    mv composer.phar /usr/local/bin/composer && \
    composer global require symfony/var-dumper

RUN rm -f /etc/apache2/sites-enabled/000-default.conf
RUN echo 'alias sf="php app/console"' >> ~/.bashrc

ADD wait-for-it.sh /usr/local/bin/wait-for-it

ADD php.ini /usr/local/etc/php/php.ini

COPY entrypoint.sh /usr/local/bin/entrypoint.sh


WORKDIR /srv
CMD ["apache2-foreground", "-DFOREGROUND"]

