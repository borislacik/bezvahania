# Architektúra

> Stav: kostra (Fáza 1). Doménový model a tok rezervácie sa doplnia vo Fáze 3 a 4.
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
| `app/Support` | pomocné triedy (napr. `UntranslatedTextScanner`) |
| `resources/views/components` | znovupoužiteľné Blade komponenty (`layouts/public`) |
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

## Doménový model (návrh, potvrdí sa vo Fáze 3)
- **Course** (typ workshopu, 3 typy): preložiteľný názov a popis, cena (~200 €), poradie, aktívny, obrázok (WebP), SEO.
- **Dostupnosť**: kalendár s dvoma termínmi denne (9–13, 15–19), admin blokuje celé dni.
- **Booking**: dátum + termín, typ workshopu, kontaktná osoba, e-mail, telefón, adresa konania, počet osôb (1–4),
  súhlas so spracovaním údajov. Vytvorenie v transakcii so zámkom, jedna rezervácia na termín.
