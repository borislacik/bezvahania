# bezvahania.sk

Web a rezervačný systém pre **domáce workshopy prvej pomoci** – malé skupiny do 4 osôb, u zákazníka doma
alebo v kancelárii, Bratislava a okolie.

| Prostredie | URL |
|---|---|
| Produkcia – web | https://bezvahania.sk (zatiaľ nenasadené) |
| Produkcia – admin | https://admin.bezvahania.sk (zatiaľ nenasadené) |
| Lokálne – web | http://bezvahania.localhost |
| Lokálne – admin | http://admin.bezvahania.localhost (od Fázy 2) |
| Lokálne – Mailpit | http://localhost:8025 |

Staging neexistuje, všetko sa testuje lokálne ([rozhodnutia](docs/decisions.md)).
Repozitár: https://github.com/borislacik/bezvahania (verejný, bez CI).

## Požiadavky
- **Docker Desktop** (Windows, macOS alebo Linux)
- **Git** (na Windows Git for Windows, hooky bežia v Git Bash)

PHP, Composer, Node, MariaDB, Mailpit a Playwright bežia v kontajneroch. Nič z toho netreba inštalovať.

## Rýchly štart
```bash
git clone git@github.com:borislacik/bezvahania.git && cd bezvahania
git config core.hooksPath .githooks
git config core.autocrlf false
cp .env.example .env
docker compose up -d --build
docker compose exec app composer install
docker compose exec app npm ci
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app npm run build
```
Potom otvor http://bezvahania.localhost. Domény `*.localhost` sa prekladajú na tvoj počítač automaticky,
súbor `hosts` netreba upravovať.

Vite s automatickým obnovovaním: `docker compose --profile vite up -d vite` (pri builde cez `npm run build`
ho vypni, inak sa použije dev server).

## Lokálny vývoj
Všetky príkazy sa spúšťajú v kontajneri `app`:

| Príkaz | Čo robí |
|---|---|
| `docker compose up -d` / `docker compose down` | spustí / zastaví stack |
| `docker compose exec app php artisan …` | artisan |
| `docker compose exec app composer lint` | Pint `--test` + Prettier `--check` |
| `docker compose exec app composer format` | opraví formátovanie (Pint + Prettier) |
| `docker compose exec app composer analyse` | Larastan (PHPStan, level 6) |
| `docker compose exec app composer test` | Unit + Feature testy, paralelne, pokrytie min. 80 % |
| `docker compose exec app composer test:browser` | browser testy (Pest + Playwright, Chromium) |
| `docker compose exec app composer docs:check` | kontrola dokumentácie |
| `docker compose exec app composer check` | všetko vyššie + `npm run build`; pri úspechu a čistom strome zapíše `.check-passed` |

Pripravuje sa (Fáza 2): vytvorenie admina (`app:create-admin`), deploy a rollback (`scripts/deploy.sh`, `scripts/rollback.sh`).

### Služby v Dockeri
| Služba | Účel | Port na hoste |
|---|---|---|
| `app` | PHP 8.4-FPM, Composer, Node 26, Playwright | – |
| `web` | nginx pre obe domény | 80 (`APP_PORT`) |
| `db` | MariaDB 11.4 (`bezvahania`, `bezvahania_testing`) | 3306 (`FORWARD_DB_PORT`) |
| `mailpit` | zachytáva odoslané e-maily | 8025 (`FORWARD_MAILPIT_UI_PORT`) |
| `vite` | Vite dev server (profil `vite`) | 5173 (`VITE_PORT`) |

`vendor/` a `node_modules/` sú v Docker volumes, nie v priečinku projektu ([ADR 0002](docs/adr/0002-docker-local-development.md)).

## Premenné prostredia
Úplný zoznam s komentármi je v [.env.example](.env.example). Najdôležitejšie:

| Premenná | Význam |
|---|---|
| `APP_DOMAIN`, `ADMIN_DOMAIN` | hostname verejného webu a adminu |
| `APP_LOCALE`, `APP_FALLBACK_LOCALE`, `APP_FAKER_LOCALE` | `sk`, `sk`, `sk_SK` |
| `APP_TIMEZONE` | `Europe/Bratislava` |
| `DB_*` | MariaDB (lokálne služba `db`) |
| `MAIL_*` | lokálne Mailpit, na produkcii SMTP Websupport, odosielateľ `rezervacie@bezvahania.sk` |
| `GOOGLE_ANALYTICS_ID` | GA4, iba na produkcii a iba po súhlase s cookies |
| `APP_PORT`, `VITE_PORT`, `FORWARD_DB_PORT`, `FORWARD_MAILPIT_UI_PORT` | porty Docker služieb na hoste |

Mimo `.env` (premenná prostredia na hoste): `STITCH_API_KEY` pre Stitch MCP v Claude Code.

## Git workflow v skratke
- `main` je vždy nasaditeľný, priamo naň sa necommituje.
- Každá požiadavka = vetva z `main` (`feat/…`, `fix/…`, `chore/…`, `docs/…`, `test/…`), Conventional Commits.
- Zmena je hotová až s testami a dokumentáciou (`docs/changes/`, `CHANGELOG.md`, tento README) a so zeleným `composer check`.
- Merge do `main` s `--no-ff`, potom `composer check` na `main` a push. Hook `pre-push` overuje `.check-passed`.
- Repozitár je verejný: nikdy necommituj `.env`, kľúče, heslá ani dumpy DB.

## Claude Code
- Pravidlá: [CLAUDE.md](CLAUDE.md).
- MCP servery (`.mcp.json`): `laravel-boost` (v kontajneri, vyžaduje bežiaci stack) a `stitch` (dizajn, kľúč v `STITCH_API_KEY`).

### Skills v repozitári (`.claude/skills/`)
| Skill | Na čo slúži |
|---|---|
| `new-change` | postup každej požiadavky: vetva → implementácia → testy → dokumentácia → `composer check` → zhrnutie → merge po súhlase |
| `infer-conventions` | (Boost) zaznamená konvencie aplikácie |
| `laravel-best-practices` | (Boost) odporúčané postupy pre Laravel |
| `testing-best-practices` | (Boost) písanie a štruktúra testov |
| `tailwindcss-development` | (Boost) práca s Tailwind CSS |

Ďalšie projektové skills (`deploy`, `rollback`, `filament-resource`, …) pribudnú po schválení.

## Dokumentácia
| Dokument | Obsah |
|---|---|
| [docs/brief.md](docs/brief.md) | pôvodné zadanie |
| [docs/decisions.md](docs/decisions.md) | odpovede na otvorené otázky a doplnené požiadavky (majú prednosť pred zadaním) |
| [docs/architecture.md](docs/architecture.md) | architektúra a doménový model |
| [docs/hosting-websupport.md](docs/hosting-websupport.md) | zistenia z hostingu, checklist WebAdminu |
| [docs/deploy.md](docs/deploy.md) | nasadenie a rollback |
| [docs/deployments.md](docs/deployments.md) | záznam nasadení |
| [docs/changes/](docs/changes/) | záznam ku každej vetve |
| [docs/adr/](docs/adr/) | architektonické rozhodnutia |
| [docs/features/](docs/features/) | dokumentácia funkcionalít |
| [CHANGELOG.md](CHANGELOG.md) | prehľad zmien |
