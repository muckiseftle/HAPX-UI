FROM php:8.3-fpm-alpine

# System-Abhängigkeiten installieren
RUN alpine_version=$(cat /etc/alpine-release | cut -d'.' -f1,2) \
    && apk add --no-cache \
    bash \
    curl \
    git \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    openssl \
    socat \
    sudo

# PHP-Erweiterungen installieren
RUN docker-php-ext-install pdo pdo_sqlite gd bcmath

# Composer installieren
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# acme.sh installieren
RUN curl https://get.acme.sh | sh -s email=admin@localhost \
    && ln -s /root/.acme.sh/acme.sh /usr/local/bin/acme.sh

# Arbeitsverzeichnis
WORKDIR /var/www/html

# Projektdateien kopieren
COPY . .

# Berechtigungen setzen
RUN chown -R www-data:www-data storage bootstrap/cache

# Composer Abhängigkeiten
RUN composer install --no-dev --optimize-autoloader

# Startup Skript
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]
