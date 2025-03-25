# syntax=docker/dockerfile:1

################################################################################
# Composer Deps Stage
################################################################################
FROM composer:lts AS deps

WORKDIR /app

# Use bind mounts for composer files and a cache mount for downloaded packages.
RUN --mount=type=bind,source=composer.json,target=composer.json \
    --mount=type=bind,source=composer.lock,target=composer.lock \
    --mount=type=cache,target=/tmp/cache \
    composer install --no-dev --no-interaction --ignore-platform-reqs

################################################################################
# PHP Build Stage
################################################################################
FROM php:8.3-apache AS final

LABEL org.opencontainers.image.source="https://github.com/mrhumanrebel/tede-rms" \
      org.opencontainers.image.documentation="https://szekedani.duckdns.org" \
      org.opencontainers.image.url="https://szekedani.duckdns.org" \
      org.opencontainers.image.vendor="Danci" \
      org.opencontainers.image.description="TeDeRMS is a free, open source advanced Rental Management System for Theatre, AV & Broadcast. This image is a PHP Apache2 docker container, which exposes TeDeRMS on port 80." \
      org.opencontainers.image.licenses="AGPL-3.0"

# Install PHP extensions and clean up in a single RUN command.
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
      libicu-dev \
      libzip-dev \
      libpng-dev && \
    docker-php-ext-install -j"$(nproc)" gd pdo pdo_mysql mysqli intl zip && \
    apt-get purge -y --auto-remove && \
    rm -rf /var/lib/apt/lists/*

# Append php.ini settings in one layer.
RUN printf "\npost_max_size=64M\nmemory_limit=256M\nmax_execution_time=600\nsys_temp_dir=/tmp\nupload_max_filesize=64M\n" >> "$PHP_INI_DIR/php.ini"

# Change Apache's DocumentRoot to /var/www/html/src.
RUN sed -ri -e 's!/var/www/html!/var/www/html/src!g' /etc/apache2/sites-available/*.conf

# Copy dependencies and application files.
COPY --from=deps /app/vendor/ /var/www/html/vendor
COPY ./src /var/www/html/src
COPY ./db /var/www/html/db
COPY ./phinx.php /var/www/html
COPY ./migrate.sh /var/www/html
RUN chmod +x /var/www/html/migrate.sh

# Switch to non-privileged user.
USER www-data

SHELL ["sh"]
ENTRYPOINT ["/var/www/html/migrate.sh"]
