# Vertrag Home

Vertrag Home ist eine selbst gehostete Webanwendung zur Verwaltung privater oder gemeinsamer Verträge, Dokumente, Laufzeiten, Kündigungen und planbarer Ausgaben.

Die Anwendung läuft vollständig auf der eigenen Infrastruktur. Vertragsdaten und hochgeladene Dokumente werden nicht an externe Dienste übertragen.

## Funktionen

- responsive Oberfläche für Desktop und Smartphone
- automatische Hell-/Dunkeldarstellung anhand der Systemeinstellung
- Benutzer- und Rollenverwaltung
- Vertragsarten und Vertragsinhaber administrierbar
- Vertragsstatus, Laufzeiten, Kündigungsfristen und automatische Verlängerungen
- geplante Kündigungen mit tatsächlichem Vertragsende
- zeitlich begrenzte Vertragspausen
- Preis- und Kostenhistorie je Vertrag
- Benachrichtigungen je Vertrag separat aktivierbar
- Dokumentarten, Dokumentversionierung und Dokumenthistorie
- geschützter Dokumentdownload
- lokale Vorschau für PDF, Bilder, Text und unterstützte Office-Dokumente
- Ausgabenplanung für 1 Monat, 3 Monate und 1 Jahr
- Ausgabenplanung nach Vertragsinhaber
- Kostenentwicklung und Einsparpotenzial
- Finanzübersicht / Selbstauskunft mit Druckansicht
- Auditlog für administrative Nachvollziehbarkeit

## Technik

- PHP 8.4 / Apache
- MariaDB 11.8
- Tailwind CSS lokal
- Docker Compose
- Poppler für PDF-Vorschauen
- LibreOffice Writer/Calc für lokale Office-Vorschauen

## Schnellstart

Voraussetzungen:

- Git
- Docker Engine mit Docker Compose oder Docker Desktop

Repository klonen:

```bash
git clone <REPOSITORY_URL> vertrag-home
cd vertrag-home
```

Konfiguration anlegen:

```bash
cp .env.example .env
```

Unter Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Danach die Werte in `.env` anpassen, insbesondere die beiden Datenbankpasswörter.

Installation unter Linux/macOS:

```bash
./scripts/install.sh
```

Installation unter Windows PowerShell:

```powershell
.\scripts\install.ps1
```

Ersten Administrator anlegen:

```bash
docker compose exec web php scripts/create_admin.php
```

Die vollständige Anleitung steht in [INSTALLATION.md](INSTALLATION.md).

## Datenschutz und Repository-Inhalt

Das öffentliche Repository enthält keine vorkonfigurierten Benutzer, persönlichen Vertragsinhaber, Passwörter oder produktiven Zugangsdaten.

Folgende Inhalte werden bewusst nicht versioniert:

- `.env`
- hochgeladene Vertragsdokumente
- Laufzeit-Logs
- Datenbankinhalte
- lokale Editor-Konfigurationen
- Sicherungen und Exportdateien

## Branding

Das Repository enthält das Standardlogo und das Favicon von Vertrag Home:

```text
public/assets/images/vertrag-home-logo.png
public/assets/images/vertrag-home-favicon.png
```

Die Dateien können bei einer eigenen Installation durch eigene Branding-Dateien mit denselben Dateinamen ersetzt werden. Fehlt das Logo, verwendet die Anwendung automatisch den Text `Vertrag Home` als Fallback.

## Sicherheit

Für eine Veröffentlichung im Internet sollte die Anwendung ausschließlich hinter einem HTTPS-Reverse-Proxy betrieben werden. Die Datenbank wird in der Standardkonfiguration nicht auf einen Host-Port veröffentlicht.

Weitere Hinweise stehen in [SECURITY.md](SECURITY.md).

## Veröffentlichung

Wenn ein zuvor privat genutztes Git-Repository persönliche Daten oder Zugangsdaten in älteren Commits enthalten hat, sollte für die öffentliche Veröffentlichung ein neues Repository mit einer neuen Historie angelegt werden.

Siehe [PUBLISHING.md](PUBLISHING.md).

## Lizenz

Vertrag Home ist Open Source und wird unter der [MIT-Lizenz](LICENSE) veröffentlicht. Die Lizenz erlaubt insbesondere Nutzung, Änderung und Weitergabe des Quellcodes unter den in der Lizenz genannten Bedingungen.

## Mitwirken

Hinweise für Beiträge, Pull Requests und lokale Entwicklung stehen in [CONTRIBUTING.md](CONTRIBUTING.md).
