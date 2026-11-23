# ADR 0006: Preložiteľný obsah, obrázky vo WebP a zdroje bez tretích strán

- Stav: prijaté
- Dátum: 2026-11-23

## Kontext
Angličtina príde neskôr, obsah kurzov musí byť preložiteľný. Všetky obrázky musia byť WebP a nahrané
obrázky sa majú automaticky konvertovať. Web má byť GDPR compliant.

## Rozhodnutie
- **`spatie/laravel-translatable` 6** (MIT, aktívne udržiavaný): textové stĺpce obsahu sú JSON s jazykmi.
  Filament formuláre upravujú aktuálny jazyk cez `App\Filament\Support\Translatable` (bez ďalšieho pluginu).
- **Konverzia na WebP cez GD** (rozšírenie PHP, bez ďalšieho balíka): `App\Support\WebpImage`. GD s WebP
  je lokálne overené; na hostingu sa overí pred nasadením.
- **Ikony Material Symbols ako lokálne SVG** (Apache 2.0) a **písma servírované z vlastnej domény** –
  žiadne požiadavky na Google Fonts.
- **Alpine.js** (`alpinejs`, `@alpinejs/collapse`, MIT) iba pre kalendár, menu, FAQ a cookie lištu.

## Dôsledky
- Pridanie jazyka = prekladové súbory `lang/<jazyk>`, prepínač vo formulároch a prefix v URL (bez zmeny schémy).
- Nahraný obrázok sa vždy uloží ako `.webp`; pôvodný súbor sa neuchováva.
- Ak hosting nemá GD s WebP, treba to vyriešiť pred nasadením (alternatíva: Imagick).

## Zvažované alternatívy
- **Filament Spatie Translatable plugin** – navyše závislosť, pre jeden jazyk zbytočná.
- **Intervention Image** – pohodlnejšie API, ale ďalší balík; GD stačí.
- **Google Fonts / Material Symbols z CDN** – prenos IP adries tretej strane (GDPR).
