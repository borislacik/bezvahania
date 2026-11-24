# Changelog

Všetky podstatné zmeny projektu. Formát podľa [Keep a Changelog](https://keepachangelog.com/sk/1.1.0/),
verzie podľa git tagov z deployov (`vRRRR.MM.DD-N`).

## [Unreleased]

### Pridané – web a administrácia
- Verejný web podľa dizajnu zo Stitchu: úvod, workshopy a ich detail, rezervácia, lektor, časté otázky, kontakt,
  právne stránky, chybové stránky 404/419/429/500/503 po slovensky.
- Rezervácia celého termínu (deň + doobeda 9 – 13 / poobede 15 – 19) v mesačnom kalendári (aktuálny a ďalší mesiac),
  okamžité potvrdenie, e-maily zákazníkovi a prevádzkovateľovi, ochrana pred dvojitou rezerváciou unikátnym indexom.
- Kontaktný formulár s uložením správy a e-mailom prevádzkovateľovi; honeypot a rate limiting na oboch formulároch.
- Administrácia (Filament 5) na subdoméne `admin.` s MFA: rezervácie (filtre, zrušenie s dôvodom, export CSV,
  telefonická rezervácia), kalendár dostupnosti, workshopy, lektori, FAQ, referencie, stránky a SEO, správy z webu,
  nastavenia kontaktu a prevádzkovateľa, nástenka. Príkaz `app:create-admin`.
- SEO editovateľné v admine (titulok, popis, obrázok pri zdieľaní), canonical, Open Graph, JSON-LD
  (LocalBusiness, Course, FAQPage), `sitemap.xml`, `robots.txt`, presmerovanie `www`.
- Automatická konverzia nahraných obrázkov na WebP; všetky obrázky webu vo WebP.
- Cookie lišta podľa EÚ, Google Analytics až po súhlase (Consent Mode v2).
- GDPR: anonymizácia rezervácií po 3 rokoch, mazanie správ po 12 mesiacoch, bezpečnostné hlavičky.
- Docker služby `queue` a `scheduler`, ukážkové dáta (`migrate --seed`).
- Testy: 114 Unit/Feature (pokrytie 97 %) a 8 browser testov (rezervácia, plný termín, admin, všetky stránky).

### Opravené
- Admin po chvíli odhlásil používateľa: middleware session a cookies sa pri Livewire požiadavkách spúšťali dvakrát.

### Zmenené
- Overenie hostingu a nasadenie sa presúvajú až po schválení lokálnej verzie (docs/decisions.md).

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
