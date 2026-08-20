FROM node:22-alpine AS assets

WORKDIR /app

COPY package*.json ./
RUN npm install

COPY resources ./resources
COPY public ./public
COPY app ./app
COPY templates ./templates
RUN npm run tailwind:build


FROM php:8.4-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        poppler-utils \
        libreoffice-writer \
        libreoffice-calc \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo_mysql \
    && a2enmod rewrite headers

COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php/uploads.ini /usr/local/etc/php/conf.d/99-uploads.ini

WORKDIR /var/www/html

COPY . /var/www/html
COPY --from=assets /app/public/assets/css/app.css /var/www/html/public/assets/css/app.css

RUN mkdir -p \
        /var/www/html/storage/documents \
        /var/www/html/storage/logs \
    && chown -R www-data:www-data /var/www/html/storage
