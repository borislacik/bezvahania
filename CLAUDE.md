# CLAUDE.md – pravidlá pre Claude Code (bezvahania.sk)

Web pre domáce workshopy prvej pomoci (max. 4 osoby, u zákazníka doma alebo v kancelárii) + administrácia.
Úplné zadanie: `docs/brief.md`. Rozhodnutia: `docs/adr/`. Architektúra: `docs/architecture.md`.

## Komunikácia a jazyk
- S používateľom **po slovensky**. Kód, názvy, DB stĺpce, commit správy **po anglicky**.
- Texty pre návštevníka aj admina iba cez `__()` a `lang/sk/*.php` / `lang/sk.json`, nikdy natvrdo.
- Dokumentácia v `docs/` je po slovensky.
- Nič si nevymýšľaj (verzie, heslá, cesty na serveri, SMTP). Ak to nevieš overiť, opýtaj sa.

## Lokálne prostredie – všetko v Dockeri
- Na hoste (Windows) je iba Docker Desktop a Git. PHP, Composer, Node, MariaDB, Mailpit a Playwright bežia v kontajneroch.
- Každý príkaz projektu spúšťaj cez `docker compose exec app …` (alebo `docker compose run --rm app …`, ak stack nebeží).
  - `docker compose exec app php artisan …`
  - `docker compose exec app composer …`
  - `docker compose exec app npm …`
- `vendor/` a `node_modules/` sú v Docker volume, nie vo Windows priečinku. Nehľadaj ich na hoste.
- Lokálne URL: `http://bezvahania.localhost`, `http://admin.bezvahania.localhost`, Mailpit `http://localhost:8025`.
- Kód je vo Windows priečinku (nie WSL), konce riadkov vždy LF (`.gitattributes`, `core.autocrlf=false`).

## Príkazy
| Príkaz | Čo robí |
|---|---|
| `docker compose up -d` | spustí stack (app, web, db, mailpit) |
| `docker compose --profile vite up -d vite` | Vite dev server s HMR |
| `docker compose exec app composer lint` | Pint `--test` + Prettier `--check` |
| `docker compose exec app composer analyse` | Larastan (PHPStan) |
| `docker compose exec app composer test` | Unit + Feature, paralelne, coverage min. 80 % |
| `docker compose exec app composer test:browser` | Pest browser testy (Playwright) |
| `docker compose exec app composer docs:check` | kontrola dokumentácie |
| `docker compose exec app composer check` | všetko vyššie + `npm run build`, zapíše `.check-passed` |

## Stack
PHP 8.4 · Laravel 13 · Filament 5 (admin na subdoméne `admin.`) · Blade + Tailwind 4 (Vite) · Alpine.js iba ak treba ·
MariaDB 11.4 · Pest 5 + browser plugin · Pint · Larastan · Prettier · Laravel Boost · Mailpit · fronta/cache/session `database`.

## Architektúra (skrátene)
- Jedna aplikácia, dve domény z `.env` (`APP_DOMAIN`, `ADMIN_DOMAIN`) cez `config('app.domain')` / `config('app.admin_domain')`. Nikdy natvrdo.
- Verejné routy v `Route::domain(config('app.domain'))`, Filament panel `->domain(config('app.admin_domain'))->path('')`.
- Tenké controllery, biznis logika v `App\Actions\…` (volá ich web aj Filament). Validácia vo Form Requests.
- Stavy ako PHP Enums s `label()` cez preklady. E-maily vo fronte.
- Rezervácia: celý termín (deň + doobeda 9–13 / poobede 15–19) pre jednu skupinu 2–4 osôb, bez potvrdzovania adminom.
  Dvojitej rezervácii bráni unikátny index na `bookings.active_slot` (ADR 0005), nie `lockForUpdate()`.
- SEO (title, description, OG obrázok) je editovateľné v admine. **Všetky obrázky na webe sú WebP**, nahraté obrázky sa automaticky konvertujú na WebP.
- Google Analytics iba po súhlase (cookie lišta podľa EÚ, Consent Mode v2). GDPR: osobné údaje nikdy do logov.
- Časové pásmo `Europe/Bratislava`, slovenské formáty. Architektúra pripravená na ďalší jazyk (`/{locale}` prefix neskôr).

## Git workflow
- `main` je vždy nasaditeľný, **nikdy necommituj priamo na `main`**. Každá požiadavka = vetva z `main`: `feat/…`, `fix/…`, `chore/…`, `docs/…`, `test/…`.
- Conventional Commits v angličtine. Hooky v `.githooks/` (`git config core.hooksPath .githooks`).
- Postup: vetva → implementácia + testy → dokumentácia (`docs/changes/`, `CHANGELOG.md`, `README.md`) → `composer check` zelený → zhrnutie → po súhlase merge `--no-ff` → `composer check` na `main` → push.
- Repozitár je **verejný**: žiadne tajomstvá, `.env`, kľúče, dumpy DB. API kľúče iba v premenných prostredia (napr. `STITCH_API_KEY`).
- **Zakázané:** `--no-verify`, `push --force` na `main`, mazanie/preskakovanie testov, znižovanie prahov kvality.

## Dokumentácia (súčasť každej zmeny)
- `docs/changes/RRRR-MM-DD-<vetva>.md` pre každú vetvu (Požiadavka, Riešenie, Dotknuté časti, Testy, Nasadenie, Odkazy).
- `CHANGELOG.md` sekcia `[Unreleased]`, `README.md` vždy aktuálny, ADR pri ťažko zmeniteľných rozhodnutiach.
- Každú požiadavku rob podľa skillu `new-change` (`.claude/skills/new-change/SKILL.md`).
- Skills projektu v `.claude/skills/<nazov>/SKILL.md` (nikdy iba v `~/.claude/`). Nový skill navrhni, vytvor až po súhlase.

## Nikdy
- nasadzovať bez výslovného „nasaď na produkciu“ (staging neexistuje)
- pushovať do `main` bez zeleného `composer check`
- mergnúť zmenu bez dokumentácie
- upravovať nasadené migrácie (vždy nová migrácia)
- pridať balík bez zdôvodnenia (účel, licencia, údržba) a bez súhlasu
- logovať osobné údaje
- mazať dáta (lokálne DB, súbory na serveri) bez pokynu

## Práca po fázach
Fázy podľa `docs/brief.md` (sekcia 11), upravené v `docs/decisions.md`: overenie hostingu a deploy až po schválení
lokálnej verzie. Po každom väčšom celku sa zastav, zhrň a počkaj na „pokračuj“.

## Praktické poznámky
- Web je one-pager (ADR 0007): menu = sekcie úvodnej stránky (`App\Support\HomeSections`). Samostatné stránky iba rezervácia, detail workshopu, právne.
- Ikony: `<x-site.icon name="…" />` (SVG v `resources/svg/icons`). `<x-icon>` patrí Filamentu (blade-icons).
- Tailwind 4: triedy použité v `@apply` iných tried definuj cez `@utility`, nie v `@layer components`.
- Lokálny admin: `admin@bezvahania.localhost` / `password` (seeder, iba `local`).

## Vzťah k Boost guidelines nižšie
Blok `<laravel-boost-guidelines>` generuje `php artisan boost:update`, ručne ho neupravuj. Pri konflikte platia pravidlá projektu vyššie:
- príkazy `php`, `composer`, `vendor/bin/*` z guidelines spúšťaj v kontajneri (`docker compose exec app …`),
- dokumentácia zmien je **povinná** (sekcia Dokumentácia), nie iba na požiadanie,
- nasadzuje sa na Websupport cez `scripts/deploy.sh`, nie cez Laravel Cloud.

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.4. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

# Pest

- This project uses Pest. Create tests with `php artisan make:test --pest {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.
- Do not delete tests or test files without approval. They are part of the application.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/pest` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

</laravel-boost-guidelines>
