FROM php:7.4-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite for Slim routing
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application code (including models, controllers, etc.)
COPY . .

# Set GitHub token for Composer
ARG GITHUB_TOKEN
RUN composer config --global github-oauth.github.com $GITHUB_TOKEN

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction


# Create necessary directories
RUN mkdir -p /var/www/html/uploads /var/www/html/logs /tmp/twig_cache

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/uploads \
    && chmod -R 777 /var/www/html/logs \
    && chmod -R 777 /tmp/twig_cache

# Apache configuration for Slim
RUN echo '<Directory /var/www/html/public>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/slim.conf \
    && a2enconf slim

# Expose port 80
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]