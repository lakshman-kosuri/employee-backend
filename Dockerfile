FROM php:8.2-apache

# ✅ Fix MPM conflict (IMPORTANT)
RUN a2dismod mpm_event || true
RUN a2dismod mpm_worker || true
RUN a2enmod mpm_prefork

# PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Apache config
RUN a2enmod rewrite

# Copy files
COPY . /var/www/html/

# Permissions
RUN chown -R www-data:www-data /var/www/html