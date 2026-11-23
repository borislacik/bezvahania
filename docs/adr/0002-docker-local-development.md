# ADR 0002: Lokálny vývoj v Dockeri, kód vo Windows priečinku

- Stav: prijaté
- Dátum: 2026-11-23

## Kontext
Zadanie pôvodne počítalo s WSL2. Používateľ doplnil pravidlo, že **všetok lokálny vývoj beží v Dockeri
a je plne testovateľný lokálne**, a rozhodol, že kód ostane vo Windows priečinku (WSL sa neinštaluje).
Na PC je Docker Desktop a Git for Windows.

## Rozhodnutie
- Vlastný `compose.yaml` (nie Laravel Sail) so službami:
  - `app` – PHP 8.4-FPM, Composer 2, Node 26 LTS, Playwright Chromium (`docker/php/Dockerfile`),
  - `web` – nginx, obsluhuje `bezvahania.localhost` aj `admin.bezvahania.localhost`,
  - `db` – MariaDB 11.4, databázy `bezvahania` a `bezvahania_testing` (+ `bezvahania_testing_test_N` pre paralelné testy),
  - `mailpit` – lokálny e-mail (UI na porte 8025),
  - `vite` – voliteľný Vite dev server (profil `vite`).
- `vendor/` a `node_modules/` sú v **named volumes**, nie v bind mounte z Windows (výkon).
- Všetky príkazy projektu sa spúšťajú cez `docker compose exec app …`, aj `composer check`.
- Git beží na hoste (commity, hooky v Git Bash). Hooky volajú Pint a Prettier v kontajneri.
- Konce riadkov LF: `.gitattributes` (`* text=auto eol=lf`), `core.autocrlf=false`, `core.fileMode=false`.

## Dôsledky
- Vlastný obraz dáva presnú kontrolu nad verziou PHP a rozšíreniami (zhoda s hostingom) a nad Playwrightom.
- IDE na Windows nevidí `vendor/` (je vo volume). Ak bude chýbať napovedanie kódu, dá sa `vendor/` skopírovať
  na hosta (`docker compose cp app:/var/www/html/vendor ./vendor`), alebo použiť Boost MCP.
- Bind mount z Windows je pomalší než WSL. Ak bude odozva neúnosná, prejdeme na WSL (nové ADR).
- Verzia Playwrightu v `docker/php/Dockerfile` (`PLAYWRIGHT_VERSION`) a v `package.json` sa musí zhodovať.

## Zvažované alternatívy
- **WSL2 + natívne nástroje** (pôvodné zadanie) – zamietnuté používateľom.
- **Kód vo WSL + Docker** – najrýchlejšie, ale používateľ nechce WSL.
- **Laravel Sail** – menej kontroly nad rozšíreniami, nginx a Playwrightom.
