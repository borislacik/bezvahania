# Changelog

Všetky podstatné zmeny projektu. Formát podľa [Keep a Changelog](https://keepachangelog.com/sk/1.1.0/),
verzie podľa git tagov z deployov (`vRRRR.MM.DD-N`).

## [Unreleased]

### Opravené
- Písma Work Sans a Space Grotesk sa reálne načítajú na stránke (chýbala direktíva `@fonts`).

### Pridané
- Kostra projektu: Laravel 13, PHP 8.4, slovenská lokalizácia, časové pásmo Europe/Bratislava.
- Lokálne vývojové prostredie v Dockeri (PHP-FPM, nginx, MariaDB 11.4, Mailpit, Vite, Playwright).
- Nástroje kvality: Pest 5 (Unit, Feature, Browser), Pint, Larastan (level 6), Prettier, Laravel Boost.
- Composer skripty `lint`, `format`, `analyse`, `test`, `test:browser`, `docs:check`, `check`.
- Git hooky (Pint/Prettier pri commite, Conventional Commits, ochrana pushu do `main`).
- Kontrola nepreložených textov v Blade šablónach.
- Skill `new-change` pre Claude Code (postup jednej požiadavky).
- Dokumentácia: README, CLAUDE.md, rozhodnutia k zadaniu, architektúra, ADR 0001–0004.
