FROM php:8.4-apache

RUN apt-get update && apt-get install -y unzip git \
    && docker-php-ext-install sockets \
    && a2enmod rewrite \
    && sed -i 's|AllowOverride None|AllowOverride All|g' /etc/apache2/apache2.conf \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]