# Netzwerk-Deployment

Diese Anleitung beschreibt das Deployment der Raspi-Verwaltung auf den Pi-hole-Host im lokalen Netzwerk.

Ziel:

- Pi-hole bleibt unter `172.16.76.162` erreichbar.
- Die Inventarverwaltung läuft unter `http://172.16.76.162:8080`.
- MariaDB bleibt nur im internen Docker-Netz erreichbar.
- Versionierung und Updates laufen über Git.
- `.env` bleibt ausschließlich auf dem Zielsystem.

## Voraussetzungen

Auf dem Pi-hole-Host:

- SSH-Zugriff auf `172.16.76.162`
- Git installiert
- Docker und Docker Compose installiert
- Ein Benutzer mit Docker-Rechten, z. B. `pi` oder `deploy`

Auf dem Entwicklungsrechner:

- Git installiert
- SSH-Zugriff auf den Pi
- Lokaler Projektstand ist committed

Vor jedem Deployment lokal prüfen:

```bash
git status
git log --oneline -5
```

## Empfohlene Struktur auf dem Pi

```text
/opt/raspi-verwaltung
├── docker-compose.yml
├── .env
├── db/
├── backups/
└── ...
```

Einmalig auf dem Pi anlegen:

```bash
sudo mkdir -p /opt/raspi-verwaltung
sudo chown "$USER:$USER" /opt/raspi-verwaltung
```

## Variante A: Git-Remote auf dem Pi

Diese Variante ist sinnvoll, wenn kein externer Git-Server genutzt werden soll.

### 1. Bare-Repository auf dem Pi anlegen

Auf dem Pi:

```bash
mkdir -p ~/git
git init --bare ~/git/raspi-verwaltung.git
```

### 2. Pi als Remote eintragen

Auf dem Entwicklungsrechner:

```bash
git remote add pi ssh://pi@172.16.76.162/home/pi/git/raspi-verwaltung.git
git push pi main
```

Falls der SSH-Benutzer nicht `pi` heißt, den Benutzernamen entsprechend ersetzen.

### 3. Arbeitskopie auf dem Pi klonen

Auf dem Pi:

```bash
git clone ~/git/raspi-verwaltung.git /opt/raspi-verwaltung
cd /opt/raspi-verwaltung
```

## Variante B: GitHub/GitLab/anderer Git-Server

Auf dem Pi:

```bash
git clone <repo-url> /opt/raspi-verwaltung
cd /opt/raspi-verwaltung
```

Danach laufen Updates später über:

```bash
git pull --ff-only
```

## `.env` auf dem Pi erstellen

Auf dem Pi:

```bash
cd /opt/raspi-verwaltung
cp .env.example .env
nano .env
chmod 600 .env
```

Wichtige Werte:

```env
APP_BIND_IP=172.16.76.162
APP_PORT=8080
DB_HOST=db
DB_PORT=3306
DB_NAME=raspi
DB_USER=raspiVer
DB_PASSWORD=<sicheres-passwort>
DB_ROOT_PASSWORD=<sicheres-root-passwort>
```

Wichtig: `.env` wird nicht per Git übertragen und darf nicht committed werden.

## Erstes Deployment

Auf dem Pi:

```bash
cd /opt/raspi-verwaltung
docker compose up -d --build
docker compose ps
```

Danach öffnen:

```text
http://172.16.76.162:8080
```

Beim ersten Login wird das initiale Admin-Konto angelegt. Danach registrieren sich weitere Nutzer selbst und werden im Admin-Panel freigegeben.

## Update-Deployment

### 1. Lokal Änderungen committen und pushen

Auf dem Entwicklungsrechner:

```bash
git status
git add .
git commit -m "Deployment-Stand"
git push pi main
```

Bei GitHub/GitLab entsprechend zum dortigen Remote pushen.

### 2. Backup auf dem Pi erstellen

Auf dem Pi:

```bash
cd /opt/raspi-verwaltung
mkdir -p backups
docker compose exec -T db sh -c 'mariadb-dump -u root -p"$MARIADB_ROOT_PASSWORD" "$MARIADB_DATABASE"' > "backups/raspi_$(date +%F_%H%M).sql"
```

### 3. Code aktualisieren

Auf dem Pi:

```bash
cd /opt/raspi-verwaltung
git pull --ff-only
```

### 4. Datenbankmigration ausführen

Nur ausführen, wenn die neue Version eine Migration enthält oder sich das Datenmodell geändert hat:

```bash
docker compose exec -T db sh -c 'mariadb -u root -p"$MARIADB_ROOT_PASSWORD" "$MARIADB_DATABASE"' < db/upgrade_2026_05_inventory.sql
```

### 5. Container neu bauen und starten

```bash
docker compose up -d --build
docker compose ps
```

## Kontrollpunkte nach dem Deployment

Auf dem Pi oder einem Rechner im Netzwerk:

```bash
curl -I http://172.16.76.162:8080/login.php
curl -I http://172.16.76.162/admin/
docker compose logs --tail=100 app
docker compose logs --tail=100 db
```

Erwartung:

- App antwortet auf Port `8080`.
- Pi-hole bleibt auf seinen bestehenden Ports erreichbar.
- Es wird kein Datenbank-Port nach außen veröffentlicht.
- Login, Registrierung und Admin-Freigabe funktionieren.

## Pi-hole schützen

Nicht ändern:

- Kein Mapping auf `172.16.76.162:80`
- Kein Mapping auf DNS-Ports `53/tcp` oder `53/udp`
- Kein MariaDB-Port nach außen, also kein `3306:3306`

Die App nutzt bewusst:

```yaml
ports:
  - "${APP_BIND_IP:-172.16.76.162}:${APP_PORT:-8080}:80"
```

## Rollback

Wenn ein Update Probleme macht:

```bash
cd /opt/raspi-verwaltung
git log --oneline -5
git checkout <commit-sha>
docker compose up -d --build
```

Falls auch die Datenbank zurückgesetzt werden muss:

```bash
docker compose exec -T db sh -c 'mariadb -u root -p"$MARIADB_ROOT_PASSWORD" "$MARIADB_DATABASE"' < backups/<backup-datei>.sql
```

Danach wieder prüfen:

```bash
docker compose ps
curl -I http://172.16.76.162:8080/login.php
```

## Regelmäßige Wartung

Empfohlen:

- Vor jedem Update ein Datenbankbackup erstellen.
- Git-Stand vor Deployment committen.
- `.env` nur auf dem Pi pflegen.
- `docker compose logs` nach Updates prüfen.
- Alte Backups regelmäßig aufbewahren und kontrolliert löschen.
- Pi-hole und Inventarverwaltung getrennt überwachen.
