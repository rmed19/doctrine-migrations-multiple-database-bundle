FROM php:7.2-alpine3.12

RUN mkdir /app
WORKDIR /app

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer