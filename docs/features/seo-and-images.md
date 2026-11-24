# SEO a obrázky

## SEO (`App\Support\Seo`, `resources/views/components/site/seo.blade.php`)
Každá stránka má:
- `<title>` a `meta description` – z admina (**Stránky a SEO**, pri workshopoch z formulára workshopu),
  inak názov a krátky popis,
- `canonical` URL, Open Graph (`og:title`, `og:description`, `og:image`, `og:locale=sk_SK`) a Twitter card,
- obrázok pri zdieľaní: z admina, inak `resources/images/og-default.webp` (1200 × 630),
- štruktúrované dáta JSON-LD: `LocalBusiness` a `FAQPage` (úvodná stránka), `Course` (detail workshopu).

Ďalej:
- `/sitemap.xml` – úvodná stránka, rezervácia, právne stránky a aktívne workshopy (web je one-pager, ADR 0007),
- bývalé stránky (`/workshopy`, `/o-lektorovi`, `/caste-otazky`, `/kontakt`) presmerúvajú 301 na sekcie úvodnej stránky,
- `/robots.txt` – na produkcii povolí indexovanie a odkáže na sitemap, mimo produkcie všetko zakáže,
- mimo produkcie a na potvrdení rezervácie je `meta robots noindex`,
- `www.` presmeruje na doménu bez `www` (301).

## Obrázky – vždy WebP
- Obrázky z dizajnu sú v `resources/images/*.webp` (Vite im pridá hash do názvu).
- Obrázky nahraté v admine sa konvertujú na WebP pomocou GD (`App\Support\WebpImage`), kvalita 82,
  max. šírka 1920 px. Uložia sa na disk `public` (`storage/app/public`).
- Lokálne obsluhuje `/storage` nginx (alias), na produkcii relatívny symlink (overí sa pri nasadení).
- Obrázky majú `width`, `height`, `loading="lazy"` (okrem hlavného obrázka na úvode, ten má `fetchpriority="high"`).
- Test `PublicPagesTest` overuje, že všetky `<img>` na úvodnej stránke sú WebP.

## Logo a favicon
- Dočasné logo (finálne pripravuje grafik): zdroj `design/logo/docasne-logo-zdroj.jpg` (červený znak na čiernom).
- `php scripts/make-logo.php <zdroj>` urobí čierne pozadie priehľadné (priehľadnosť podľa červeného kanála, bez tmavej
  obruby), oreže znak na štvorec a vytvorí `resources/images/logo.webp` (web), `design/logo/logo.png` (master),
  `public/favicon.ico` (16 + 32 px), `public/favicon.png` a `public/apple-touch-icon.png` (biele pozadie, iOS).
- **Výnimka z pravidla WebP:** favicony sú ICO/PNG, lebo WebP ako favicon prehliadače nepodporujú spoľahlivo.
- Pri finálnom logu stačí spustiť skript znova s novým zdrojom (pri inej farbe upraviť farbu v skripte).

## Písma a ikony bez tretích strán
- Písma Space Grotesk a Work Sans sa pri builde stiahnu a servírujú z vlastnej domény (`@fonts`).
- Ikony Material Symbols sú SVG súbory v `resources/svg/icons` (licencia Apache 2.0), komponent
  `<x-site.icon name="…" />`. Nenačítava sa žiadny font z Google.
