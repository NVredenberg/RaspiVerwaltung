# Raspi-Verwaltung

Interne Inventarverwaltung für Raspberrys, Arduino-Koffer, PC-Teile, Zubehör, Sets und Ausleihen.

## Betrieb auf dem Pi-hole-System

Die Anwendung ist für Docker vorbereitet und belegt standardmäßig **nicht** Port 80. Damit bleibt Pi-hole unter `172.16.76.162` weiterhin erreichbar.

- App: `http://172.16.76.162:8080`
- Pi-hole: bleibt auf den bestehenden Pi-hole-Ports erreichbar
- Datenbank: nur im internen Docker-Netz, kein veröffentlichter Port

## Start

1. `.env` prüfen und sichere Passwörter setzen.
2. Container bauen und starten:

```bash
docker compose up -d --build
```

3. App öffnen:

```text
http://172.16.76.162:8080
```

Beim ersten Login gibt es noch keine Konten. Die Login-Maske legt dann automatisch das erste Admin-Konto an.

Weitere Nutzer verwenden `Registrieren` auf der Login-Seite. Diese Konten bleiben gesperrt, bis ein Admin sie unter `Admin` freigibt.

## Konfiguration

Die Anwendung liest die Datenbankkonfiguration aus Umgebungsvariablen:

- `APP_BIND_IP=172.16.76.162`
- `APP_PORT=8080`
- `DB_HOST=db`
- `DB_PORT=3306`
- `DB_NAME=raspi`
- `DB_USER=raspiVer`
- `DB_PASSWORD=...`
- `DB_ROOT_PASSWORD=...`

`.env` ist absichtlich in `.gitignore` und wird nicht versioniert. `.env.example` dient als Vorlage.

## Migration bestehender Datenbanken

Neue Installationen verwenden automatisch `db/migration.sql`. Für bestehende Datenbanken einmalig ausführen:

```bash
docker compose exec -T db sh -c 'mariadb -u root -p"$MARIADB_ROOT_PASSWORD" raspi' < db/upgrade_2026_05_inventory.sql
```

Das Root-Passwort steht lokal in `.env` als `DB_ROOT_PASSWORD`.

## Inventarmodell

Inventar und Sets sind nicht mehr fest auf Oberstufe/Mittelstufe ausgelegt.

- Inventarkategorien: Raspberry, Arduino-Koffer, PC-Teile, Sonstiges
- Sets/Koffer haben eine eigene `Bezeichnung`
- Zielgruppe/Klasse ist ein freies Feld, z. B. `8A`, `EF Informatik`, `Werkstatt`
- Ansprechpartner und Beschreibung sind optional

## Sicherheitsmaßnahmen

- Keine festen Datenbankpasswörter mehr im PHP-Code
- Der erste Login erzeugt das initiale Admin-Konto
- Neue Konten registrieren sich selbst und bleiben bis zur Admin-Freigabe gesperrt
- Admin-Panel für manuelle Freigabe oder Sperrung von Konten
- Rollenmodell: `admin` und `user`
- Login-Sessions mit `HttpOnly`, `SameSite=Strict` und Session-ID-Wechsel beim Login
- AJAX-Endpunkte verlangen Login und CSRF-Token
- Eingaben werden serverseitig validiert
- Datenbankfehler werden nicht mehr direkt an die Oberfläche ausgegeben
- Sicherheitsheader inklusive CSP mit Script-Nonce sind aktiv
- Änderungsprotokoll zeigt, welcher Nutzer Bestände, Sets und Ausleihen geändert hat
- Ausleihe und Rückgabe laufen in Transaktionen
- Bestände können bei Ausleihe nicht unter 0 fallen
- Löschen von Einträgen mit Ausleihhistorie wird blockiert
- Docker-App bindet nur an `172.16.76.162:${APP_PORT}`
- MariaDB ist nicht nach außen veröffentlicht
- Container haben Ressourcenlimits, `no-new-privileges`, reduzierte Capabilities und ein read-only App-Dateisystem

## Wichtige Betriebsregeln

- Nicht direkt aus dem Internet veröffentlichen.
- `.env` nicht committen.
- Regelmäßige Datenbank-Backups einrichten.
- Pi-hole und diese App getrennt überwachen, weil beide auf derselben Hardware laufen.
- Vor Updates ein Backup des Docker-Volumes `db_data` erstellen.
