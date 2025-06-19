# Use the official PHP image with Apache
FROM php:8.2-apache

# Install common PHP extensions and tools
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    unzip \
    curl \
    git \
    && docker-php-ext-install curl

# Enable Apache mod_rewrite (common for PHP apps)
RUN a2enmod rewrite

# Set working directory inside container
WORKDIR /var/www/html

# Copy the project files into the container
COPY . /var/www/html

# Set appropriate permissions
RUN chown -R www-data:www-data /var/www/html

# Expose default Apache port
EXPOSE 80

# Start Apache when the container launches
CMD ["apache2-foreground"]
