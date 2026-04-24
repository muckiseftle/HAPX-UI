# HAPX-UI - HAProxy Management Interface

Ein modernes Web-Interface zur Verwaltung von HAProxy-Instanzen, inklusive automatischer Let's Encrypt Zertifikatsverwaltung (DNS & HTTP Challenge).

## 🚀 All-In-One Installation (Docker)

Kopieren Sie diesen Befehl und führen Sie ihn auf Ihrer VM aus:

```bash
git clone https://github.com/muckiseftle/HAPX-UI.git && \
cd HAPX-UI && \
docker-compose up -d --build && \
echo -e "\n\033[1;32m✅ HAPX-UI wurde erfolgreich installiert!\033[0m\n" && \
echo -e "\033[1;34m🌐 URL:\033[0m http://\$(curl -s https://ifconfig.me):8000" && \
echo -e "\033[1;34m👤 User:\033[0m admin@localhost" && \
echo -e "\033[1;34m🔑 Pass:\033[0m admin123\n" && \
echo -e "\033[0;33m⚠️  Bitte ändern Sie das Passwort nach dem ersten Login unter 'Profil'.\033[0m\n"
```

---

## Features

- **Proxy-Verwaltung:** HTTP & TCP (SSL Passthrough) Hosts einfach konfigurieren.
- **SSL Automatisierung:** Let's Encrypt Zertifikate via HTTP-Challenge oder DNS-API (Cloudflare, Hetzner, do.de, etc.).
- **Sicherheit:** Passwortgeschützter Login & Zwei-Faktor-Authentifizierung (OTP).
- **Monitoring:** Live-Statistiken und Performance-Metriken.
- **Docker Ready:** Vollständige Isolierung und einfaches Reloading.

## Zugang & Standard-Login
- **URL:** `http://DEINE-IP:8000`
- **Benutzer:** `admin@localhost`
- **Passwort:** `admin123`

## HAProxy Konfiguration
Die Anwendung verwaltet einen spezifischen Block in der `haproxy.cfg`, der durch Marker gekennzeichnet ist:
```haproxy
# BEGIN HAPX-UI-MANAGED
...
# END HAPX-UI-MANAGED
```

## Lizenz
MIT
