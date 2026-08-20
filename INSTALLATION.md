# Installation

Diese Anleitung beschreibt eine frische Installation von Vertrag Home aus einem Git-Repository.

## 1. Voraussetzungen

Benötigt werden:

- Git
- Docker Engine und Docker Compose oder Docker Desktop
- mindestens 2 GB freier Arbeitsspeicher für die Container
- freier TCP-Port für die Webanwendung, standardmäßig `8080`

Für PDF- und Office-Vorschauen werden Poppler und LibreOffice automatisch im Webcontainer installiert.

## 2. Repository klonen

```bash
git clone <REPOSITORY_URL> vertrag-home
cd vertrag-home
```

## 3. Umgebungsdatei erstellen

Linux/macOS:

```bash
cp .env.example .env
```

Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Die Datei `.env` wird von Git ignoriert und darf nicht veröffentlicht werden.

## 4. Konfiguration anpassen

Mindestens folgende Werte sollten geändert werden:

```env
APP_URL=http://localhost:8080
APP_TIMEZONE=Europe/Berlin
APP_BIND_ADDRESS=127.0.0.1
APP_PORT=8080

DB_PASSWORD=EIN_LANGES_ZUFAELLIGES_PASSWORT
DB_ROOT_PASSWORD=EIN_ANDERES_LANGES_ZUFAELLIGES_PASSWORT
```

Die beiden Datenbankpasswörter müssen unterschiedlich sein.

Geeignete Zufallswerte können beispielsweise erzeugt werden mit:

Linux/macOS:

```bash
openssl rand -hex 32
```

PowerShell:

```powershell
[Convert]::ToHexString([Security.Cryptography.RandomNumberGenerator]::GetBytes(32)).ToLower()
```

### Bind-Adresse

`APP_BIND_ADDRESS=127.0.0.1` macht die Anwendung nur auf dem Host selbst erreichbar und ist die sicherste Standardkonfiguration.

Für einen Zugriff aus dem lokalen Netzwerk kann beispielsweise verwendet werden:

```env
APP_BIND_ADDRESS=0.0.0.0
```

Bei Internetzugriff sollte vorzugsweise ein Reverse Proxy verwendet werden. Läuft der Reverse Proxy selbst in Docker, muss er den Webcontainer entweder über ein gemeinsames Docker-Netzwerk erreichen oder der veröffentlichte Host-Port muss für den Proxy erreichbar sein.

## 5. Automatische Installation

### Linux/macOS

```bash
chmod +x scripts/install.sh
./scripts/install.sh
```

### Windows PowerShell

```powershell
.\scripts\install.ps1
```

Falls PowerShell die Ausführung lokaler Skripte blockiert, kann die Installation auch manuell durchgeführt werden. Eine dauerhafte Änderung der systemweiten Execution Policy ist dafür nicht erforderlich.

## 6. Manuelle Installation

Zuerst die Datenbank starten:

```bash
docker compose up -d db
```

Sobald MariaDB bereit ist, alle Migrationen in alphabetischer Reihenfolge ausführen.

Linux/macOS:

```bash
for file in database/migrations/*.sql; do
  echo "Migration: $file"
  cat "$file" | docker compose exec -T db sh -c 'mariadb -u"$MARIADB_USER" -p"$MARIADB_PASSWORD" "$MARIADB_DATABASE"'
done
```

Windows PowerShell:

```powershell
Get-ChildItem .\database\migrations\*.sql |
    Sort-Object Name |
    ForEach-Object {
        Get-Content -Raw $_.FullName |
            docker compose exec -T db sh -c 'mariadb -u"$MARIADB_USER" -p"$MARIADB_PASSWORD" "$MARIADB_DATABASE"'
    }
```

Danach die Webanwendung bauen und starten:

```bash
docker compose up -d --build web
```

## 7. Ersten Administrator anlegen

Nach den Migrationen:

```bash
docker compose exec web php scripts/create_admin.php
```

Das Skript fragt interaktiv nach:

- Benutzername
- Anzeigename
- optionaler E-Mail-Adresse

Das Startpasswort wird zufällig erzeugt und einmalig in der Konsole angezeigt. Beim ersten Login muss es geändert werden.

Es sind keine Benutzer oder Passwörter im Repository vorkonfiguriert.

## 8. Erster Login

Bei Standardkonfiguration:

```text
http://localhost:8080
```

Nach dem ersten Login:

1. temporäres Passwort ändern
2. unter Administration mindestens einen Vertragsinhaber anlegen
3. bei Bedarf weitere Benutzer einrichten
4. Vertragsarten und Dokumentarten prüfen

## 9. HTTPS mit Reverse Proxy

Für Netzwerk- oder Internetzugriff sollte die Anwendung hinter einem Reverse Proxy betrieben werden, beispielsweise:

- Nginx Proxy Manager
- Traefik
- Caddy
- klassisches Nginx

Beispiel für die interne Weiterleitung:

```text
https://contracts.example.org
        ↓
Reverse Proxy
        ↓
http://HOST:8080
```

Danach in `.env` setzen:

```env
APP_URL=https://contracts.example.org
SESSION_SECURE_COOKIE=true
```

Wenn der Reverse Proxy die ursprüngliche Client-IP über `X-Forwarded-For` übergibt, kann zusätzlich `TRUSTED_PROXIES` gesetzt werden. Dort dürfen ausschließlich tatsächlich kontrollierte Proxy-IP-Adressen oder CIDR-Netze eingetragen werden, zum Beispiel:

```env
TRUSTED_PROXIES=172.20.0.0/16
```

Ohne diese Einstellung ignoriert Vertrag Home `X-Forwarded-For` bewusst und verwendet die direkte Gegenstelle als Client-IP.

Anschließend den Webcontainer neu erstellen:

```bash
docker compose up -d --force-recreate web
```

## 10. Eigene Domain im lokalen Netzwerk

Für rein lokale Installationen kann ein interner DNS-Name verwendet werden, zum Beispiel:

```text
contracts.home.arpa
```

`home.arpa` ist für private Heimnetze vorgesehen und kollidiert nicht mit öffentlichen DNS-Namen.

## 11. Dokumente und Datenbank

Die Standardinstallation verwendet Docker-Volumes:

- `database` für MariaDB
- `documents` für Vertragsdokumente
- `logs` für Anwendungslogs

Diese Daten liegen nicht im Git-Repository.

Volumes anzeigen:

```bash
docker volume ls
```

## 12. Backup

Für ein vollständiges Backup müssen mindestens gesichert werden:

1. MariaDB-Datenbank
2. Dokument-Volume
3. `.env`

Beispiel für einen Datenbankexport:

```bash
docker compose exec -T db sh -c 'mariadb-dump -u"$MARIADB_USER" -p"$MARIADB_PASSWORD" "$MARIADB_DATABASE"' > vertrag-home.sql
```

Die Sicherungsdatei darf nicht in das öffentliche Git-Repository aufgenommen werden.

## 13. Update

Repository aktualisieren:

```bash
git pull
```

Anwendung neu bauen:

```bash
docker compose up -d --build web
```

Danach die Migrationen erneut in alphabetischer Reihenfolge ausführen. Die bereitgestellten Migrationen sind so aufgebaut, dass bereits vorhandene Strukturen berücksichtigt werden.

## 14. Entwicklung mit Tailwind-Watcher

Für lokale Entwicklung:

```bash
docker compose --profile dev up tailwind
```

Der produktive Docker-Build erzeugt die CSS-Datei automatisch. Für eine normale Installation ist daher keine lokale Node.js-Installation erforderlich.

## Branding anpassen

Das Standardlogo und das Favicon sind bereits enthalten. Für eine eigene Installation können folgende Dateien ersetzt werden:

```text
public/assets/images/vertrag-home-logo.png
public/assets/images/vertrag-home-favicon.png
```

Nach einem Austausch empfiehlt sich ein vollständiges Neuladen des Browsers, damit ein zwischengespeichertes Favicon aktualisiert wird.
