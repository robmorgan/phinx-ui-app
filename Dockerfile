FROM php:8.1-alpine

RUN apk --update add wget \
  curl \
  git \
  php81 \
  php81-curl \
  php81-openssl \
  php81-iconv \
  php81-json \
  php81-mbstring \
  php81-phar \
  && rm /var/cache/apk/*

RUN docker-php-ext-install pdo pdo_mysql mysqli

# install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# Create app directory
WORKDIR /var/www

COPY composer.json ./
COPY composer.lock ./
RUN composer install

COPY . .

EXPOSE 8080
CMD [ "php", "-S", "0.0.0.0:8080", "-t", "public" ]
