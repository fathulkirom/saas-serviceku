# ============================================
# ServiceKU - Production Dockerfile
# ============================================
# Multi-stage build:
#   Stage 1: build frontend assets (Vite/Node)
#   Stage 2: aplikasi PHP-FPM + Nginx (serversideup/php:8.4)
#
# Image dasar sama dengan docker-compose.yml (serversideup/php:8.4-fpm-nginx),
# yang sudah menyediakan PHP-FPM + Nginx + composer untuk Laravel.
# Aplikasi disajikan dari /var/www/html/public pada port 8080.

# ============================================
# Stage 1: Frontend Build (Vite)
# ============================================
FROM node:20-alpine AS frontend
WORKDIR /app

# Cache layer untuk dependency
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

# Build aset frontend
COPY . .
RUN npm run build

# ============================================
# Stage 2: Aplikasi (PHP-FPM + Nginx)
# ============================================
FROM serversideup/php:8.4-fpm-nginx

# Install extension PHP yang dibutuhkan ServiceKU
USER root
RUN install-php-extensions bcmath gd intl pdo_mysql exif zip opcache
USER www-data

WORKDIR /var/www/html

# Salin composer files dulu untuk layer caching yang lebih baik
COPY --chown=www-data:www-data composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# Salin source aplikasi (vendor & node_modules di-exclude lewat .dockerignore)
COPY --chown=www-data:www-data . .

# Salin frontend build dari Stage 1
COPY --chown=www-data:www-data --from=frontend /app/public/build ./public/build

# Generate autoload + discover package
RUN composer dump-autoload --optimize

# Permission storage & bootstrap cache
RUN chown -R www-data:www-data storage bootstrap/cache

# Image serversideup menyajikan /var/www/html/public pada port 8080
EXPOSE 8080
