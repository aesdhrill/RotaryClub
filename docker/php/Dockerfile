FROM php:8.1-fpm

ENV TZ=Europe/Warsaw

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        zlib1g-dev \
        libxml2-dev \
        libzip-dev \
        libpq-dev \
        cron \
        htop \
        supervisor \
        wget \
        rsync

RUN pecl install pcov

RUN docker-php-ext-install \
    zip \
    intl opcache \
    pdo pdo_pgsql

RUN docker-php-ext-enable \
    pcov

RUN apt-get install -y \
    gdebi

RUN wget https://github.com/wkhtmltopdf/packaging/releases/download/0.12.6-1/wkhtmltox_0.12.6-1.stretch_amd64.deb \
    && gdebi -n wkhtmltox_0.12.6-1.stretch_amd64.deb

COPY conf.d/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY conf.d/php.ini /usr/local/etc/php/conf.d/php.ini

RUN curl -sL https://deb.nodesource.com/setup_14.x | bash - \
      && apt-get install -y nodejs \
      && curl -L https://www.npmjs.com/install.sh | sh

RUN npm install -g yarn

RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

RUN curl -sS https://get.symfony.com/cli/installer | bash && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

#COPY cron/daily /etc/cron.d/daily
#RUN chmod 0644 /etc/cron.d/daily && crontab /etc/cron.d/daily

WORKDIR /var/www/symfony/
