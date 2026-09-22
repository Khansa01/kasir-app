FROM php:8.2-apache

# Enable mod_rewrite
RUN a2enmod rewrite

# Install MySQL extension
RUN docker-php-ext-install pdo pdo_mysql

# Copy semua file project ke Apache
COPY . /var/www/html/

# Set permission
RUN chown -R www-data:www-data /var/www/html

# Apache config
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf