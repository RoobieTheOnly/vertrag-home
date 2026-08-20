#!/usr/bin/env sh
set -eu

cd "$(dirname "$0")/.."

if [ ! -f .env ]; then
    echo "Die Datei .env fehlt. Kopiere zuerst .env.example nach .env und passe die Werte an." >&2
    exit 1
fi

echo "Starte Datenbank..."
docker compose up -d db

echo "Warte auf MariaDB..."
until docker compose exec -T db healthcheck.sh --connect --innodb_initialized >/dev/null 2>&1; do
    sleep 2
done

echo "Führe Datenbankmigrationen aus..."
for file in database/migrations/*.sql; do
    echo "  -> $(basename "$file")"
    cat "$file" |
        docker compose exec -T db sh -c 'mariadb -u"$MARIADB_USER" -p"$MARIADB_PASSWORD" "$MARIADB_DATABASE"'
done

echo "Baue und starte Webanwendung..."
docker compose up -d --build web

echo "Installation abgeschlossen."
echo "Lege jetzt den ersten Administrator an:"
echo "docker compose exec web php scripts/create_admin.php"
