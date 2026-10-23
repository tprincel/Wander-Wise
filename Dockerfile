FROM php:8.2-apache

# Enable Apache rewrite module
RUN a2enmod rewrite

# Install common PHP extensions for MySQL/PDO
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Copy application files to Apache root
COPY . /var/www/html/

# Expose standard Apache HTTP port
EXPOSE 80

# Start Apache in the foreground
CMD ["apache2-foreground"]
