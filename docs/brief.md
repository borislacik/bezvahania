# Projekt bezvahania.sk – zadanie pre Claude Code (setup projektu)

> Tento dokument je úvodné zadanie. Prečítaj ho celý ešte pred prvým príkazom. Postupuj po fázach (sekcia 11).
> Na konci každej fázy sa zastav, zhrň, čo si urobil, a počkaj, kým napíšem „pokračuj“.

---

## 0. Ako so mnou pracuješ

- So mnou komunikuj **po slovensky**. Kód, názvy tried, premenných, DB stĺpcov a commit správy píš **po anglicky**. Všetky texty, ktoré uvidí používateľ webu alebo adminu, sú **po slovensky** (cez prekladové súbory, nie natvrdo v kóde).
- Ak niečo nevieš overiť (verziu, prístupový údaj, správanie hostingu), **opýtaj sa**. Nič si nevymýšľaj, hlavne nie heslá, cesty na serveri a SMTP údaje.
- Bez môjho výslovného pokynu **nikdy** nenasadzuj na produkciu, nepushuj do `main` a nemaž dáta (lokálne DB, súbory na serveri).
- Pri každom väčšom rozhodnutí (nový balík, zmena architektúry, zmena DB schémy) najprv navrhni riešenie a v 2–3 vetách napíš prečo. Až potom ho implementuj.
- Pred inštaláciou balíkov over ich **aktuálne stabilné verzie**. Ak je dostupná novšia major verzia, než uvádza tabuľka v sekcii 2, opýtaj sa ma, ktorú použiť.

---

## 1. O projekte

**bezvahania.sk** je web pre kurzy prvej pomoci pre malé skupiny (**max. 4 osoby na termín**).

- **Verejný web** – `https://bezvahania.sk`
  - informácie: čo robíme, kto sme, ponuka kurzov, FAQ, kontakt, právne stránky
  - **rezervácia termínu kurzu** (bez online platieb, len rezervácia)
- **Administrácia** – `https://admin.bezvahania.sk` (Laravel + Filament)
  - správa kurzov, termínov a rezervácií, potvrdzovanie a rušenie rezervácií
  - správa obsahu webu, ktorý sa bude meniť (rozsah upresníme, viď sekcia 14)
- **Jazyk:** zatiaľ iba slovenčina, ale architektúra musí od začiatku umožniť pridať ďalšie jazyky bez prepisovania.
- **Hosting:** Websupport.sk, zdieľaný (mass) webhosting. Nie VPS. Z toho vychádzajú obmedzenia v sekcii 9.
- **Vývoj:** lokálne na Windows (cez WSL2, viď sekcia 3). Nasadenie je vždy z lokálneho stroja cez SSH, až po prejdení všetkých testov.

---

## 2. Technologický stack (záväzný)

| Oblasť | Technológia | Poznámka |
|---|---|---|
| Jazyk | PHP 8.4 | Rovnaká verzia lokálne aj na hostingu. V `composer.json` nastav `config.platform.php`. |
| Framework | Laravel 13.x | Čistá inštalácia **bez** starter kitu (verejný web nemá používateľské účty). |
| Admin | Filament 5.x | Panel beží na subdoméne `admin.` |
| Frontend | Blade komponenty + Tailwind CSS 4 (Vite) | Alpine.js iba tam, kde je interaktivita naozaj potrebná. Žiadne SPA. |
| Databáza | MariaDB 11.4 (alebo MySQL 8.4, podľa toho, čo ponúka hosting) | Lokálne použi rovnaký engine a verziu ako na produkcii. |
| Testy | Pest 4 + `pestphp/pest-plugin-browser` (Playwright) | Unit, Feature (integračné) a Browser (E2E) testy |
| Kvalita kódu | Laravel Pint, Larastan (PHPStan), Prettier (`prettier-plugin-blade`, `prettier-plugin-tailwindcss`) | |
| AI tooling | `laravel/boost` (dev) | MCP server a guidelines pre Claude Code |
| Lokálny e-mail | Mailpit | |
| Fronta / cache / session | `database` driver | Na zdieľanom hostingu nie je Redis ani supervisor. |

---

## 3. Vývojové prostredie (Windows)

Odporúčané nastavenie, ktoré najprv over a nastav v Fáze 0:

- **WSL2 + Ubuntu 24.04 LTS**. Projekt je uložený **vo WSL súborovom systéme** (napr. `~/projects/bezvahania`), **nie** v `/mnt/c/...` (výkon a oprávnenia).
- Claude Code beží **vo WSL**. Editor: VS Code s rozšírením WSL (alebo PhpStorm s WSL interpreterom).
- Vo WSL nainštaluj: PHP 8.4 CLI s rozšíreniami (`intl`, `mbstring`, `pdo_mysql`, `zip`, `gd`, `bcmath`, `xml`, `curl`, `pcov`), Composer 2, Node.js LTS + npm, MariaDB server, Mailpit, Git, `rsync`, `openssh-client`, závislosti Playwrightu (`npx playwright install --with-deps chromium`).
- Dôvod: produkcia beží na Linuxe. Deploy skripty (bash, ssh, tar) a Playwright fungujú vo WSL bez problémov.
- `.gitattributes` s `* text=auto eol=lf`. Žiadne CRLF v repozitári.
- **Lokálne domény:** `bezvahania.localhost` a `admin.bezvahania.localhost`. Domény `*.localhost` sa v prehliadačoch prekladajú na loopback, takže netreba upravovať `hosts`. Over, že to funguje z Windows prehliadača aj z Playwrightu. Ak nie, navrhni alternatívu.
- Lokálne databázy: `bezvahania` (vývoj) a `bezvahania_testing` (testy).

---

## 4. Architektúra aplikácie

### 4.1 Dve domény, jedna aplikácia
- Jedna Laravel aplikácia, dva hostnamy. Oba v `.env`: `APP_DOMAIN=bezvahania.sk`, `ADMIN_DOMAIN=admin.bezvahania.sk`. Do kódu sa načítajú cez `config/app.php`, nikde nie sú natvrdo.
- Verejné routy sú ohraničené `Route::domain(config('app.domain'))`.
- Filament panel: `->domain(config('app.admin_domain'))` a `->path('')`. Admin beží na koreni subdomény, nie na `/admin`.
- Na verejnej doméne nesmie byť dostupný admin a na admin doméne nesmú byť dostupné verejné stránky. Over to testami.
- Admin doména posiela hlavičku `X-Robots-Tag: noindex, nofollow`.
- Redirect `www.bezvahania.sk` → `bezvahania.sk`. Všetko ide cez HTTPS.

### 4.2 Admin a prístup
- Bez registrácie. Administrátorov vytvára artisan príkaz `app:create-admin`.
- Zapnuté MFA cez vstavanú Filament app authentication (TOTP).
- Autorizácia cez Policies. Roly zatiaľ nie sú, viď otázka v sekcii 14.

### 4.3 Štruktúra kódu
- Tenké controllery. Biznis logika je v **Action** triedach (napr. `App\Actions\Bookings\CreateBooking`). Volá ich verejný web aj Filament.
- Validácia vo Form Requests. Stavy sú PHP **Enums** (s `label()` cez preklady). E-maily sú Notifications alebo Mailables vo fronte.
- Blade: znovupoužiteľné komponenty v `resources/views/components`, žiadna logika vo view.
- Časové pásmo `Europe/Bratislava`. Dátumy a čísla sa formátujú podľa slovenskej lokalizácie.

### 4.4 Lokalizácia a pripravenosť na ďalšie jazyky
- `APP_LOCALE=sk`, `APP_FALLBACK_LOCALE=sk`, `APP_FAKER_LOCALE=sk_SK`. Slovenské preklady Laravelu (`lang/sk`) aj Filamentu.
- Všetky UI texty idú cez `__()` / `lang/sk/*.php`. Pridaj test alebo statickú kontrolu, ktorá nájde nepreložené texty v Blade, ak sa to dá rozumne zautomatizovať.
- URL zatiaľ bez jazykového prefixu. Routy však pomenuj a štruktúruj tak, aby sa neskôr dal pridať prefix `/{locale}` (napr. `/en/...`) bez rozbitia slovenských URL.
- Preklad obsahu v DB (názvy a popisy kurzov a pod.) závisí od odpovede v sekcii 14. Ak budú cudzie jazyky „isto“, použi od začiatku `spatie/laravel-translatable` (JSON stĺpce).

### 4.5 Bezpečnosť
- Rate limiting na rezervačnom a kontaktnom formulári, honeypot proti spamu (Cloudflare Turnstile iba po dohode).
- Bezpečnostné hlavičky (CSP aspoň v report-only režime, HSTS, `X-Content-Type-Options`, `Referrer-Policy`).
- `APP_DEBUG=false` na produkcii. Chybové stránky 404, 419, 500 a 503 sú po slovensky.
- Osobné údaje sa nikdy nezapisujú do logov.

---

## 5. Doménový model (MVP)

Toto je návrh. Pred implementáciou ho so mnou potvrď a zapracuj odpovede zo sekcie 14.

- **Course** (kurz): názov, slug, krátky a dlhý popis, dĺžka, informatívna cena (bez platby), predvolená kapacita (max. 4), aktívny/neaktívny, poradie, obrázok.
- **CourseSession** (termín): kurz, začiatok, koniec, miesto, kapacita (1–4), stav (`open`, `full`, `cancelled`, `completed`), poznámka pre admina. Inštruktor je voliteľný.
- **Booking** (rezervácia): termín, meno a priezvisko kontaktnej osoby, e-mail, telefón, počet účastníkov (1 až počet voľných miest), poznámka, stav (`pending`, `confirmed`, `cancelled`, `attended`, `no_show`), čas a verzia súhlasu so spracovaním osobných údajov, token na zrušenie (podpísaná URL), IP adresa (ak to GDPR politika dovolí).
- *(voliteľné podľa odpovedí)* **Participant** (mená jednotlivých účastníkov), **TeamMember**, **Faq**, **Page**, **ContactMessage**.

**Kritické pravidlá:**
- **Nesmie dôjsť k prekročeniu kapacity.** Rezervácia sa vytvára v DB transakcii so zámkom `lockForUpdate()` na termíne. Okrajové prípady pokry testami: posledné voľné miesto, viac miest, než je voľných, zrušený termín, termín v minulosti.
- Pri zmene stavu rezervácie (potvrdenie alebo zrušenie) sa automaticky odošle e-mail.
- Osobné údaje majú definovanú dobu uchovávania. Mazanie alebo anonymizáciu po uplynutí doby rieši plánovaná úloha (`model:prune`).

**Admin (Filament):**
- Resources: Kurzy, Termíny (s prehľadom obsadenosti), Rezervácie (filtre podľa dátumu, stavu a kurzu; akcie Potvrdiť, Zrušiť, Označiť účasť; export CSV), Administrátori.
- Dashboard: najbližšie termíny s obsadenosťou, nové nepotvrdené rezervácie.
- *(voliteľné)* kalendárový pohľad na termíny.

**E-maily (po slovensky, Markdown šablóny, vo fronte):**
- Zákazníkovi: rezervácia prijatá, rezervácia potvrdená, rezervácia zrušená (s dôvodom), *(voliteľne)* pripomienka deň pred kurzom.
- Administrátorovi: nová rezervácia.

---

## 6. Verejný web

Stránky:
- Domov
- Kurzy
- Detail kurzu (voľné termíny a rezervačný formulár)
- Potvrdenie rezervácie
- Zrušenie rezervácie cez odkaz z e-mailu
- O nás
- Kontakt (údaje a formulár)
- FAQ
- Ochrana osobných údajov
- Podmienky rezervácie a storna
- Cookies, ak budú potrebné

Požiadavky:
- Mobile-first, responzívne, prístupnosť **WCAG 2.1 AA** (kontrasty, labely formulárov, ovládanie klávesnicou, `lang="sk"`).
- Výkon: Lighthouse ≥ 90 vo všetkých kategóriách na mobile. Obrázky v moderných formátoch s `width`/`height` a lazy-load.
- SEO: unikátne `title` a `meta description`, Open Graph, canonical, `sitemap.xml`, `robots.txt`, schema.org (`LocalBusiness`, `Course` / `Event` pre termíny).
- Žiadne cookies okrem technicky nevyhnutných, kým sa nedohodneme na analytike.
- Dizajn: čistý, dôveryhodný, zdravotnícky ladený. Farby, logo a fonty doplním (sekcia 14). Dovtedy použi neutrálne placeholdery definované cez Tailwind theme (CSS premenné), aby sa dali vymeniť na jednom mieste.

---

## 7. Testovanie a kvalita (povinné)

### 7.1 Druhy testov
- `tests/Unit` – Actions, Enums, pomocné triedy, výpočty kapacity.
- `tests/Feature` – každá route, validácie, autorizácia, oddelenie domén, e-maily (`Mail::fake` / `Notification::fake`), plánované úlohy, **Filament resources** (Livewire testy: zoznam, vytvorenie, úprava, akcie).
- `tests/Browser` – Pest 4 browser testy (Playwright). Minimálne tieto scenáre:
  1. Návštevník vyberie kurz → termín → vyplní formulár → vidí potvrdenie. V DB existuje rezervácia a e-mail je zaradený do fronty.
  2. Plný termín sa nedá rezervovať.
  3. Admin sa prihlási na admin doméne → potvrdí rezerváciu → zmení sa stav.
  4. Smoke test všetkých verejných stránok: HTTP 200, žiadne JS chyby (`assertNoJavascriptErrors`).
  - Na testy admin subdomény použi podporu subdomén v Pest browser testing (nastavenie hostu podľa dokumentácie Pestu).
- Testy bežia proti **MariaDB** (`bezvahania_testing`), nie SQLite, aby sa zhodovali s produkciou.

### 7.2 Brány kvality
- Pint (preset `laravel`), Larastan **level 6** na začiatok s cieľom zvýšiť na 8, Prettier pre Blade, JS a CSS.
- Pokrytie kódu (pcov) **min. 80 %** riadkov. Hranicu nesmieš znižovať bez môjho súhlasu.
- Composer skripty:
  - `composer lint` – Pint `--test` a Prettier `--check`
  - `composer analyse` – PHPStan
  - `composer test` – Unit + Feature, paralelne, s `--coverage --min=80`
  - `composer test:browser` – Browser testy
  - `composer docs:check` – kontrola dokumentácie podľa sekcie 10.4
  - `composer check` – všetko vyššie + `npm run build`. **Iba ak všetko prejde a pracovný strom je čistý**, zapíše SHA aktuálneho commitu do `.check-passed` (súbor je v `.gitignore`). Pushovanie do `main` aj deploy tento súbor overujú.
- `tests/Browser/Screenshots` je v `.gitignore`.

---

## 8. Git workflow

- `main` je vždy nasaditeľný. **Na `main` sa nikdy necommituje priamo.**
- **Každá požiadavka** = nová vetva z aktuálneho `main`: `feat/…`, `fix/…`, `chore/…`, `docs/…`, `test/…`.
- Commit správy sú v angličtine podľa **Conventional Commits**.
- Postup pre každú zmenu:
  1. Vetva.
  2. Implementácia a testy (nová funkcionalita bez testov sa nepovažuje za hotovú).
  3. Dokumentácia zmeny podľa sekcie 10 (záznam v `docs/changes/`, `CHANGELOG.md`, aktualizovaný `README.md` a ďalšie dotknuté dokumenty).
  4. `composer check` je zelený.
  5. Zhrnutie pre mňa.
  6. Po mojom súhlase merge do `main` (`--no-ff`).
  7. `composer check` na `main`.
  8. Push.
- Git hooky v `.githooks/` (`git config core.hooksPath .githooks`):
  - `pre-commit`: Pint na staged PHP súboroch a Prettier na staged súboroch.
  - `commit-msg`: kontrola formátu Conventional Commits.
  - `pre-push`: push do `main` je zablokovaný, ak `.check-passed` neobsahuje SHA pushovaného commitu.
- **Zakázané:** `--no-verify`, `git push --force` na `main`, preskakovanie alebo mazanie testov, aby check prešiel.
- Remote repozitár: *(doplním – sekcia 14)*. Tagy pri deployi: `vRRRR.MM.DD-N`.

---

## 9. Hosting Websupport a nasadenie

### 9.1 Čo vieme z dokumentácie Websupportu (na reálnom hostingu treba overiť)
- SSH je dostupné na každom webhostingu. Štandardný shell je časovo obmedzený (1 hodina, 256 MB RAM). **Máme zakúpený trvalý SSH prístup (1 GB RAM)**, takže deploy môže bežať kedykoľvek bez ručnej aktivácie shellu vo WebAdmine.
- PHP 8.2–8.5 je štandardne k dispozícii. Databázy: MariaDB 11.4, MySQL 8.4, PostgreSQL 14. `memory_limit` 512 MB.
- Koreňový adresár domény aj subdomén sa nastavuje vo WebAdmine (Pokročilá konfigurácia → Web → Služby → Zobrazenie webového obsahu → Vlastný koreňový adresár). Predvolene je to `domena.sk/web` a pre subdomény `domena.sk/sub/<subdoména>`.
- Podľa návodu Websupportu pre Laravel musí koreňový adresár smerovať do `public`. **`php artisan storage:link` nefunguje**, symlink treba vytvoriť ručne a **relatívne** (`ln -s ../storage/app/public storage`). Dôvod: shell konzola a webový server vidia **rozdielne cesty**.
- Cron sa nastavuje vo WebAdmine a spúšťa **PHP skript alebo URL**, nie ľubovoľný shell príkaz. (Toto vychádza z dokumentácie Websupportu pre inú krajinu, pre .sk to treba overiť.)
- Node.js na serveri nepredpokladáme. **Assety sa buildujú lokálne** a nahrávajú hotové. Rovnako `vendor/`, aby bol nasadený presne ten build, ktorý prešiel testami, a server nepotreboval prístup na Packagist ani GitHub.

### 9.2 Overenie hostingu (Fáza 2, ešte pred písaním deploy skriptu)
Spolu so mnou over na reálnom hostingu a výsledky zapíš do `docs/hosting-websupport.md`:
1. Verzia PHP pre web vs. pre CLI v SSH a správna cesta k binárke (napr. `php8.4`). Musia sa zhodovať.
2. Dostupné PHP rozšírenia (najmä `intl`, `gd`/`imagick`, `zip`, `bcmath`, `pdo_mysql`, `fileinfo`).
3. **Reálne cesty:** výstup `__DIR__` z dočasného PHP súboru volaného cez web vs. `pwd -P` v SSH.
   - Ak sa líšia, **nesmieš** spúšťať `config:cache` ani `view:cache` cez SSH, pretože do cache by sa zapísali zlé absolútne cesty. Navrhni riešenie (napr. bez config cache alebo cache generovaná cez HTTP endpoint chránený tokenom).
   - Po overení dočasný súbor zmaž.
4. Či webserver akceptuje koreňový adresár cez symlink (`current/public`) a relatívne symlinky.
5. Či funguje cron wrapper (sekcia 9.5) a s akou PHP verziou beží.
6. Pripojenie k DB (host, port), SMTP odchod pošty, SSL certifikáty pre `bezvahania.sk`, `www.` aj `admin.`.
7. Či je v shelli dostupný `tar`, `gzip`, `mysqldump`.

### 9.3 Cieľová štruktúra na serveri (ak overenie potvrdí, že symlinky fungujú)
```
<koreň hostingu pre bezvahania.sk>/
├── releases/
│   ├── 20261123-153000_ab12cd3/
│   └── ...                      (uchovávame posledných 5)
├── shared/
│   ├── .env                     (iba na serveri, nikdy v gite ani v deploy balíku)
│   └── storage/
├── backups/                     (dump DB pred každou migráciou, posledných 10)
└── current -> releases/20261123-153000_ab12cd3   (RELATÍVNY symlink)
```
Koreňový adresár `bezvahania.sk` aj `admin.bezvahania.sk` vo WebAdmine smeruje na `current/public`.
**Fallback:** ak symlinkovaný root nefunguje, navrhni in-place deploy s `php artisan down` / `up` a postupom, ktorý pri chybe nenechá web rozbitý.

### 9.4 Deploy skript `scripts/deploy.sh` (spúšťa sa z WSL)
Parametre: `staging` | `production`, voliteľne `--dry-run`. SSH alias v `~/.ssh/config` (`bezvahania-staging`, `bezvahania-prod`), prihlásenie iba kľúčom.

1. **Kontroly:** vetva `main`, čistý pracovný strom, `main` = `origin/main`, `.check-passed` = `HEAD`. Inak skript skončí s chybou.
2. **Build** do dočasného adresára z `git archive HEAD`: `composer install --no-dev --optimize-autoloader`, `npm ci && npm run build`. Odstráň `node_modules`, `tests` a dev súbory. Zbal do `tar.gz`.
3. Overenie SSH pripojenia (`ssh -o BatchMode=yes -o ConnectTimeout=10`). Ak zlyhá, skript skončí so zrozumiteľnou chybou (kľúč, alias, dostupnosť servera). Nič sa nezmení.
4. Upload balíka (`scp`), rozbalenie do `releases/<timestamp>_<sha>`.
5. Relatívne symlinky na `shared/.env` a `shared/storage`, `public/storage`.
6. Záloha DB (`mysqldump`) do `backups/`.
7. `php artisan migrate --force` (správna PHP binárka podľa 9.2). Optimalizácie iba v rozsahu, ktorý 9.2 potvrdí ako bezpečný.
8. Atomické prepnutie `current` (vytvor dočasný symlink a premenuj ho).
9. **Smoke test:** `https://<doména>/up` vráti 200 a admin login stránka vráti 200. Ak zlyhá, automatický rollback na predchádzajúci release.
10. Upratanie starých releasov a záloh. Pri produkčnom deployi git tag, presun sekcie `[Unreleased]` v `CHANGELOG.md` pod nový tag a záznam do `docs/deployments.md`. Tieto zmeny dokumentácie commitne skript ako `chore(release): <tag>` priamo na `main`. Je to **jediná povolená výnimka** z pravidla „nič priamo na `main`“: commit smie meniť iba `CHANGELOG.md` a `docs/deployments.md`. `pre-push` hook ho pustí, ak sa od commitu v `.check-passed` zmenili iba tieto dva súbory.

K tomu `scripts/rollback.sh <staging|production>` (prepnutie na predchádzajúci release; migrácie sa nevracajú automaticky, iba so mnou).
Krok-za-krokom návod pre človeka napíš do `docs/deploy.md`.

### 9.5 Cron a fronta
- `cron/scheduler.php` (mimo `public/`): nabootuje Laravel a spustí `schedule:run`. Vo WebAdmine ho nastavíme na každú minútu.
- V scheduleri:
  - `queue:work --stop-when-empty --max-time=50`
  - `model:prune` (GDPR mazanie)
  - čistenie starých záloh
  - pripomienky termínov (ak budú)
  - Všetko s `withoutOverlapping()`.

### 9.6 Manuálne kroky vo WebAdmine (pripravíš mi checklist v `docs/hosting-websupport.md`)
- PHP verzia pre doménu aj subdomény.
- Vytvorenie DB a používateľa (staging aj produkcia).
- Koreňové adresáre.
- SSL certifikáty.
- SSH kľúč.
- `shared/.env`.
- Cron.
- E-mailová schránka a SMTP.
- DNS záznamy SPF, DKIM a DMARC.
- Subdoména `staging.` s HTTP Basic Auth a `noindex`.

---

## 10. Dokumentácia a skills v repozitári (povinné)

**Pravidlo:** všetok vývoj a každá zmena sa dokumentuje v repozitári ako Markdown súbory. Dokumentácia je súčasťou zmeny, nie úloha na neskôr. Zmena bez dokumentácie nie je hotová a nesmie sa mergnúť do `main`.

### 10.1 Štruktúra dokumentácie
```
README.md                  vždy aktuálny vstupný bod projektu (viď 10.2)
CLAUDE.md                  pravidlá pre Claude Code (cca do 150 riadkov)
CHANGELOG.md               súhrn zmien podľa „Keep a Changelog“, sekcia [Unreleased] + verzie podľa git tagov z deployov
docs/
├── architecture.md        architektúra, doménový model, tok rezervácie
├── hosting-websupport.md  zistenia z hostingu, checklist WebAdminu
├── deploy.md              postup nasadenia a rollbacku pre človeka
├── deployments.md         záznam každého nasadenia (dátum, prostredie, tag, poznámky)
├── changes/               jeden súbor na každú vetvu / požiadavku
│   └── RRRR-MM-DD-<nazov-vetvy>.md
├── adr/                   Architecture Decision Records pri zásadných rozhodnutiach
│   └── NNNN-<nazov>.md
└── features/              dokumentácia funkcionalít (rezervácie, e-maily, admin, …)
```

**Záznam o zmene** (`docs/changes/RRRR-MM-DD-<nazov-vetvy>.md`) obsahuje:
- **Požiadavka:** čo som chcel, mojimi slovami
- **Riešenie:** čo sa zmenilo a prečo takto (alternatívy, ak boli)
- **Dotknuté časti:** modely, migrácie, routy, Filament resources, šablóny, konfigurácia, `.env` premenné
- **Testy:** aké testy pribudli alebo sa zmenili a ako zmenu overiť ručne
- **Nasadenie:** migrácie, nové `.env` premenné, manuálne kroky vo WebAdmine, riziká a rollback
- **Odkazy:** súvisiace ADR, dokumenty vo `docs/features/`

**ADR** píš pri rozhodnutiach, ktoré sa ťažko menia: voľba balíka, štruktúra DB, stratégia deployu, prístup k lokalizácii. Formát: Kontext → Rozhodnutie → Dôsledky → Zvažované alternatívy. Prvé ADR vytvor hneď vo Fáze 1 (stack, WSL, dve domény v jednej aplikácii, build assetov lokálne).

### 10.2 README.md je vždy aktuálny
`README.md` sa aktualizuje **v tej istej vetve** ako zmena, ktorá ho ovplyvní. Musí vždy obsahovať:
- čo projekt je a odkazy na produkciu, staging a admin
- požiadavky a rýchly štart (WSL setup, inštalácia, `.env`, DB, Mailpit, lokálne domény)
- všetky príkazy, ktoré vývojár potrebuje (spustenie, testy, `composer check`, deploy, rollback, vytvorenie admina)
- prehľad `.env` premenných (alebo odkaz na `.env.example` s vysvetlivkami)
- git workflow v skratke
- zoznam projektových skills (10.3) s jednou vetou, na čo slúžia
- rozcestník na `docs/`

Ak sa README a skutočnosť rozchádzajú, je to chyba, ktorú opravíš hneď, aj keď nesúvisí s aktuálnou úlohou (v samostatnom commite `docs:`).

### 10.3 Claude Code skills
- **Navrhuj skills tam, kde to dáva zmysel:** keď sa nejaký viackrokový postup zopakuje, alebo keď je postup na hosťovanie či projekt natoľko špecifický, že ho budúca session nebude poznať. Navrhni názov, účel a kroky. Vytvor ho až po mojom súhlase.
- Skills sa ukladajú **v repozitári** v `.claude/skills/<nazov-skillu>/SKILL.md` (frontmatter `name` a `description`, ktorá jasne hovorí, kedy sa má skill použiť). Pomocné skripty sú v rovnakom priečinku. Commitujú sa spolu s kódom.
- Skill musí byť v súlade s `CLAUDE.md` a `docs/`. Ak sa postup zmení, aktualizuj skill v tej istej vetve.
- Kandidáti na skills (navrhni ich, keď na ne príde rad):
  - `new-change` – celý postup požiadavky: vetva → implementácia → testy → záznam v `docs/changes/` → CHANGELOG → README → `composer check` → zhrnutie
  - `deploy` – nasadenie na staging/produkciu podľa `docs/deploy.md` vrátane kontrol a zápisu do `docs/deployments.md`
  - `rollback` – bezpečný návrat na predchádzajúci release
  - `filament-resource` – nový Filament resource podľa konvencií projektu vrátane testov a prekladov
  - `add-translations` – pridanie alebo kontrola slovenských prekladov (neskôr ďalší jazyk)
  - `db-migration` – nová migrácia, factory, seeder a testy; pravidlo „nasadené migrácie sa nemenia“
  - `hosting-check` – overenie stavu hostingu (PHP verzia, cesty, cron, SSL) podľa `docs/hosting-websupport.md`

### 10.4 Kontrola dokumentácie (`composer docs:check`)
Jednoduchý skript (súčasť `composer check`), ktorý na vetve porovná zmeny voči `main`:
- **Chyba:** vetva nemá nový alebo upravený súbor v `docs/changes/`.
- **Chyba:** zmenili sa kódové súbory, ale `CHANGELOG.md` (sekcia `[Unreleased]`) nie.
- **Chyba:** zmenil sa `composer.json` (skripty, balíky), `package.json`, `.env.example`, `scripts/`, `routes/` alebo `config/`, ale `README.md` nie. Výnimka je možná iba výslovným riadkom `README: bez zmeny – <dôvod>` v zázname v `docs/changes/`.
- **Chyba:** existuje súbor v `.claude/skills/` bez platného frontmatteru (`name`, `description`).
- **Chyba:** nefunkčné relatívne odkazy medzi `.md` súbormi.
- Na `main` (po merge) kontrola prechádza, kontroluje sa iba platnosť odkazov a skills.

Ďalšie pravidlá:
- `CLAUDE.md` aktualizuj pri každej zmene pravidiel alebo príkazov.
- `.env.example` je vždy aktuálny a bez reálnych tajomstiev.
- Dokumentácia je **po slovensky**. Názvy súborov, kód a príkazy v nej zostávajú tak, ako sú v repozitári.

---

## 11. Fázy

Každá fáza má vlastnú vetvu, testy, zelený `composer check`, zhrnutie a čaká na moje „pokračuj“.

- **Fáza 0 – Prostredie a otázky:** over a nastav WSL prostredie (sekcia 3), vypíš verzie nástrojov. Polož mi otázky zo sekcie 14, na ktoré ešte nemám odpoveď.
- **Fáza 1 – Kostra a kvalita:**
  - Laravel 13 a nástroje zo sekcie 2, slovenská lokalizácia
  - `.gitattributes`, `.editorconfig`, git hooky, composer skripty vrátane `docs:check`
  - kostra dokumentácie zo sekcie 10.1: `README.md`, `CLAUDE.md`, `CHANGELOG.md`, `docs/`, prvé ADR
  - jeden ukážkový test z každého druhu, Laravel Boost
  - návrh skillu `new-change` (po mojom súhlase ho vytvor v `.claude/skills/`)
- **Fáza 2 – Dve domény, admin a nasadenie kostry:**
  - oddelenie domén a Filament panel na `admin.` (login, MFA, `app:create-admin`) s testami
  - overenie hostingu (9.2), deploy a rollback skripty, cron wrapper
  - **prvý deploy kostry na staging, potom na produkciu** (iba na môj pokyn)
  - Riziká hostingu odstraňujeme hneď na začiatku, nie až na konci.
- **Fáza 3 – Doménový model a admin:** migrácie, modely, Enums, Actions, Policies, Filament resources a dashboard, factories a seedery so slovenskými dátami.
- **Fáza 4 – Verejný web a rezervácie:** stránky, rezervačný proces, e-maily, zrušenie cez odkaz, ochrana proti spamu, browser testy hlavných scenárov.
- **Fáza 5 – Dokončenie:**
  - SEO, prístupnosť, výkon, GDPR (texty dodám), chybové stránky
  - launch checklist a finálny deploy

---

## 12. Definition of Done (pre každú zmenu)

- [ ] Zmena je vo vlastnej vetve s Conventional Commits.
- [ ] Nové správanie je pokryté testami (unit/feature, pri UI toku aj browser test).
- [ ] `composer check` je zelený a `.check-passed` sa zhoduje s `HEAD`.
- [ ] Žiadne natvrdo zapísané texty v UI, všetko je v `lang/sk`.
- [ ] Žiadne tajomstvá v kóde ani v gite. `.env.example` je aktualizovaný.
- [ ] Existuje záznam v `docs/changes/` a položka v `CHANGELOG.md` (`[Unreleased]`).
- [ ] `README.md` zodpovedá skutočnosti (príkazy, `.env`, setup, zoznam skills).
- [ ] Dotknuté dokumenty v `docs/`, `CLAUDE.md` a skills v `.claude/skills/` sú aktualizované. Pri zásadnom rozhodnutí pribudlo ADR.
- [ ] Ak sa postup zopakoval alebo je špecifický pre projekt, navrhol si skill.
- [ ] Zhrnutie pre mňa: čo sa zmenilo, ako to otestovať, riziká.

## 13. Nikdy

- nasadzovať bez môjho výslovného „nasaď na staging/produkciu“
- pushovať do `main` bez zeleného `composer check`, používať `--no-verify`
- mergnúť zmenu bez dokumentácie, alebo nechať `README.md` neaktuálny
- vytvárať alebo meniť skills mimo repozitára (napr. iba v `~/.claude/`), ich miesto je `.claude/skills/`
- upravovať migrácie, ktoré už boli nasadené (vždy nová migrácia)
- commitovať `.env`, kľúče, heslá, dumpy DB
- pridávať balík bez zdôvodnenia (účel, licencia, či je udržiavaný)
- vypínať, preskakovať alebo oslabovať testy či prahy kvality, aby niečo prešlo
- logovať osobné údaje

---

## 14. Otvorené otázky (predvolené hodnoty platia, kým neodpoviem inak)

| # | Otázka | Predvolená hodnota |
|---|---|---|
| 1 | ~~Trvalý SSH prístup?~~ **Vyriešené:** trvalý SSH (1 GB RAM) je zakúpený. | – |
| 2 | Chceme staging `staging.bezvahania.sk` na tom istom hostingu? | Áno (Basic Auth, `noindex`, samostatná DB) |
| 3 | Kde bude git repozitár? Chceme navyše CI (napr. GitHub Actions) ako druhú poistku? | GitHub, súkromný repozitár. CI zatiaľ nie, testy bežia lokálne. |
| 4 | Model rezervácie: rezervuje sa **počet miest** v termíne (viac rezervácií na 1 termín), alebo **celý termín** pre jednu skupinu? Môže zákazník navrhnúť vlastný termín? | Počet miest (1–4) v termínoch, ktoré vypíše admin. Vlastný termín iba cez kontaktný formulár. |
| 5 | Potvrdzovanie: automaticky, alebo až po schválení adminom? Overenie e-mailu (double opt-in)? | Stav `pending` → potvrdí admin. Bez double opt-in. |
| 6 | Môže zákazník rezerváciu zrušiť sám? Do kedy pred kurzom? | Áno, cez odkaz v e-maile, najneskôr 48 h pred začiatkom. |
| 7 | Pripomienkový e-mail pred kurzom? | Áno, 24 h pred začiatkom |
| 8 | Aké údaje zbierame? Mená všetkých účastníkov? Firemné údaje (IČO) pre fakturáciu mimo webu? | Kontaktná osoba + počet účastníkov. Mená účastníkov nepovinne. |
| 9 | Vydávate osvedčenia o absolvovaní (PDF)? Potrebujete evidenciu kvôli akreditácii (napr. kurz pre vodičský preukaz)? | Zatiaľ nie |
| 10 | Miesto konania: stále miesto, alebo aj u klienta (firmy)? | Stále miesto, adresa sa nastavuje pri termíne. |
| 11 | Zobrazujeme ceny? Ako sa platí (na mieste, faktúrou)? | Informatívna cena + text „platba na mieste“ |
| 12 | Čo má byť editovateľné v admine (kurzy, termíny, tím, FAQ, referencie, texty stránok, obrázky)? | Kurzy, termíny, tím, FAQ. Ostatné texty v Blade cez prekladové súbory. |
| 13 | Koľko bude administrátorov? Treba roly (majiteľ vs. inštruktor)? | 1–3 administrátori, bez rolí |
| 14 | Odosielateľ e-mailov a príjemca notifikácií. Schránka na Websupporte? | `rezervacie@bezvahania.sk`, SMTP Websupport |
| 15 | Prevádzkovateľ (s.r.o./živnosť, IČO) pre právne texty. Kto dodá texty GDPR a podmienok? Doba uchovávania údajov? | Texty dodá prevádzkovateľ. Rezervácie sa anonymizujú 3 roky po termíne. |
| 16 | Analytika (GA4, Plausible, žiadna)? Od toho závisí cookie lišta. | Žiadna, teda bez cookie lišty |
| 17 | Ďalšie jazyky: ktoré a kedy? Musí sa prekladať aj obsah kurzov v DB? | Neskôr angličtina. Od začiatku `spatie/laravel-translatable` na obsah kurzov. |
| 18 | Dizajn: logo, farby, fonty, fotky, referenčné weby? | Neutrálne placeholdery |
| 19 | Je doména `bezvahania.sk` už registrovaná a smerovaná na hosting Websupport? | Áno |
| 20 | Monitoring chýb: stačia logy + e-mail pri kritickej chybe, alebo služba (Sentry, Flare)? | Logy + e-mail administrátorovi pri výnimke (s limitom počtu správ) |
