FROM php:7-alpine

RUN apk --update add wget \
  curl \
  git \
  php7 \
  php7-curl \
  php7-openssl \
  php7-iconv \
  php7-json \
  php7-mbstring \
  php7-phar \
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
