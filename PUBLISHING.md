# Öffentliche Veröffentlichung über Git

Für eine Veröffentlichung auf GitHub, GitLab, Codeberg, Gitea oder einem anderen öffentlichen Git-Server sollte eine saubere Repository-Historie verwendet werden.

## Empfohlener Weg

Wenn das bisherige private Repository jemals persönliche Daten, lokale IP-Adressen, Passwörter, `.env`-Dateien oder andere interne Informationen enthalten hat, sollte der öffentliche Stand nicht aus derselben Historie veröffentlicht werden.

Stattdessen:

1. den bereinigten Projektordner verwenden
2. sicherstellen, dass kein `.git`-Ordner übernommen wurde
3. ein neues leeres öffentliches Repository erstellen
4. eine neue Git-Historie beginnen

Beispiel:

```bash
cd vertrag-home
git init
git add .
git commit -m "Initial public release"
git branch -M main
git remote add origin <PUBLIC_REPOSITORY_URL>
git push -u origin main
```

## Vor dem Push prüfen

```bash
git status
git ls-files
```

Insbesondere dürfen nicht auftauchen:

```text
.env
storage/documents/
storage/logs/
*.sql
*.zip
node_modules/
```

Zusätzlich empfiehlt sich eine Textsuche nach persönlichen Begriffen oder internen Infrastrukturwerten.

Linux/macOS:

```bash
grep -RniE 'password|secret|token|192\\.168\\.|10\\.|172\\.16\\.' . --exclude-dir=.git
```

Die Treffer müssen einzeln bewertet werden, da Begriffe wie `password` in normalem Anwendungscode vorkommen können.

## Repository-Beschreibung

Eine neutrale Kurzbeschreibung wäre beispielsweise:

> Self-hosted contract management with document history, cancellation tracking and expense planning.

## Releases

Für Releases können ZIP-Archive direkt aus Git erzeugt werden. Laufzeitdaten, Dokumente und `.env` gehören nicht in Release-Artefakte.

## Open-Source-Lizenz

Das Repository enthält eine `LICENSE`-Datei mit der MIT-Lizenz. Diese Datei sollte bei öffentlichen Forks und Weitergaben zusammen mit dem Quellcode erhalten bleiben.
