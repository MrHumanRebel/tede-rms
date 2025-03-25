# syntax=docker/dockerfile:1

################################################################################
# Composer Deps Stage
################################################################################

FROM composer:lts AS deps

WORKDIR /app

# Composer telepítés a cache-elés optimalizálásával
RUN --mount=type=bind,source=composer.json,target=composer.json \
    --mount=type=bind,source=composer.lock,target=composer.lock \
    --mount=type=cache,target=/tmp/cache \
    composer install --no-dev --no-interaction --ignore-platform-reqs

################################################################################
# PHP Build Stage
################################################################################

# Használj Alpine alapú PHP képet, Apache telepítéssel
FROM php:8.3-alpine AS final

LABEL org.opencontainers.image.source=https://github.com/adam-rms/adam-rms
LABEL org.opencontainers.image.documentation=https://adam-rms.com/self-hosting
LABEL org.opencontainers.image.url=https://adam-rms.com
LABEL org.opencontainers.image.vendor="Bithell Studios Ltd."
LABEL org.opencontainers.image.description="TeDeRMS is a free, open source advanced Rental Management System for Theatre, AV & Broadcast. This image is a PHP Apache2 docker container, which exposes TeDeRMS on port 80."
LABEL org.opencontainers.image.licenses=AGPL-3.0

# Alapcsomagok telepítése Apache és PHP szükséges kiterjesztésekkel
RUN apk update && apk add --no-cache \
    apache2 \
    apache2-utils \
    libicu \
    libzip \
    libpng \
    bash \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql mysqli intl zip \
    && rm -rf /var/cache/apk/*  # Eltávolítjuk az apk cache-t, hogy csökkentsük a méretet

# Apache konfiguráció beállítása, ha szükséges
RUN sed -ri -e 's!/var/www/html!/var/www/html/src!g' /etc/apache2/sites-available/*.conf

# A szükséges PHP beállítások
RUN echo "\npost_max_size=64M\n" >> "$PHP_INI_DIR/php.ini" \
    && echo "memory_limit=256M\n" >> "$PHP_INI_DIR/php.ini" \
    && echo "max_execution_time=600\n" >> "$PHP_INI_DIR/php.ini" \
    && echo "sys_temp_dir=/tmp\n" >> "$PHP_INI_DIR/php.ini" \
    && echo "upload_max_filesize=64M\n" >> "$PHP_INI_DIR/php.ini"

# A szükséges fájlok másolása a megfelelő helyekre
COPY --from=deps /app/vendor/ /var/www/html/vendor
COPY ./src /var/www/html/src
COPY ./db /var/www/html/db
COPY ./phinx.php /var/www/html
COPY ./migrate.sh /var/www/html
RUN chmod +x /var/www/html/migrate.sh

# Apache szerver indítása
RUN mkdir -p /run/apache2 && chown -R www-data:www-data /run/apache2

# Az alkalmazás futtatásához szükséges felhasználó
USER www-data

# Web server indítása Apache konfigurációval
CMD ["httpd", "-D", "FOREGROUND"]
