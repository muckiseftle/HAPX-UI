# HAPX-UI - HAProxy Management Interface

Ein modernes Web-Interface zur Verwaltung von HAProxy-Instanzen, inklusive automatischer Let's Encrypt Zertifikatsverwaltung (DNS & HTTP Challenge).

## Features

- **Proxy-Verwaltung:** HTTP & TCP (SSL Passthrough) Hosts einfach konfigurieren.
- **SSL Automatisierung:** Let's Encrypt Zertifikate via HTTP-Challenge oder DNS-API (Cloudflare, Hetzner, do.de, etc.).
- **Sicherheit:** Passwortgeschützter Login & Zwei-Faktor-Authentifizierung (OTP).
- **Monitoring:** Live-Statistiken und Performance-Metriken.
- **Docker Ready:** Einfache Installation via Docker Compose.

## Schnellstart (Docker)

### 1. Voraussetzungen
Stellen Sie sicher, dass `docker` und `docker-compose` installiert sind.

### 2. Installation
Klonen Sie das Repository und starten Sie die Container:

```bash
docker-compose up -d --build
```

### 3. Zugang
Das Web-Interface ist standardmäßig erreichbar unter:
`http://DEINE-IP:8000`

**Standard-Login:**
- **Benutzer:** `admin@localhost`
- **Passwort:** `admin123`

*Hinweis: Bitte ändern Sie das Passwort nach dem ersten Login unter "Profil".*

## Manuelle Installation (Ubuntu/Debian)

1. PHP 8.3+, Nginx und HAProxy installieren.
2. Repository klonen.
3. `composer install` ausführen.
4. `.env` Datei konfigurieren.
5. `php artisan migrate` ausführen.
6. `php artisan make:admin admin@localhost admin123`

## HAProxy Konfiguration
Die Anwendung verwaltet einen spezifischen Block in der `haproxy.cfg`, der durch Marker gekennzeichnet ist:
```haproxy
# BEGIN HAPX-UI-MANAGED
...
# END HAPX-UI-MANAGED
```

## Lizenz
MIT
