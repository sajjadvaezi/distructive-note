FROM php:8.2-apache

# System deps (libsodium for ext/sodium, zip tools, etc.)
RUN apt-get update && apt-get install -y --no-install-recommends \
    libsodium-dev \
    libzip-dev \
    unzip \
    git \
  && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-install pdo_mysql sodium opcache

# Enable Apache modules and allow .htaccess overrides
RUN a2enmod rewrite headers && \
    sed -ri 's#<Directory /var/www/>#<Directory /var/www/>#; s#AllowOverride None#AllowOverride All#' /etc/apache2/apache2.conf

# Workdir + copy app source
WORKDIR /var/www/html
COPY ./public /var/www/html
COPY ./src /var/www/src

# Composer (optional but handy)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Permissions
RUN chown -R www-data:www-data /var/www

EXPOSE 80
