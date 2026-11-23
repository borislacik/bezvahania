# Hosting Websupport

> Stav: čiastočne overené (23. 11. 2026). Úplné overenie podľa [zadania, sekcia 9.2](brief.md) prebehne vo Fáze 2.
> Repozitár je verejný, preto tu **nie sú** prístupové údaje (host, port, používateľ). Sú iba v `~/.ssh/config`
> vývojára pod aliasom `bezvahania-prod`.

## Overené
| Čo | Výsledok |
|---|---|
| SSH | Trvalý SSH prístup funguje, prihlásenie kľúčom (ed25519). |
| Štruktúra domovského adresára | `~/bezvahania.sk/` obsahuje `web/` (koreň domény), `sub/` (subdomény), `logs/`. |
| Starý obsah | WordPress zmazaný z `web/` 23. 11. 2026. Databáza WordPressu vo WebAdmine ešte existuje. |

## Na overenie vo Fáze 2
1. Verzia PHP pre web vs. CLI v SSH a cesta k binárke.
2. PHP rozšírenia: `intl`, `gd` (s **WebP**) / `imagick`, `zip`, `bcmath`, `pdo_mysql`, `fileinfo`.
3. Reálne cesty: `__DIR__` cez web vs. `pwd -P` v SSH (vplyv na `config:cache`).
4. Koreňový adresár cez symlink (`current/public`) a relatívne symlinky.
5. Cron wrapper a jeho PHP verzia.
6. DB (verzia MariaDB, host, port), SMTP, SSL pre `bezvahania.sk`, `www.` a `admin.`.
7. `tar`, `gzip`, `mysqldump` v shelli.

## Checklist WebAdminu
Doplní sa vo Fáze 2 (PHP verzia, DB, koreňové adresáre, SSL, `shared/.env`, cron, SMTP, SPF/DKIM/DMARC).
Staging sa **nezakladá** ([decisions.md](decisions.md)).
