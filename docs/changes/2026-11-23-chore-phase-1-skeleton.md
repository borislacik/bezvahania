# chore/phase-1-skeleton – kostra projektu a brány kvality

## Požiadavka
Fáza 1 zo zadania: Laravel 13 s nástrojmi zo sekcie 2, slovenská lokalizácia, `.gitattributes`, `.editorconfig`,
git hooky, composer skripty vrátane `docs:check`, kostra dokumentácie, prvé ADR, ukážkový test každého druhu,
Laravel Boost. Navyše: všetok lokálny vývoj v Dockeri a plne testovateľný lokálne, kód vo Windows priečinku.

## Riešenie
- Vlastný Docker stack (`compose.yaml`, `docker/`) namiesto Sailu – presná kontrola nad PHP rozšíreniami a
  Playwrightom ([ADR 0002](../adr/0002-docker-local-development.md)).
- Laravel 13.43 bez starter kitu, Pest 5 namiesto Pestu 4 (rozhodnutie používateľa).
- Testy bežia proti MariaDB (`bezvahania_testing`), nie SQLite. Paralelné testy si vytvárajú vlastné DB.
- PHPStan analyzuje `app`, `config`, `database`, `routes`, `bootstrap/app.php`; `tests/` nie (Pest closures).
- `UntranslatedTextScanner` + test hľadajú natvrdo zapísané texty v Blade (sekcia 4.4 zadania).
- Design tokeny a písma podľa Stitch návrhu, písma servírované lokálne.
- Domény `APP_DOMAIN` / `ADMIN_DOMAIN` sú v konfigurácii, oddelenie rout a Filament prídu vo Fáze 2.

## Dotknuté časti
- Docker: `compose.yaml`, `docker/php/Dockerfile`, `docker/php/php.ini`, `docker/nginx/default.conf`, `docker/mariadb/init/`.
- Konfigurácia: `config/app.php` (domény, locale, timezone), `.env.example`, `phpunit.xml`, `phpstan.neon`, `pint.json`,
  `.prettierrc.json`, `.prettierignore`, `vite.config.js`, `boost.json`, `.mcp.json`, `.gitignore`.
- Kód: `app/Support/UntranslatedTextScanner.php`, `routes/web.php`, `resources/views/components/layouts/public.blade.php`,
  `resources/views/pages/home.blade.php`, `resources/css/app.css`, `lang/sk/*`.
- Skripty a hooky: `scripts/docs-check.php`, `scripts/check.sh`, `.githooks/*`.
- `.env` premenné: nové `APP_DOMAIN`, `ADMIN_DOMAIN`, `APP_TIMEZONE`, `GOOGLE_ANALYTICS_ID`, porty Dockeru.

## Testy
- Unit: `tests/Unit/UntranslatedTextScannerTest.php`.
- Feature: `tests/Feature/HomePageTest.php`, `tests/Feature/TranslationsTest.php`.
- Browser: `tests/Browser/HomePageTest.php` (Playwright, bez JS chýb).
- Ručne: `docker compose up -d`, otvoriť http://bezvahania.localhost.

## Nasadenie
Nič sa nenasadzuje. Deploy skript vznikne vo Fáze 2.

## Odkazy
[ADR 0001](../adr/0001-tech-stack.md), [ADR 0002](../adr/0002-docker-local-development.md),
[ADR 0003](../adr/0003-two-domains-one-app.md), [ADR 0004](../adr/0004-build-assets-locally.md),
[decisions.md](../decisions.md).
