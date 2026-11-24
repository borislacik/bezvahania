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
- Slovenské písmená s diakritikou (č, š, ľ, ť, ž, ň…) sa kreslili záložným písmom; písma Work Sans a Space Grotesk sa teraz načítavajú aj so sadou latin-ext.

### Zmenené – štruktúra webu
- Piliere so spomaleným skrolovaním: na desktope sa sekcia posúva pomalšie a piliere aj ikony v tmavom paneli postupne pribúdajú; sekcia „Ako prebieha workshop priamo u vás?“ nahradená prvou častou otázkou „Ako prebieha workshop?“.
- Efekt pilierov začína skôr (vrch sekcie v 30 % výšky obrazovky); tmavý pruh „Kritický čas“ nad nimi sa posúva rovnako pomaly.
- Piliere bez označenia „Pilier 01 – 03“, iba nadpis a text; nové ikony 1. piliera (medaila) a 2. piliera (dotyk ruky).
- Prvý pilier a jeho ikona sa nezvýraznia hneď, ale až keď je sekcia pilierov v polovici obrazovky.
- Hero bez troch číselných údajov (12+ rokov, 90 % nácvik, 2 – 4 účastníci); na desktope je vrch hlavného nadpisu zarovnaný s vrchom obrázka.
- Karty workshopov: všetky tlačidlá „Rezervovať termín“ červené ako rovnocenné výzvy; štítok na obrázku je nenápadný ako „Živý nácvik“ v hero, bez bodky; nové texty štítkov „Pokročilý tréning“, „Univerzálny“, „Špecializovaný“; dočasná fotka pre workshop Civilná prvá pomoc; nadpis „Tri intenzívne praktické workshopy“ (predtým „špecializované“); štítky z matného skla (hero aj workshopy) sú priehľadnejšie.
- Časté otázky a skúsenosti absolventov vedľa seba (na desktope otázky 2/3, skúsenosti 1/3 – dve nad sebou, pri viacerých sa striedajú); mobil bez zmeny poradia.
- Lektor: Boris Lacík, profesionálny hasič a zdravotnícky záchranár, 9+ rokov aktívnej služby, dočasná fotka (finálna od fotografa príde neskôr).
- Pruh „Kritický čas“: namiesto blikajúcej bodky animovaná červená EKG krivka poza text „Pripravenosť zachraňuje“ (bez animácie pri obmedzení pohybu).
- Hero sekcia: bez tlačidla „Ako prebieha workshop“, nový úvodný text („Žiadne suché poučky…“).
- Dočasné logo (priehľadné WebP, 60 px) v hlavičke, pätičke a na chybových stránkach; nový favicon (ICO, PNG, Apple touch icon) pre web aj admin; skript `scripts/make-logo.php`.
- Slogan v logu: „Prvá pomoc, ktorú zvládneš“ (predtým „Prvá pomoc • Workshop“).
- Štítok „Živý nácvik“ na hlavnom obrázku je nenápadný (matné biele sklo s pulzujúcou červenou bodkou), červená ostáva pre tlačidlá rezervácie.
- Hlavný obrázok: odstránený text na spodku (prekrýval ho baner ERC), štítok „Živý nácvik“ presunutý do horného rohu.
- Skupina je 2 – 4 osoby (predtým 1 – 4): texty na webe, výber v rezervácii, validácia aj admin.
- Myšlienka „Prečo malé skupiny“ zo zrušenej stránky O lektorovi je zapracovaná do 3. piliera na úvodnej stránke.
- Web je jedna stránka: menu skroluje na sekcie úvodnej stránky (aj z rezervácie, detailu workshopu a právnych stránok), aktívna sekcia sa zvýrazní.
- Zrušené samostatné stránky Workshopy, O lektorovi, Časté otázky a Kontakt; ich adresy presmerúvajú (301) na sekcie. Na úvodnej stránke sú všetky časté otázky.

### Zmenené – kontakt
- Pätička, časť Kontakt: údaje o živnosti (meno, IČO, DIČ, „Nie som platiteľ DPH“, sídlo) s ikonami namiesto popisov; DIČ a text k DPH sú nové polia v admine. Riadok „Bratislava a okolie do 60 km“ z pätičky odstránený.
- Pätička bez bloku „Užitočné odkazy“ (odkazy sú v hlavnom menu).
- Text kontaktného bloku: „malej firme“ → „firemnému tímu“.
- Blok „Garancia Bez váhania“ v kontaktnom bloku nahradil krátky kontaktný formulár (meno, e-mail, správa); správa ide do admina aj na info@bezvahania.sk.
- Kontaktné formuláre: nepovinný telefón aj v krátkom formulári, namiesto zaškrtávacieho poľa súhlasu informačná veta o spracovaní osobných údajov.
- Zrozumiteľnejšie hlásenie pri chýbajúcom súhlase v rezervačnom formulári.

### Opravené
- Kontakt na webe: telefón +421 915 918 386 a e-mail info@bezvahania.sk; rezervacie@bezvahania.sk iba ako odosielateľ automatických e-mailov, odpovede zákazníkov idú na info@.
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
