#!/bin/bash
set -e

# Verzeichnisse sicherstellen
mkdir -p /etc/haproxy/certs /run/haproxy
chown -R www-data:www-data /etc/haproxy /run/haproxy

# HAProxy Basis-Konfiguration erstellen, falls nicht vorhanden
if [ ! -f /etc/haproxy/haproxy.cfg ]; then
    echo "Creating default HAProxy config..."
    cat > /etc/haproxy/haproxy.cfg <<EOF
global
    log stdout format raw local0
    stats socket /run/haproxy/admin.sock mode 660 level admin user www-data
    maxconn 4096

defaults
    log global
    mode http
    option httplog
    timeout connect 5000ms
    timeout client 50000ms
    timeout server 50000ms

# BEGIN HAPX-UI-MANAGED
# END HAPX-UI-MANAGED
EOF
    chown www-data:www-data /etc/haproxy/haproxy.cfg
fi

# Datenbank initialisieren
if [ ! -f /var/www/html/database/database.sqlite ]; then
    echo "Creating SQLite database..."
    touch /var/www/html/database/database.sqlite
    chown www-data:www-data /var/www/html/database/database.sqlite
fi

# Migrationen und Cache
echo "Running migrations..."
php artisan migrate --force

# App Key generieren falls fehlt
if [ -z "$APP_KEY" ] && [ ! -f .env ]; then
    php artisan key:generate --show
fi

# Permissions fix (just in case)
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

exec "$@"
