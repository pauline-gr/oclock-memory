# Récupération de la config php7.4 apache
FROM php:7.4-apache

# Mises à jour et installations pour git composer et curl
RUN apt-get update && apt-get upgrade -y
RUN apt-get install -y git gnupg curl zip unzip vim nano nodejs npm
    
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# PHP Extensions
RUN docker-php-ext-install -j$(nproc) opcache pdo_mysql


RUN npm install --global yarn
RUN yarn config set ignore-engines true
RUN a2enmod rewrite

COPY ./composer.json /var/www/html/composer.json
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install
RUN yarn install
RUN yarn add sass-loader@^11.0.0 sass --dev
RUN yarn build
RUN php bin/console cache:clear --env=prod

RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*