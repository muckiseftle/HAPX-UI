FROM php:8.3-fpm-alpine

# System-Abhängigkeiten installieren
RUN apk add --no-cache \
    bash \
    curl \
    git \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    openssl \
    socat \
    sudo \
    nginx \
    haproxy \
    supervisor \
    sqlite \
    icu-dev \
    nodejs \
    npm

# PHP-Erweiterungen installieren
RUN docker-php-ext-install pdo pdo_sqlite gd bcmath intl

# Composer installieren
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# acme.sh installieren
RUN curl https://get.acme.sh | sh -s email=admin@localhost \
    && ln -s /root/.acme.sh/acme.sh /usr/local/bin/acme.sh

# Arbeitsverzeichnis
WORKDIR /var/www/html

# Projektdateien kopieren
COPY . .

# Berechtigungen für Laravel setzen
RUN chown -R www-data:www-data storage bootstrap/cache

# Composer Abhängigkeiten
RUN composer install --no-dev --optimize-autoloader

# Frontend Abhängigkeiten und Build
RUN npm install && npm run build

# Nginx Konfiguration kopieren
COPY docker/nginx/nginx-all-in-one.conf /etc/nginx/http.d/default.conf

# Supervisor Konfiguration kopieren
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf

# Cron für Laravel Scheduler einrichten
RUN echo "* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1" > /etc/crontabs/root

# HAProxy Verzeichnisse
RUN mkdir -p /etc/haproxy/certs /run/haproxy \
    && chown -R www-data:www-data /etc/haproxy /run/haproxy

# Startup Skript
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Ports: HAProxy (80, 443), Nginx UI (8080)
EXPOSE 80 443 8080

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
