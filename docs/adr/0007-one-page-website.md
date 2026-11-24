# ADR 0007: Web ako jedna stránka (one-pager)

- Stav: prijaté
- Dátum: 2026-11-24

## Kontext
Prvá verzia mala samostatné stránky Workshopy, O lektorovi, Časté otázky a Kontakt. Ich obsah bol zároveň
na úvodnej stránke. Prevádzkovateľ chce, aby menu neviedlo na samostatné stránky, ale skrolovalo na sekcie
úvodnej stránky.

## Rozhodnutie
- Menu (Workshopy, O lektorovi, Časté otázky, Kontakt) odkazuje na sekcie úvodnej stránky
  `/#workshopy`, `/#lektor`, `/#otazky`, `/#kontakt` (`App\Support\HomeSections`). Z ostatných stránok
  vedie na úvodnú stránku k sekcii. Na úvodnej stránke sa aktívna sekcia zvýrazní v menu (IntersectionObserver).
- Samostatné stránky ostávajú iba: **rezervácia**, **detail každého workshopu** (SEO) a **právne stránky**.
- Bývalé adresy `/workshopy`, `/o-lektorovi`, `/caste-otazky`, `/kontakt` presmerúvajú **301** na sekcie
  (iba GET; `POST /kontakt` je kontaktný formulár).
- Sekcia Časté otázky na úvodnej stránke zobrazuje všetky otázky a nesie štruktúrované dáta `FAQPage`.
- Kontaktný formulár je v sekcii Kontakt; samostatná stránka s plným formulárom zanikla.
- SEO záznamy bývalých stránok ostávajú v databáze, admin (**Stránky a SEO**) zobrazuje iba existujúce stránky
  (`Page::KEYS`).

## Dôsledky
- Jednoduchšia navigácia, menej stránok na údržbu.
- Menej vstupných stránok pre vyhľadávače; kompenzujú to detailné stránky workshopov.
- Text „Prečo malé skupiny“ zo stránky O lektorovi na webe už nie je.

## Zvažované alternatívy
- **Zrušiť aj detaily workshopov** – horšie SEO pre jednotlivé workshopy.
- **Ponechať všetky stránky mimo menu** – duplicitný obsah a stránky, ktoré nikto nenájde.
