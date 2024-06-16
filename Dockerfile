# syntax=docker/dockerfile:1.7-labs
FROM composer as backend

RUN apk add --no-cache git

COPY composer.json composer.json
COPY composer.lock composer.lock

RUN composer install --no-scripts --no-dev --no-interaction


FROM node:18-alpine as frontend

WORKDIR /app
RUN apk add --no-cache git

COPY ./resources/js /app/resources/js
COPY ./resources/css /app/resources/css

COPY ./package.json /app/
COPY ./vite.config.js /app/

RUN npm i && npm run build

FROM serversideup/php:8.3-fpm-nginx-alpine-v3.0.0
LABEL authors="Delivery Trans"

RUN mkdir --parents ~/.postgresql && \
    wget "https://storage.yandexcloud.net/cloud-certs/CA.pem" \
         --output-document ~/.postgresql/root.crt && \
    chmod 0600 ~/.postgresql/root.crt

WORKDIR /var/www/html
ENV PHP_OPCACHE_ENABLE 1
ENV AUTORUN_ENABLED true

COPY --chown=www-data:www-data --exclude=./resources/js --exclude=*.config.js --exclude=package.json --exclude=composer.json --exclude=composer.lock . /var/www/html/.
COPY --chown=www-data:www-data --from=backend /app/vendor /var/www/html/vendor
COPY --chown=www-data:www-data --from=frontend /app/public/build /var/www/html/public/build
