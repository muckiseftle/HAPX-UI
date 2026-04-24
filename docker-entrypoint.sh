#!/bin/bash
set -e

# Verzeichnisse sicherstellen
mkdir -p /etc/haproxy/certs
chown -R www-data:www-data /etc/haproxy

# Datenbank initialisieren
if [ ! -f /var/www/html/database/database.sqlite ]; then
    touch /var/www/html/database/database.sqlite
    chown www-data:www-data /var/www/html/database/database.sqlite
fi

# Migrationen und Cache
php artisan migrate --force
php artisan config:cache
php artisan route:cache

# Standard-Admin erstellen (falls nicht vorhanden)
php artisan make:admin admin@localhost admin123

exec "$@"
