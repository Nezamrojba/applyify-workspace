FROM php:8.3-cli

# Install system dependencies and PHP extensions required by Laravel
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libzip-dev libpng-dev \
    && docker-php-ext-install pdo_mysql pdo_pgsql zip bcmath \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy backend source
COPY . .

# Install PHP dependencies (further optimisation can run at runtime once env vars exist)
RUN composer install --no-dev --optimize-autoloader

# Ensure startup script is executable
RUN chmod +x docker/startup.sh

# Run startup script that waits for the database, migrates, seeds, links storage, and starts the server
CMD ["./docker/startup.sh"]

