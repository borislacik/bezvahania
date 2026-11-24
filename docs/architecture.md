# Architektúra

> Stav: web aj admin implementované lokálne (23. 11. 2026). Podrobnosti v [features/](features/README.md).
> Rozhodnutia k zadaniu: [decisions.md](decisions.md). ADR: [adr/](adr/).

## Prehľad
- Jedna Laravel 13 aplikácia, dva hostnamy: verejný web (`APP_DOMAIN`) a Filament admin (`ADMIN_DOMAIN`),
  pozri [ADR 0003](adr/0003-two-domains-one-app.md).
- Lokálne všetko v Dockeri, pozri [ADR 0002](adr/0002-docker-local-development.md).
- Produkcia: zdieľaný hosting Websupport, hotový balík z lokálneho buildu, pozri [ADR 0004](adr/0004-build-assets-locally.md).

## Štruktúra kódu
| Miesto | Účel |
|---|---|
| `app/Actions/…` | biznis logika (volá ju web aj Filament) |
| `app/Http/Controllers` | tenké controllery |
| `app/Http/Requests` | validácia (Form Requests) |
| `app/Enums` | stavy s `label()` cez preklady |
| `app/Support` | `Availability`, `Seo`, `WebpImage`, `UntranslatedTextScanner` |
| `app/Filament` | admin: resources, stránky (Dostupnosť, Nastavenia), widgety |
| `resources/views/components` | Blade komponenty (`layouts/public`, `site/*`, ikony `<x-site.icon>`) |
| `resources/views/pages` | stránky verejného webu |
| `lang/sk`, `lang/sk.json` | všetky texty UI |

## Lokalizácia
- `APP_LOCALE=sk`, `APP_FALLBACK_LOCALE=sk`, `APP_FAKER_LOCALE=sk_SK`, časové pásmo `Europe/Bratislava`.
- Slovenské preklady Laravelu z `laravel-lang/common` (`php artisan lang:update`).
- Test `tests/Feature/TranslationsTest.php` hľadá nepreložené texty v Blade šablónach.
- URL zatiaľ bez jazykového prefixu, routy sú pomenované (`route('home')`), aby sa dal neskôr pridať `/{locale}`.

## Dizajn
- Design tokeny (farby, písma, zaoblenie) sú na jednom mieste v `resources/css/app.css` (`@theme`).
  Hodnoty pochádzajú zo Stitch projektu „Mobile First Aid Workshops“.
- Písma Work Sans a Space Grotesk sa pri builde stiahnu a servírujú lokálne.

## Doménový model
| Model | Tabuľka | Popis |
|---|---|---|
| `Course` | `courses` | typ workshopu (3 typy), preložiteľné texty, cena, obrázok (WebP), SEO |
| `Booking` | `bookings` | rezervácia celého termínu: dátum + `TimeSlot`, workshop, kontakt, adresa, 1 – 4 osoby, stav, súhlas |
| `BlockedDay` | `blocked_days` | deň, keď lektor nie je k dispozícii |
| `TeamMember` | `team_members` | lektor (meno, funkcia, citát, text, foto) |
| `Faq`, `Testimonial` | `faqs`, `testimonials` | časté otázky a referencie |
| `Page` | `pages` | SEO a texty pevných stránok (kľúče v `Page::KEYS`) |
| `Setting` | `settings` | kontaktné údaje, prevádzkovateľ, pravidlá rezervácií (kľúč – hodnota, cache) |
| `ContactMessage` | `contact_messages` | správy z kontaktného formulára (mažú sa po 12 mesiacoch) |
| `User` | `users` | administrátor (MFA cez aplikáciu) |

Enums: `TimeSlot` (doobeda 9 – 13, poobede 15 – 19), `BookingStatus` (confirmed, completed, no_show, cancelled).

## Tok rezervácie
1. `BookingController@create` – `Availability::months()` pripraví kalendár (aktuálny + ďalší mesiac).
2. `StoreBookingRequest` – validácia, honeypot, súhlas; rate limit `bookings`.
3. `CreateBooking` – kontrola dostupnosti, vloženie v transakcii, unikátny `active_slot`
   ([ADR 0005](adr/0005-unique-slot-booking.md)), e-maily do fronty.
4. Presmerovanie na podpísanú adresu potvrdenia (`booking.show`).

## Verejné routy (`routes/web.php`, doména `APP_DOMAIN`)
Web je one-pager ([ADR 0007](adr/0007-one-page-website.md)): úvodná stránka `/` so sekciami `#workshopy`,
`#lektor`, `#otazky`, `#kontakt`, ďalej `/workshopy/{slug}` (detail), `/rezervacia` (GET/POST),
`/rezervacia/{číslo}` (podpísaná), `POST /kontakt`, `/ochrana-osobnych-udajov`, `/podmienky-rezervacie`, `/cookies`,
`/sitemap.xml`, `/robots.txt`. Bývalé `/workshopy`, `/o-lektorovi`, `/caste-otazky`, `/kontakt` (GET) presmerúvajú 301
na sekcie. Health check `/up`.

## Plánované úlohy (`routes/console.php`)
`model:prune` (správy z formulára), `app:anonymize-bookings` (rezervácie po 3 rokoch), na produkcii aj
`queue:work --stop-when-empty` každú minútu. Lokálne beží služba `scheduler` a `queue`.
