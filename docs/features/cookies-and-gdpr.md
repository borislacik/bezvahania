# Cookies a GDPR

## Cookie lišta (`resources/js/cookie-consent.js`, `components/site/cookie-banner.blade.php`)
- Zobrazí sa pri prvej návšteve. **„Odmietnuť voliteľné“ je rovnako výrazné ako „Prijať všetky“.**
- V nastaveniach sa dá zvoliť kategória **Analytické** (nevyhnutné sú vždy zapnuté).
- Voľba sa uloží do `localStorage` (`bv-cookie-consent`, s verziou a dátumom) – nie je to sledovacie cookie.
- Súhlas sa dá kedykoľvek zmeniť odkazom **„Nastavenia cookies“** v pätičke.
- **Google Analytics 4** (`GOOGLE_ANALYTICS_ID`, produkcia `G-Z690RSSEM0`) sa načíta **až po súhlase**.
  Pred načítaním sa nastaví Google Consent Mode v2 (reklamné úložiská `denied`, analytické `granted`).
- Po odvolaní súhlasu sa cookies `_ga*` zmažú a Consent Mode sa prepne na `denied`.
- Lokálne a v testoch je `GOOGLE_ANALYTICS_ID` prázdne, GA sa nenačíta vôbec.

## GDPR
- Formuláre zbierajú len potrebné údaje. Rezervácia vyžaduje súhlas s podmienkami (zaškrtávacie pole),
  kontaktné formuláre zobrazujú iba informačnú vetu o spracovaní osobných údajov (súhlas tu nie je právnym základom).
- Pri rezervácii sa ukladá čas a verzia súhlasu (`consent_at`, `consent_version`).
- Doby uchovávania: rezervácie anonymizované **3 roky** po termíne, správy z formulára zmazané po **12 mesiacoch**.
- Právne stránky sú dve: **Obchodné podmienky** (`/obchodne-podmienky`) a **Ochrana osobných údajov a používanie
  cookies** (`/ochrana-osobnych-udajov`, obsahuje aj časť o cookies). Texty dodá prevádzkovateľ, vkladajú sa v admine
  (**Stránky a SEO**). Kým nie sú vyplnené, stránka zobrazí informáciu, že text sa pripravuje.
- Okno **Nastavenia cookies** (odkaz v pätičke) sa po výbere zatvorí; ak už výber existuje, dá sa zatvoriť aj
  krížikom alebo klávesom Esc bez zmeny.
- Bezpečnostné hlavičky: `X-Content-Type-Options`, `Referrer-Policy`, `X-Frame-Options`, `Permissions-Policy`,
  CSP v režime report-only a HSTS na HTTPS (`App\Http\Middleware\SecurityHeaders`).
