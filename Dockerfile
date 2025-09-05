FROM php:8.2-fpm

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    libzip-dev \
    npm \
    nodejs

# Installer les extensions PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copier le projet dans le conteneur
COPY . .

# Installer les dépendances Laravel
RUN composer install --optimize-autoloader --no-dev

# Compiler les assets avec Vite
RUN npm install && npm run build

# Donner les bons droits
RUN chown -R www-data:www-data /var/www

# Exposer le port utilisé par Artisan serve
EXPOSE 8000

# Lancer le serveur Laravel
CMD php artisan serve --host=0.0.0.0 --port=8000
