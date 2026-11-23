# ADR 0001: Technologický stack

- Stav: prijaté
- Dátum: 2026-11-23

## Kontext
Web pre domáce workshopy prvej pomoci s rezerváciami a administráciou. Beží na zdieľanom hostingu
Websupport (bez VPS, bez Redisu a supervisora). Vývojár je jeden, kód píše hlavne Claude Code.

## Rozhodnutie
- PHP 8.4, Laravel 13 (čistá inštalácia bez starter kitu), Filament 5 pre admin.
- Blade komponenty + Tailwind CSS 4 cez Vite, Alpine.js iba kde treba. Žiadne SPA.
- MariaDB 11.4 lokálne aj na produkcii (overí sa vo Fáze 2).
- Fronta, cache a session cez `database` driver.
- Pest 5 + `pestphp/pest-plugin-browser` (Playwright), Pint, Larastan (level 6 → 8), Prettier.
- `laravel/boost` (dev) pre guidelines a MCP pre Claude Code.
- `laravel-lang/common` (dev) na slovenské preklady Laravelu.

## Dôsledky
- Overené verzie z 23. 11. 2026: Laravel 13.43.0, Pest 5.3.7, pest-plugin-browser 5.2.0, Larastan 3.15.0,
  Boost 2.10.4, Pint 1.32.3, Node 26 LTS, Playwright 1.65.1.
- Browser plugin vyžaduje PHP rozšírenie `sockets` (iba vývoj).
- Bez Redisu: fronta beží cez cron (`queue:work --stop-when-empty`), pozri Fázu 2.

## Zvažované alternatívy
- **Pest 4** (podľa zadania) – zamietnuté, Pest 5 je aktuálna major verzia (rozhodnutie používateľa).
- **Livewire/Inertia SPA na verejnom webe** – zbytočné pre prevažne statický web, horšie SEO a výkon.
