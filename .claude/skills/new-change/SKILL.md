---
name: new-change
description: "Celý postup jednej požiadavky v projekte bezvahania.sk: vetva z main → implementácia → testy → záznam v docs/changes/ → CHANGELOG → README → composer check → zhrnutie → merge a push až po súhlase. Použi vždy, keď používateľ zadá novú funkcionalitu, opravu, úpravu dokumentácie alebo inú zmenu v repozitári (feat, fix, chore, docs, test), aj keď to výslovne nenazve „zmena“."
---

# new-change – postup jednej požiadavky

Záväzné pravidlá sú v `CLAUDE.md` a `docs/brief.md`; pri rozpore má prednosť `docs/decisions.md`.
Tento skill ich iba skladá do poradia. S používateľom komunikuj po slovensky, kód a commity píš po anglicky.
Všetky príkazy projektu spúšťaj v Dockeri (`docker compose exec app …`), git na hoste.

## 0. Pred začatím
1. Over, že stack beží: `docker compose ps --status running --services` obsahuje `app` a `db`. Ak nie, `docker compose up -d`.
2. Over čistý strom a aktuálny `main`:
   ```bash
   git status --porcelain          # musí byť prázdne
   git switch main && git pull --ff-only
   ```
3. Ak požiadavka znamená **väčšie rozhodnutie** (nový balík, zmena architektúry, zmena DB schémy), najprv navrhni
   riešenie a v 2–3 vetách zdôvodni. Pri balíku uveď účel, licenciu a či je udržiavaný. Implementuj až po súhlase.
4. Ak niečo nevieš overiť (verzia, správanie hostingu, prístupové údaje), opýtaj sa. Nič si nevymýšľaj.

## 1. Vetva
Názov `<typ>/<kratky-popis>` v kebab-case, typ podľa Conventional Commits:
`feat/…` funkcionalita · `fix/…` oprava · `chore/…` údržba, nástroje · `docs/…` iba dokumentácia · `test/…` iba testy.
```bash
git switch -c feat/booking-calendar
```

## 2. Implementácia
- Dodrž štruktúru z `docs/architecture.md`: tenké controllery, logika v `App\Actions\…`, validácia vo Form Requests,
  stavy ako Enums s `label()` cez preklady, e-maily vo fronte.
- Žiadne texty natvrdo: všetko cez `__()` a `lang/sk/*.php` alebo `lang/sk.json`.
- Domény iba cez `config('app.domain')` / `config('app.admin_domain')`.
- Obrázky na webe iba WebP, nahraté obrázky sa konvertujú na WebP. SEO údaje editovateľné v admine.
- Osobné údaje nikdy do logov. Žiadne tajomstvá v kóde (repozitár je verejný).
- Nová migrácia namiesto úpravy nasadenej. Nové `.env` premenné hneď do `.env.example` s komentárom.
- Pri Laravel kóde aktivuj skill `laravel-best-practices`, pri Tailwinde `tailwindcss-development`.

## 3. Testy
Nová funkcionalita bez testov nie je hotová. Aktivuj skill `testing-best-practices`.
- `tests/Unit` – Actions, Enums, výpočty.
- `tests/Feature` – routy, validácia, autorizácia, oddelenie domén, e-maily (`Mail::fake`/`Notification::fake`), Filament.
- `tests/Browser` – pri každom UI toku (Pest browser plugin, `assertNoJavaScriptErrors`).
- Počas práce spúšťaj iba dotknuté testy: `docker compose exec app vendor/bin/pest tests/Feature/XyzTest.php`.
- Nikdy nemaž, nepreskakuj ani neoslabuj testy a prahy (pokrytie min. 80 %, Larastan level).

## 4. Dokumentácia (súčasť zmeny)
1. **Záznam o zmene** `docs/changes/RRRR-MM-DD-<nazov-vetvy-s-pomlckami>.md` (napr. `2026-11-24-feat-booking-calendar.md`)
   podľa šablóny nižšie.
2. **`CHANGELOG.md`** – položka v `## [Unreleased]` pod `### Pridané` / `### Zmenené` / `### Opravené` / `### Odstránené`.
3. **`README.md`** – vždy, keď sa zmenili príkazy, `.env`, setup, `composer.json`/`package.json`, `routes/`, `config/`,
   `scripts/`, Docker alebo zoznam skills. Ak sa README naozaj meniť nemusí, napíš do záznamu riadok
   `README: bez zmeny – <dôvod>`.
4. Dotknuté dokumenty v `docs/` (`architecture.md`, `features/…`, `hosting-websupport.md`, `deploy.md`), `CLAUDE.md`
   pri zmene pravidiel alebo príkazov, a skills v `.claude/skills/`, ak sa zmenil ich postup.
5. **ADR** v `docs/adr/NNNN-<nazov>.md` pri ťažko zmeniteľnom rozhodnutí (balík, DB štruktúra, deploy, lokalizácia).
6. Ak sa nejaký viackrokový postup zopakoval alebo je špecifický pre projekt, **navrhni nový skill** (názov, účel, kroky).
   Vytvor ho až po súhlase.

### Šablóna záznamu o zmene
```markdown
# <vetva> – <krátky popis>

## Požiadavka
Čo používateľ chcel, jeho slovami.

## Riešenie
Čo sa zmenilo a prečo takto. Zvažované alternatívy.

## Dotknuté časti
Modely, migrácie, routy, Filament resources, šablóny, konfigurácia, `.env` premenné.

## Testy
Aké testy pribudli alebo sa zmenili. Ako zmenu overiť ručne.

## Nasadenie
Migrácie, nové `.env` premenné, manuálne kroky vo WebAdmine, riziká a rollback.

## Odkazy
Súvisiace ADR a dokumenty v `docs/features/`.
```

## 5. Commit a `composer check`
```bash
git add -A
git commit -m "feat(booking): add monthly availability calendar"   # hooky spustia Pint, Prettier a kontrolu formátu
docker compose exec app composer check
```
- Commit správa po anglicky podľa Conventional Commits, na konci riadok `Co-Authored-By` podľa pokynov harnessu.
- `composer check` = lint, analýza, testy s pokrytím, build, browser testy, `docs:check`. Pri úspechu a čistom strome
  zapíše `.check-passed`. Ak niečo zlyhá, oprav príčinu, commitni a spusti znova.
- **Zakázané:** `--no-verify`, `--force` na `main`, obchádzanie hookov.

## 6. Zhrnutie pre používateľa (po slovensky)
- čo sa zmenilo a prečo,
- ako to otestovať ručne (URL na `bezvahania.localhost`, Mailpit…),
- výsledok `composer check` (počet testov, pokrytie),
- riziká a čo nie je overené,
- otázka, či môžeš mergnúť do `main`.

## 7. Merge a push – iba po výslovnom súhlase
```bash
git switch main && git pull --ff-only
git merge --no-ff <vetva>
docker compose exec app composer check    # musí prejsť aj na main
git push                                  # pre-push hook overí .check-passed
git branch -d <vetva>
```
Nasadenie na produkciu **nie je** súčasťou tohto skillu. Robí sa iba na výslovný pokyn „nasaď na produkciu“.
