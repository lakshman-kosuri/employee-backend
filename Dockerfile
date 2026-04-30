FROM php:8.2-apache

# PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Apache modules
RUN a2enmod rewrite

# Copy files
COPY . /var/www/html/

# Permissions
RUN chown -R www-data:www-data /var/www/html

# Copy start script
COPY start.sh /start.sh
RUN chmod +x /start.sh

# 🔥 Use custom start command
CMD ["/start.sh"]