FROM php:8.3-fpm
ARG WORKDIR
WORKDIR ${WORKDIR}

RUN apt-get -y update && apt-get -y install git unzip rsync curl && \
    docker-php-ext-install pdo pdo_mysql && \
    pecl install xdebug && docker-php-ext-enable xdebug;

COPY ./docker/configs/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

ARG PUID
ARG PGID
RUN groupadd -g ${PGID} app && \
    useradd -u ${PUID} -g ${PGID} -m app

USER app

CMD php-fpm
EXPOSE 9000