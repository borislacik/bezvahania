# fix/load-fonts – načítanie písiem zo Stitch návrhu

## Požiadavka
Pri spustení webu lokálne sa ukázalo, že nadpis nepoužíva písmo Space Grotesk a text nepoužíva Work Sans,
hoci ich Vite pri builde stiahne.

## Riešenie
`laravel-vite-plugin` (fonts) písma zbuilduje do `public/build`, ale do stránky ich vloží až Blade direktíva
`@fonts` (vygeneruje `@font-face` a `preload` odkazy). Direktíva chýbala v layoute, doplnil som ju pred `@vite`.
Písma sa naďalej servírujú z vlastnej domény (žiadne CDN tretích strán).

## Dotknuté časti
- `resources/views/components/layouts/public.blade.php`

README: bez zmeny – oprava šablóny, príkazy ani nastavenie sa nemenia.

## Testy
- `tests/Browser/HomePageTest.php` – nový test overí cez `document.fonts`, že sa načítali Space Grotesk aj Work Sans.
  Bez opravy zlyhá, s opravou prejde.
- Ručne: http://bezvahania.localhost – nadpis je v písme Space Grotesk.

## Nasadenie
Nič sa nenasadzuje.

## Odkazy
[ADR 0004](../adr/0004-build-assets-locally.md)
