$ErrorActionPreference = "Stop"

Set-Location (Split-Path -Parent $PSScriptRoot)

if (-not (Test-Path ".env")) {
    throw "Die Datei .env fehlt. Kopiere zuerst .env.example nach .env und passe die Werte an."
}

Write-Host "Starte Datenbank..."
docker compose up -d db

Write-Host "Warte auf MariaDB..."
do {
    docker compose exec -T db healthcheck.sh --connect --innodb_initialized *> $null
    if ($LASTEXITCODE -ne 0) {
        Start-Sleep -Seconds 2
    }
} while ($LASTEXITCODE -ne 0)

Write-Host "Führe Datenbankmigrationen aus..."
Get-ChildItem ".\database\migrations\*.sql" |
    Sort-Object Name |
    ForEach-Object {
        Write-Host ("  -> " + $_.Name)
        Get-Content -Raw $_.FullName |
            docker compose exec -T db sh -c 'mariadb -u"$MARIADB_USER" -p"$MARIADB_PASSWORD" "$MARIADB_DATABASE"'
    }

Write-Host "Baue und starte Webanwendung..."
docker compose up -d --build web

Write-Host "Installation abgeschlossen."
Write-Host "Lege jetzt den ersten Administrator an:"
Write-Host "docker compose exec web php scripts/create_admin.php"
