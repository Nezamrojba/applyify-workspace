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

# Run migrations, seed data, then start the application server
CMD php artisan migrate --force \
    && php artisan db:seed --force \
    && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}

