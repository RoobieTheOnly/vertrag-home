# Mitwirken an Vertrag Home

Beiträge zum Projekt sind willkommen.

## Lokale Entwicklung

1. Repository forken oder klonen.
2. `.env.example` nach `.env` kopieren.
3. Eigene lokale Zugangsdaten in `.env` setzen.
4. Container starten:

```bash
docker compose up -d --build
```

5. Migrationen ausführen und einen lokalen Administrator über `scripts/create_admin.php` anlegen.

Die vollständige Installation ist in `INSTALLATION.md` beschrieben.

## Änderungen einreichen

- Änderungen möglichst klein und nachvollziehbar halten.
- Keine Zugangsdaten, produktiven Datenbanken oder Vertragsdokumente committen.
- Neue Datenbankänderungen als eigene Migration unter `database/migrations/` ergänzen.
- PHP-Dateien vor dem Commit mit `php -l` prüfen.
- JavaScript-Dateien vor dem Commit mit `node --check` prüfen.
- Bei Änderungen am Tailwind-Markup die CSS-Ausgabe neu bauen.

## Datenschutz

Pull Requests dürfen keine personenbezogenen Vertragsdaten, privaten Dokumente, E-Mail-Adressen, Passwörter, Tokens, internen Hostnamen oder IP-Adressen aus realen Installationen enthalten.

## Lizenz

Mit dem Einreichen eines Beitrags erklärst du dich damit einverstanden, dass dein Beitrag unter der MIT-Lizenz des Projekts veröffentlicht wird.
