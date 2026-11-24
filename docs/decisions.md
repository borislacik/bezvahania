# Rozhodnutia k zadaniu

Pôvodné zadanie je v [brief.md](brief.md). Tento dokument zachytáva odpovede na otvorené otázky (sekcia 14)
a doplnené požiadavky. **Pri rozpore platí tento dokument.**

Posledná aktualizácia: 23. 11. 2026

## Zmena postupu (23. 11. 2026)
Overenie hostingu, deploy skripty a nasadenie sa robia **až keď bude prevádzkovateľ spokojný s lokálnou verziou**.
Dovtedy sa vyvíja celý web aj admin lokálne v Dockeri (pôvodné fázy 2 – 5 zo zadania sa spojili).

## Štruktúra webu (24. 11. 2026)
Web je **jedna stránka**: menu skroluje na sekcie úvodnej stránky. Samostatné stránky sú iba rezervácia, detail
workshopu a právne stránky. Pozri [ADR 0007](adr/0007-one-page-website.md).

## Doplnené pravidlá a požiadavky

| Téma | Rozhodnutie |
|---|---|
| Lokálny vývoj | Všetok lokálny vývoj beží v **Dockeri** a je plne testovateľný lokálne. Na hoste iba Docker Desktop a Git. |
| Umiestnenie kódu | Windows priečinok, **nie WSL** (rozhodnutie používateľa). `vendor/` a `node_modules/` sú v Docker volume. Pozri [ADR 0002](adr/0002-docker-local-development.md). |
| Testy | **Pest 5** (zadanie uvádzalo Pest 4, medzitým vyšla nová major verzia). |
| SEO | Plne pripravené a **editovateľné v admine** (titulok, popis, obrázok pre zdieľanie) pre každú stránku. |
| Obrázky | **Všetky obrázky na webe sú WebP.** Každý obrázok nahratý cez admin sa automaticky skonvertuje na WebP. |
| Dizajn | Návrh v Google Stitch (projekt „Mobile First Aid Workshops“), dostupný cez Stitch MCP. |
| Analytika | Google Analytics 4, ID `G-Z690RSSEM0`, iba na produkcii (`GOOGLE_ANALYTICS_ID`). |

## Odpovede na otázky zo sekcie 14

| # | Téma | Rozhodnutie |
|---|---|---|
| 1 | SSH | Trvalý SSH prístup je zakúpený (`shell.r6.websupport.sk`, port 26972, prihlásenie kľúčom). |
| 2 | Staging | **Staging nebude.** Vývoj aj testovanie iba lokálne, nasadzuje sa priamo na produkciu. |
| 3 | Repozitár, CI | GitHub, **verejný** repozitár. CI nebude. |
| 4 | Model rezervácie | Rezervuje sa **celý termín** pre jednu skupinu (**2 – 4 osoby**, zmena z 1 – 4 dňa 24. 11. 2026). Obsadené a zablokované termíny sa nedajú zvoliť. |
| 4 | Kalendár | Mesačný kalendár: aktuálny mesiac + prepnutie na nasledujúci. Admin označí nedostupné dni (sivé). Ostatné dni majú termín **doobeda 9–13** a **poobede 15–19**. Zákazník vyberie deň a termín, potom typ workshopu. |
| 5 | Potvrdzovanie | **Bez potvrdzovania adminom** – rezervácia voľného termínu je potvrdená hneď. |
| 6 | Zrušenie zákazníkom | Upresní sa neskôr. |
| 7 | Pripomienka | Predvolené: e-mail 24 h pred začiatkom (zatiaľ nepotvrdené). |
| 8 | Údaje | Predvolené: kontaktná osoba + počet účastníkov + **adresa konania**. |
| 9 | Osvedčenia | Na konci workshopu sa vydávajú osvedčenia o absolvovaní, **rieši ich prevádzkovateľ mimo webu**. Bez akreditácie (nie je to kurz BOZP). |
| 10 | Miesto konania | **U zákazníka doma alebo v kancelárii.** Malé skupiny ľudí, ktorí sa poznajú – hlavná konkurenčná výhoda. |
| 10 | Oblasť | Bratislava a okolie. **Doprava do 60 km je v cene**, ďalej podľa individuálnej dohody. |
| 11 | Ceny a platba | Tri typy workshopov (upresnia sa), cca **200 € za workshop**. Platba na mieste v hotovosti alebo kartou **po** workshope. |
| 12 | Admin – obsah | Kurzy (typy workshopov), termíny, tím, FAQ, SEO. Hlavne správa rezervácií a informácií k nim. |
| 13 | Administrátori | **Jeden** administrátor, bez rolí. |
| 14 | E-maily | `rezervacie@bezvahania.sk` **iba ako odosielateľ** automatických e-mailov rezervačného systému (schránka existuje). Kontakt na webe a príjemca upozornení: `info@bezvahania.sk`, odpovede zákazníkov (Reply-To) idú na `info@bezvahania.sk`. |
| 14 | Telefón | `+421 915 918 386` (zmena 24. 11. 2026) |
| 15 | Prevádzkovateľ | **Živnosť**, údaje a právne texty dodá prevádzkovateľ neskôr. |
| 16 | Analytika, cookies | Google Analytics → **cookie lišta podľa legislatívy EÚ** (GA až po súhlase). Web je GDPR compliant (aj kontaktný formulár). |
| 17 | Jazyky | Angličtina neskôr, **obsah kurzov preložiteľný** od začiatku (`spatie/laravel-translatable`). |
| 18 | Dizajn | Návrh zo Stitchu (pozri vyššie). |
| 19 | Doména | `bezvahania.sk` je registrovaná a na hostingu Websupport. Starý WordPress bol zo servera zmazaný 23. 11. 2026 (jeho databáza vo WebAdmine ešte existuje). |
| 20 | Monitoring chýb | **Iba log**, žiadne e-maily pri chybách. |
