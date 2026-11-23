# ADR 0003: Dve domény, jedna aplikácia

- Stav: prijaté (implementácia vo Fáze 2)
- Dátum: 2026-11-23

## Kontext
Verejný web beží na `bezvahania.sk`, administrácia na `admin.bezvahania.sk`. Obe pracujú s tými istými
dátami (kurzy, termíny, rezervácie).

## Rozhodnutie
- Jedna Laravel aplikácia, dva hostnamy z `.env`: `APP_DOMAIN`, `ADMIN_DOMAIN` → `config('app.domain')`,
  `config('app.admin_domain')`. Nikde natvrdo.
- Verejné routy v `Route::domain(config('app.domain'))`, Filament panel `->domain(config('app.admin_domain'))->path('')`.
- Na verejnej doméne nie je dostupný admin a naopak (overené testami). Admin posiela `X-Robots-Tag: noindex, nofollow`.
- Lokálne `bezvahania.localhost` a `admin.bezvahania.localhost` (`*.localhost` sa prekladá na loopback bez úpravy `hosts`).

## Dôsledky
- Jeden deploy, jedna databáza, zdieľané Actions pre web aj admin.
- Na Websupporte musia oba koreňové adresáre smerovať do toho istého `public/` (overí sa vo Fáze 2).

## Zvažované alternatívy
- **Admin na `/admin`** – jednoduchšie, ale zadanie chce subdoménu (oddelenie, `noindex`).
- **Dve aplikácie** – duplicita kódu a deployov.
