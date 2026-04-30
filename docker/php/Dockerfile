FROM php:8.4-fpm-bookworm
ARG TIMEZONE=Europe/Moscow
ARG UID=1000
ARG GID=1000

COPY php.ini /usr/local/etc/php/conf.d/docker-php-config.ini

RUN apt-get update && apt-get install -y \
    gnupg \
    g++ \
    procps \
    openssl \
    git \
    unzip \
    zlib1g-dev \
    libzip-dev \
    libfreetype6-dev \
    libpng-dev \
    libjpeg-dev \
    libicu-dev  \
    libonig-dev \
    libxslt1-dev \
    acl \
    whois \
    && echo 'alias sf="php bin/console"' >> ~/.bashrc

RUN docker-php-ext-configure gd --with-jpeg --with-freetype

RUN apt-get update && apt-get install -y --no-install-recommends ntp

RUN apt-get install -y libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql

RUN pecl install xdebug && docker-php-ext-enable xdebug

RUN docker-php-ext-install intl

RUN apt-get update && apt-get install -y librabbitmq-dev && \
    pecl install amqp && \
    docker-php-ext-enable amqp

# Set timezone
RUN ln -snf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime && echo ${TIMEZONE} > /etc/timezone \
    && printf '[PHP]\ndate.timezone = "%s"\n', ${TIMEZONE} > /usr/local/etc/php/conf.d/tzone.ini \
    && "date"

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN addgroup -gid ${GID} www
RUN adduser www \
	-uid ${UID} \
	--disabled-login \
	--ingroup www \
	--no-create-home \
	--quiet \
	--system

WORKDIR /var/www/symfony