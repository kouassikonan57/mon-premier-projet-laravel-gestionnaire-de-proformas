FROM php:8.2-fpm

# Installer les dépendances nécessaires
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libxml2-dev zip unzip libzip-dev \
    libmcrypt-dev mariadb-client nodejs npm

# Installer les extensions PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Créer et utiliser le répertoire de travail
WORKDIR /var/www

# Copier le projet dans le conteneur
COPY . .

# Installer les dépendances PHP et JS
RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build

# Donner les bons droits
RUN chown -R www-data:www-data /var/www/storage

# Exposer le port de Laravel
EXPOSE 8000

# Lancer le serveur Laravel
CMD php artisan serve --host=0.0.0.0 --port=8000

RUN chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache
