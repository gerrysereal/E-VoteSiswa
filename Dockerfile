FROM php:8.1-apache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install required PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends libzip-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql zip \
    && docker-php-ext-enable mysqli zip \
    && docker-php-ext-enable opcache \
    && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /var/www/html

# Copy all project files into the Apache document root
COPY . /var/www/html/

# Set proper permissions for CodeIgniter cache and logs
RUN chown -R www-data:www-data /var/www/html/application/cache && \
    chown -R www-data:www-data /var/www/html/application/logs && \
    chmod -R 755 /var/www/html/application/cache && \
    chmod -R 755 /var/www/html/application/logs && \
    chmod -R 755 /var/www/html/asset/file && \
    chmod -R 755 /var/www/html/application/third_party/fpdf && \
    chown -R www-data:www-data /var/www/html/application/third_party/fpdf

# Create uploads and session directories
RUN mkdir -p /var/www/html/uploads && \
    chown -R www-data:www-data /var/www/html/uploads && \
    chmod -R 755 /var/www/html/uploads && \
    mkdir -p /tmp/sessions && \
    chown -R www-data:www-data /tmp/sessions && \
    chmod 755 /tmp/sessions

# Configure Apache to allow .htaccess overrides
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Expose port 80
EXPOSE 80

CMD ["apache2-foreground"]
