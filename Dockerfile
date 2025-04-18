FROM php:8.1-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install zip

# Enable Apache modules
RUN a2enmod rewrite

# Set the working directory in the container
WORKDIR /var/www/html/translation-game

# Copy the PHP files to the container
COPY . /var/www/html/translation-game/

# Set permissions
RUN chown -R www-data:www-data /var/www/html/translation-game \
    && chmod -R 755 /var/www/html/translation-game

# Configure Apache to serve from the translation-game directory
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html\n\
    <Directory /var/www/html>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
        # CORS Headers\n\
        # Header always set Access-Control-Allow-Origin "*"\n\
        # Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"\n\
        # Header always set Access-Control-Allow-Headers "Content-Type, Authorization"\n\
        # Header always set Access-Control-Allow-Credentials "true"\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Enable headers module
# RUN a2enmod headers

# Expose port 80
EXPOSE 80

# Start Apache in the foreground
CMD ["apache2-foreground"]