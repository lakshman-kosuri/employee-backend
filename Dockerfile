FROM php:8.2-apache

# PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Apache modules
RUN a2enmod rewrite

# ✅ ADD THIS LINE
RUN echo "PassEnv DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD" \
    > /etc/apache2/conf-enabled/passenv.conf

# Copy files
COPY . /var/www/html/

# Permissions
RUN chown -R www-data:www-data /var/www/html

# Copy start script
COPY start.sh /start.sh
RUN chmod +x /start.sh

CMD ["/start.sh"]