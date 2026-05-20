FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite headers \
    && a2enconf security \
    && rm -rf /var/lib/apt/lists/*

COPY docker/apache-hardening.conf /etc/apache2/conf-available/raspi-hardening.conf
RUN a2enconf raspi-hardening

WORKDIR /var/www/html
COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

HEALTHCHECK --interval=30s --timeout=5s --retries=3 CMD php -r 'exit(@fsockopen("127.0.0.1", 80) ? 0 : 1);'
