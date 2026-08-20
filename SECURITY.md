# Sicherheit

## Geheimnisse und Konfiguration

- `.env` niemals committen oder veröffentlichen.
- für `DB_PASSWORD` und `DB_ROOT_PASSWORD` unterschiedliche, zufällige Passwörter verwenden.
- keine echten Passwörter in Screenshots, Issues oder Logdateien veröffentlichen.
- das temporäre Passwort des ersten Administrators nach dem ersten Login sofort ändern.

## Netzwerk

Die MariaDB-Datenbank wird in der Standardkonfiguration nicht auf einen Host-Port veröffentlicht.

Für eine ausschließlich lokale Installation sollte `APP_BIND_ADDRESS=127.0.0.1` verwendet werden, wenn nur ein Reverse Proxy auf demselben Host zugreifen muss.

Für Zugriffe aus anderen Netzen oder aus dem Internet:

- HTTPS verwenden
- `SESSION_SECURE_COOKIE=true` setzen
- einen Reverse Proxy einsetzen
- keine direkte Portfreigabe auf den MariaDB-Container einrichten
- Updates für Hostsystem, Docker und Container regelmäßig einspielen

## Dokumente

Vertragsdokumente liegen außerhalb des öffentlichen Webverzeichnisses und werden nur über die Anwendung ausgeliefert.

Die Docker-Volumes mit Dokumenten und Datenbankinhalten sollten als vertrauliche Daten behandelt und entsprechend gesichert werden.

## Git-Veröffentlichung

Vor einer öffentlichen Veröffentlichung sollte geprüft werden, dass folgende Inhalte nicht in der Git-Historie vorkommen:

- `.env`
- Datenbankdumps
- Dokumente
- private Schlüssel
- Zertifikate mit privaten Schlüsseln
- Zugangsdaten
- interne IP-Adressen oder Hostnamen, sofern diese nicht veröffentlicht werden sollen
- personenbezogene Namen oder E-Mail-Adressen

Wenn solche Inhalte bereits in einem privaten Repository committed wurden, reicht das Löschen im aktuellen Stand nicht aus. Die Git-Historie muss bereinigt oder für die öffentliche Veröffentlichung neu erstellt werden.

## Reverse Proxies

HTTP-Header wie `X-Forwarded-For` werden nur ausgewertet, wenn die direkte Gegenstelle in `TRUSTED_PROXIES` eingetragen ist. Dadurch kann ein Client nicht selbst eine beliebige Quell-IP über diesen Header vortäuschen.

Beispiele:

```env
TRUSTED_PROXIES=172.20.0.10
```

oder für ein kontrolliertes internes Proxy-Netz:

```env
TRUSTED_PROXIES=172.20.0.0/16
```

Es sollten keine fremden oder öffentlichen Netze als vertrauenswürdige Proxies eingetragen werden.
