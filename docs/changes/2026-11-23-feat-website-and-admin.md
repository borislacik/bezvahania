# feat/website-and-admin – celý web a administrácia lokálne

## Požiadavka
„Fáza 2 nemá obsahovať overenie hostingu a deploy skript, to budeme robiť až keď budem spokojný s lokálnou
verziou. Teraz chcem, aby si vyvinul celý web aj s adminom a lokálne ho spustil na Dockeri. Na dizajn použi Stitch.“
Doplnené počas práce: SEO plne pripravené a editovateľné v admine (vrátane obrázka, nadpisu a popisu), všetky
obrázky vo WebP s automatickou konverziou nahraných obrázkov, Google Analytics `G-Z690RSSEM0`.

## Riešenie
- **Dizajn**: Stitch projekt „Mobile First Aid Workshops“ (cez Stitch MCP) – farby, typografia, sekcie, obrázky
  (konvertované na WebP). Texty z návrhu sú upravené podľa [decisions.md](../decisions.md) (1 – 4 osoby,
  Bratislava + 60 km, časti dňa 9 – 13 / 15 – 19, okamžité potvrdenie, osvedčenie bez akreditácie).
- **Rezervácie**: celý termín, mesačný kalendár, unikátny `active_slot` ([ADR 0005](../adr/0005-unique-slot-booking.md)),
  logika v `App\Actions\Bookings`, dostupnosť v `App\Support\Availability`.
- **Admin**: Filament 5 na `ADMIN_DOMAIN`, MFA (TOTP), vlastné stránky Dostupnosť a Nastavenia, widgety.
- **Obsah a SEO** editovateľné v admine, preložiteľné cez `spatie/laravel-translatable`
  ([ADR 0006](../adr/0006-translatable-content-and-webp.md)).
- **Bez tretích strán**: písma a ikony (SVG) z vlastnej domény, GA až po súhlase.

### Nové balíky (zdôvodnenie)
| Balík | Účel | Licencia | Poznámka |
|---|---|---|---|
| `filament/filament` 5.12 | administrácia | MIT | v zadaní |
| `spatie/laravel-translatable` 6.15 | preložiteľný obsah | MIT | v zadaní (predvolená odpoveď 17) |
| `alpinejs`, `@alpinejs/collapse` 3.17 | kalendár, menu, FAQ, cookie lišta | MIT | Alpine je v zadaní |

### Oprava počas kontroly (24. 11. 2026)
Používateľ nahlásil, že ho admin po chvíli odhlási. Príčina: v `AdminPanelProvider` bol celý zoznam middleware
označený `isPersistent: true`, takže Livewire pri každej požiadavke na pozadí (obnovovanie widgetu štatistík každých
5 s) spustil `EncryptCookies` a `StartSession` druhýkrát. Dešifrovanie už dešifrovaného cookie zlyhalo, vznikla
nová neuložená session a ďalšia požiadavka skončila presmerovaním na prihlásenie. Persistentný je teraz iba
`AddNoIndexHeader`; regresný test v `DomainSeparationTest`. Predchádzajúce obídenie (widgety bez lazy načítania)
bolo odstránené. Chyba by sa prejavila aj na produkcii.

### Kontaktné údaje (24. 11. 2026)
Požiadavka: „Všade kde uvádzaš telefónne číslo použi +421 915 918 386, všade kde uvádzaš kontaktný e-mail použi
info@bezvahania.sk. E-mail rezervacie@bezvahania.sk sa bude používať len na automatické rozposielanie e-mailov
v rezervačnom systéme.“ Zmenené predvolené nastavenia (`Setting::DEFAULTS`), Reply-To v e-mailoch zákazníkom,
poznámka o odosielateľovi v admine, placeholder telefónu vo formulári (`+421 9xx xxx xxx`). Testy overujú, že
`rezervacie@` sa na webe nezobrazuje.

### Kontaktný formulár v kontaktnom bloku (24. 11. 2026)
Požiadavka: „Ľavú polovicu nechaj ako je. Pravú polovicu vymeň celú za kontaktný formulár, kde bude môcť užívateľ
napísať správu, ktorá mi príde do správ v admine a zároveň na info@bezvahania.sk. Polia budú meno, e-mail a správa.“
Riešenie: pravá časť `contact-cta` je formulár s rovnakou logikou ako stránka Kontakt (`SendContactMessage`),
navyše súhlas so spracovaním údajov (GDPR) a honeypot. Návrat na pôvodnú stránku k formuláru (`ContactRedirect`),
po načítaní sa stránka posunie k formuláru s chybou alebo potvrdením. Komponent `site.field` dostal `idPrefix` a `rows`.

Úprava podľa návrhov (24. 11. 2026): „pridaj nepovinné pole telefón a namiesto zaškrtávacieho poľa daj iba
informačnú vetu; automatické potvrdenie odosielateľovi nie je potrebné.“ Platí pre oba kontaktné formuláre
(blok aj stránka Kontakt), rezervácia si zaškrtávacie pole ponecháva.

Text v ľavej časti bloku: „malej firme“ zmenené na „firemnému tímu“ (`lang/sk/site.php`, 24. 11. 2026).

Pätička: odstránený blok „Užitočné odkazy“, odkazy sú v hlavnom menu (24. 11. 2026).

### Web ako jedna stránka (24. 11. 2026)
Požiadavka: „Odkazy z menu nebudú presmerovávať na samostatné stránky, ale skrolovať na príslušnú sekciu na hlavnej
stránke. Z rezervácie budú odkazy menu odkazovať na príslušnú sekciu na hlavnej stránke.“ Doplnené: ponechať detaily
workshopov a právne stránky. Riešenie a dôsledky: [ADR 0007](../adr/0007-one-page-website.md). Zmazané šablóny
`pages/about`, `pages/faq`, `pages/contact`, `pages/workshops/index`; nové `App\Support\HomeSections`;
`ContactController` má iba `store`. Testy: menu odkazy zo všetkých stránok, sekcie na úvode, 301 presmerovania,
browser test skrolovania z rezervácie.

Text „Prečo malé skupiny“ zo zrušenej stránky O lektorovi zapracovaný do 3. piliera („Komfort vášho domova“), texty sa ešte budú upravovať.

### Skupina 2 – 4 osoby (24. 11. 2026)
Požiadavka: „Miesta, kde sa hovorí o počte účastníkov 1 – 4 osoby, zmeň na 2 – 4 osoby.“ Zmenené texty
(`lang/sk/site.php`, FAQ a SEO úvodu v seederi), aj samotné pravidlo: `Booking::MIN_PARTICIPANTS = 2`, validácia
rezervácie, výber vo formulári (2, 3, 4) a v admine, factory. Lokálna databáza: upravená odpoveď vo FAQ a SEO popis úvodu.

Hero obrázok (24. 11. 2026): odstránený popis na spodku obrázka, ktorý prekrýval baner „Aktuálne odporúčania ERC“; baner ostal, štítok „Živý nácvik“ je v ľavom hornom rohu, bez tmavého prechodu.

Štítok „Živý nácvik“ (24. 11. 2026): podľa výberu používateľa z troch variantov je matné biele sklo s pulzujúcou červenou bodkou (pri `prefers-reduced-motion` bez pulzu). Červená je vyhradená pre výzvy na rezerváciu.

Slogan v logu (hlavička aj pätička): „Prvá pomoc, ktorú zvládneš“ (`site.brand_tagline`, 24. 11. 2026).

### Dočasné logo a favicon (24. 11. 2026)
Požiadavka: „Použi tento obrázok ako nové logo, bude dočasné. Všetko čo je čierne sprav priehľadné. Následne ho použi
aj ako nový favicon.“ Skript `scripts/make-logo.php` (GD), výstupy pozri `docs/features/seo-and-images.md`.
Logo nahradilo červený štvorec s ikonou (`components/site/logo`), favicon aj v admine (`->favicon()`), odstránený
`public/favicon.svg`.

Logo zväčšené zo 44 na 60 px podľa screenshotu používateľa (24. 11. 2026).

Hero sekcia (24. 11. 2026): odstránené tlačidlo „Ako prebieha workshop“, nový úvodný text podľa používateľa.

EKG krivka (24. 11. 2026): pri „Pripravenosť zachraňuje“ nahradila blikajúcu bodku (dve blikajúce bodky pri sebe boli priveľa). SVG s dvoma tepmi po stranách textu, impulz cez `stroke-dashoffset` (`.ecg-line` v `resources/css/app.css`), pri `prefers-reduced-motion` statická.

EKG krivka podľa predlohy od používateľa (24. 11. 2026): obkreslená iba čierna čiara (P vlna, PQ úsek, QRS, ST úsek, T vlna), dva tepy po stranách textu, jeden prebiehajúci impulz.

Text v pruhu „Kritický čas“: „…až o niekoľko minút neskôr…“ (25. 11. 2026).

Nadpis pruhu ostáva „Kritický čas: prvé 3 až 5 minút“ (krátko zmenené na „minúty“ a vrátené, 25. 11. 2026).

### Piliere so spomaleným skrolovaním (24. 11. 2026)
Sekcia „Ako prebieha workshop priamo u vás?“ je zrušená, jej obsah zhŕňa nová prvá častá otázka „Ako prebieha
workshop?“ (`ContentSeeder`). Piliere sa už nepodobajú na karty workshopov: komponent
`components/site/pillars-scrolly.blade.php` (Alpine.js, bez nového balíka).

- Desktop (od 1024 px): sekcia pilierov spolu so začiatkom sekcie Workshopy sa „prilepí“ pod hlavičku a posúva sa
  asi tretinovou rýchlosťou, kým skrolovanie neprejde všetky tri piliere. Piliere sa postupne zvýraznia a ostanú
  zvýraznené; v tmavom paneli pribudne nad každou časťou čiary ikona piliera.
- Mobil: bez spomalenia, piliere sa plynulo objavia pri príchode na obrazovku.
- Bez JavaScriptu alebo s obmedzením pohybu (`prefers-reduced-motion`): všetko statické a viditeľné.
- Odkaz „Workshopy“ v menu: počas spomalenia by prehliadač vypočítal zlú pozíciu kotvy, preto komponent skroluje
  na miesto, kde sekcia skončí po spomalení (kliknutie aj príchod z inej stránky s `#workshopy`).
- Začiatok efektu je skôr: sekcia sa „prilepí“, keď je jej vrch v 30 % výšky obrazovky (predtým hneď pod hlavičkou,
  asi 10 %). Hodnotu si používateľ vyskúšal cez dočasné nastavenie v admine (25 %, potom upravené na 30 %); je zapísaná v komponente
  a nastavenie z admina je odstránené.
- Tmavý pruh „Kritický čas“ nad piliermi je súčasťou spomaleného bloku (slot `before`), takže pri začiatku
  spomalenia „neodskočí“ hore, ale pomaly zájde pod hlavičku rovnakou rýchlosťou ako piliere.
- Piliere bez označenia „Pilier 01 – 03“ (kľúč `site.home.pillar_label` odstránený), iba nadpis a text.
- Nové ikony podľa výberu používateľa: 1. pilier `workspace_premium` (medaila, predtým plameň; krátko štít s fajkou),
  2. pilier `touch_app` (dotyk ruky, predtým ruka); 3. pilier ostáva `cottage`.
- Na začiatku nie je zvýraznený žiadny pilier; prvý sa zvýrazní, keď je vrch pilierov v 50 % výšky obrazovky
  (ešte pred spomalením, ktoré začína pri 30 %), ďalšie v rovnakých krokoch a posledný ostane zvýraznený ešte asi tretinu úseku. Bez JavaScriptu sú ikony v paneli viditeľné.

### Lektor a hero bez čísel (24. 11. 2026)
- Lektor: meno **Boris Lacík** (seeder aj lokálna DB, aj v referencii „Boris prišiel…“), popis „Profesionálny hasič
  a zdravotnícky záchranár“, odznak „9+ rokov zásahov“.
- Dočasná fotka lektora od používateľa: WebP 1024 × 1024 v `resources/images/instructor.webp` (seeder ju skopíruje do
  `storage/app/public/team/`). Finálnu fotku od fotografa stačí nahrať v admine (Lektori), netreba meniť kód.
- Hero: odstránené tri číselné údaje (12+ rokov, 90 % nácvik, 2 – 4 účastníci, kľúč `site.home.kpis`). Prázdne miesto
  po nich bolo príliš veľké, preto je na desktope vrch hlavného nadpisu zarovnaný s vrchom obrázka (`lg:items-start`,
  obrázok `lg:mt-7`) a sekcia je nižšia.

### Viac fotiek lektora (25. 11. 2026)
- Sekcia O lektorovi: hlavná fotka a ďalšie fotky sa striedajú každých 6 s (prelínanie), pauza pri prejdení myšou
  alebo fokuse, pri obmedzení pohybu sa samé nemenia. Bodky vľavo dole na fotke (matné sklo) ich prepínajú ručne.
  Pri jednej fotke sa bodky nezobrazia.
- Model `TeamMember`: nové pole `gallery` (JSON zoznam ciest, migrácia `2026_11_25_114106_add_gallery_to_team_members_table`),
  `photoUrls()` = hlavná fotka + galéria. Admin → Lektori: „Ďalšie fotky“ (viac súborov, poradie potiahnutím,
  max. 10, konverzia na WebP ako pri ostatných obrázkoch).
- Fotky od používateľa orezané na štvorec 1024 × 1024 / 921 × 921 tak, aby lektor ostal v zábere:
  `resources/images/instructor-2.webp` (nácvik KPR so skupinou, tváre účastníkov zakryté už na zdrojovej fotke),
  `resources/images/instructor-field.webp` (lektor v záchrannej službe; nahradila pôvodnú `instructor-3.webp` vo vyššej
  kvalite, nový názov kvôli cache prehliadačov). Poradie: súčasná fotka, nácvik, záchranná služba. Seeder aj lokálna DB.
- Prepínač (bodky) a odznak „Aktívna služba · 9+ rokov zásahov“ sú z matného bieleho skla (biela 55 %, rozmazanie);
  text odznaku tmavomodrý (`text-inverse-surface`), štítok „Aktívna služba“ červený – predtým tmavomodrý odznak.

### Blokovanie doobedného alebo poobedného termínu (25. 11. 2026)
- Admin → Dostupnosť termínov: každý deň má dva termíny (9 – 13, 15 – 19), kliknutím sa termín zablokuje alebo
  uvoľní. Tlačidlo **Celý deň** blokuje / uvoľní oba naraz. Uvoľnenie jedného termínu v celom zablokovanom dni
  nechá druhý termín zablokovaný; zablokovanie oboch termínov postupne sa zlúči na celý deň (zvýrazní sa aj
  „Celý deň“, v DB jeden riadok `day`). Rezervovaný termín sa nedá zablokovať (zobrazí sa s menom zákazníka), deň
  s rezerváciou sa nedá zablokovať celý, minulé dni sa nemenia.
- Nová migrácia `2026_11_25_105940_add_slot_to_blocked_days_table`: stĺpec `slot` (`day` | `morning` |
  `afternoon`, predvolene `day`), unikátny index `date` nahradený `(date, slot)`. Existujúce blokovania ostávajú
  ako celé dni. Pôvodná migrácia sa nemenila.
- `App\Support\Availability` berie do úvahy blokovanie celého dňa aj termínu; verejný kalendár tak ponúkne iba voľný
  termín.
- Testy: blokovanie / uvoľnenie termínu, prechod medzi termínom a celým dňom, termín s rezerváciou, neplatný
  termín a minulý deň (Livewire), stav termínu v `AvailabilityTest`.

### Fakturačné údaje v rezervácii (25. 11. 2026)
- Pod adresou konania pribudlo zaškrtávacie pole **Fakturačné údaje sú iné ako adresa konania**; po zaškrtnutí sa
  zobrazí textové pole **Fakturačné údaje** (meno alebo firma, adresa, prípadne IČO a DIČ, max. 500 znakov).
  Zaškrtnuté a prázdne pole je chyba; bez zaškrtnutia sa text ignoruje.
- Nová migrácia `2026_11_25_220802_add_billing_address_to_bookings_table` (stĺpec `bookings.billing_address`, text,
  nepovinný). Pôvodná migrácia sa nemenila.
- Fakturačné údaje sú v potvrdení na webe, v e-maile zákazníkovi aj prevádzkovateľovi, v detaile a úprave rezervácie
  v admine (prázdne = „Rovnaké ako adresa konania“), pri telefonickej rezervácii a v exporte CSV.
- GDPR: anonymizácia po 3 rokoch maže aj fakturačné údaje.
- Testy: uloženie a orezanie textu, ignorovanie bez zaškrtnutia, povinnosť po zaškrtnutí, potvrdzovacia stránka,
  e-maily, admin (vytvorenie, úprava), anonymizácia, browser test celej rezervácie so zaškrtnutím.

### Storno podmienky (25. 11. 2026)
- Nová právna stránka **Storno podmienky** (`/storno-podmienky`, route `legal.cancellation`, kľúč stránky
  `cancellation`), v pätičke medzi Obchodnými podmienkami a Ochranou osobných údajov, v sitemape a v admine
  (Stránky a SEO, s editorom obsahu).
- Text storno podmienok zatiaľ nie je (brief: politika zrušenia sa ešte určí) – stránka zobrazuje „Text sa
  pripravuje“. Text dodá používateľ; do nasadenia ho vkladá Claude do seedera, nie cez admin.

### Facebook v kontaktnom bloku (25. 11. 2026)
- Pod telefón a e-mail v bloku „Osobná konzultácia“ pribudol odkaz **Bez váhania na Facebooku** s ikonou
  (nové okno, `rel="noopener"`).
- Adresa stránky je nové nastavenie `social_facebook` (admin → Kontakt a prevádzkovateľ, validácia URL), predvolene
  `https://www.facebook.com/profile.php?id=61596209436961`; prázdne pole odkaz skryje. Pridaná aj do JSON-LD
  LocalBusiness ako `sameAs`.
- Ikona `resources/svg/icons/facebook.svg` je logo zo Simple Icons (CC0), uložené lokálne – web nenačítava nič
  z Facebooku (žiadne vkladané widgety ani cookies tretích strán).
- Väčšia medzera medzi textom a kontaktmi (`mt-6`, na desktope `mt-10`) a medzi kontaktmi (`gap-4`), aby pod nimi
  neostávalo veľa prázdneho miesta vedľa formulára.
- Lokálna DB: na pokyn používateľa zmazané štyri nepoužívané stránky z obdobia pred jednostránkovým webom
  (`workshops`, `about`, `faq`, `contact`); seeder ich nevytvára, na produkciu by sa nedostali.

### Právne stránky a nastavenia cookies (25. 11. 2026)
- Dve právne stránky namiesto troch: **Obchodné podmienky** (`/obchodne-podmienky`, kľúč `terms`, predtým „Podmienky
  rezervácie“ na `/podmienky-rezervacie`) a **Ochrana osobných údajov a používanie cookies** (`/ochrana-osobnych-udajov`,
  kľúč `privacy`; obsah bývalej stránky `/cookies` je jej časťou). Route `legal.cookies` a kľúč `cookies` zrušené,
  staré adresy presmerúvajú 301. Pätička: Obchodné podmienky, Ochrana osobných údajov a cookies (kratší text
  odkazu, kľúč `site.footer.privacy`; nadpis stránky ostáva dlhší), Nastavenia cookies. Cookie lišta „Viac o cookies“ vedie na stránku ochrany údajov, súhlas v rezervácii hovorí
  o „obchodných podmienkach“.
- Lokálna DB: stránky premenované (aj SEO titulok a popis), text o cookies presunutý do stránky ochrany údajov,
  záznam stránky `cookies` zmazaný (podľa pokynu používateľa, že stránky budú dve). Na produkcii ešte nič nie je.
- Oprava: okno Nastavenia cookies otvorené z pätičky sa po výbere nezatvorilo. Pri uložení sa naraz skrývalo okno
  aj rozbalená časť s nastaveniami (`x-collapse`) a Alpine čakal na animáciu, ktorá sa nedokončila. Teraz sa skryje
  iba okno, nastavenia sa resetujú pri otvorení. Ak už výber existuje, okno má krížik a zatvára sa aj klávesom Esc
  (bez zmeny výberu). Browser test pokrýva všetky tri spôsoby zatvorenia.

### Údaje o živnosti v pätičke (24. 11. 2026)
- Časť Kontakt v pätičke: pod telefónom a e-mailom (bez deliacej čiary) nasleduje ikona preukazu s menom, IČO, DIČ
  a textom k DPH, ikona domčeka so sídlom (ikony namiesto popisov „Prevádzkovateľ“, „Sídlo“).
- Údaje sú v nastaveniach (admin → Kontakt a prevádzkovateľ). Nové polia `operator_dic` a `operator_vat_note`
  (bez migrácie, tabuľka `settings`). Predvolené hodnoty v `Setting::DEFAULTS` sú údaje živnosti Borisa Lacíka,
  takže sa zobrazia aj po nasadení bez ručného zadania; lokálne sú uložené aj v DB.
- Z pätičky odstránený riadok „Bratislava a okolie do 60 km“ (oblasť je uvedená v iných častiach webu).
- Adresa v tvare „Studenohorská 2061/17, 841 03, Bratislava“, text „Nie som platiteľ DPH“.

### Karty workshopov (24. 11. 2026)
- Tlačidlá „Rezervovať termín“ boli pri každom workshope inej farby (červené, tmavé, svetlé), takže prvý workshop
  pôsobil dôležitejšie. Teraz sú všetky červené (`btn-primary`): tri rovnocenné výzvy, červená ostáva vyhradená
  pre rezerváciu. Workshopy sa odlišujú obrázkom, štítkom a nadpisom.
- Štítok vľavo hore na obrázku (napr. „Najžiadanejší“) je matné biele sklo ako „Živý nácvik“ v hero, bez pulzujúcej
  bodky a bez hviezdičky. `course-card` už nepotrebuje parameter `index`.
- Nové texty štítkov (seeder aj lokálna DB, v admine pole Štítok): KPR a AED „Pokročilý tréning“, Civilná prvá
  pomoc „Univerzálny“, Taktická prvá pomoc „Špecializovaný“.
- Workshop Civilná prvá pomoc mal namiesto fotky iba ikonu. Dočasne má fotku od používateľa (skupinový nácvik KPR,
  WebP 600 × 400, `resources/images/workshop-family.webp`; seeder aj lokálna DB). Finálne fotky workshopov dodá
  používateľ, nahrajú sa v admine (Workshopy → Obrázok).
- Nadpis ponuky: „Tri intenzívne praktické workshopy“ (predtým „Tri špecializované…“, kolidovalo so štítkom
  „Špecializovaný“ tretieho workshopu).
- Štítky z matného bieleho skla („Živý nácvik“ v hero a štítky workshopov) sú priehľadnejšie: biela 70 % namiesto 85 %.
  Neskôr (25. 11.) zjednotené na 55 % spolu so štítkom dĺžky workshopu (predtým 90 %) a prvkami pri lektorovi –
  všetky štítky z matného skla majú `bg-white/55 backdrop-blur-md`. Hlavička webu ostáva `bg-surface/90`. Kartička „Aktuálne odporúčania ERC“ v hero
  je podľa výberu používateľa (náhľady 55 % / 70 %) z matného skla `bg-white/70` – dlhší text sa číta ľahšie.
  Po náhľadoch kariet workshopov a fotky lektora používateľ zvolil 70 % pre všetky prvky z matného skla
  (6× `bg-white/70 backdrop-blur-md`); hlavička ostáva `bg-surface/90`.

### Časté otázky a skúsenosti vedľa seba (24. 11. 2026)
- Na desktope jedna sekcia: vľavo časté otázky (2/3 šírky), vpravo skúsenosti absolventov (1/3) s menším nadpisom.
- Skúsenosti sú dve nad sebou (`components/site/testimonials.blade.php`). Ak ich je v admine viac ako dve, každých
  8 sekúnd sa vymenia za ďalšie dve (pauza pri prejdení myšou alebo fokuse, bez automatickej výmeny pri obmedzení
  pohybu); bodky pod nimi ich prepínajú ručne. Výmena má jemné zjavenie.
- Mobil: poradie ako predtým (najprv skúsenosti, potom otázky), všetky skúsenosti pod sebou; obe časti majú teraz
  rovnaké pozadie.
- Kotvy `#skusenosti` a `#otazky` (menu) ostávajú, sú na jednotlivých častiach sekcie.
- `section-heading` má nové voľby `align="responsive"` (na mobile na stred, od lg vľavo) a `compact`;
  `faq-list` a `testimonial` prijímajú ďalšie triedy.

### Slovenská diakritika v písmach (24. 11. 2026)
Vlastné písma (Work Sans, Space Grotesk) sa cez `bunny()` v `vite.config.js` sťahovali iba v sade `latin`
(predvolená hodnota pluginu). Písmená č, š, ľ, ť, ž, ň, ď, ĺ, ŕ sú v sade `latin-ext`, preto ich prehliadač kreslil
záložným písmom a v texte vyzerali inak. Doplnené `subsets: ['latin', 'latin-ext']`; prehliadač si sadu latin-ext
stiahne iba vtedy, keď ju text potrebuje (`unicode-range`). Browser test kontroluje, že sa sady latin-ext načítajú.

### Stabilita browser testov
`composer test:browser` pred testami skompiluje šablóny (`view:cache`). Pri studenom štarte sa inak pri prvom
prihlásení do adminu kompilovali všetky Filament šablóny a test prekročil časový limit.

Paralelné testy bežia v **4 procesoch** (`--processes=4`). Pri predvolenom počte (16 = počet jadier) a 2 GB RAM pre
Docker zabil systém kontajner MariaDB (exit 137) a testy sa zasekli.

`composer test:browser` najprv ukončí staré procesy `playwright run-server` (`scripts/stop-stale-playwright.sh`).
Pest browser plugin ich pri skončení nie vždy zastaví; hromadili sa v kontajneri `app` (asi 15 procesov, ~1 GB RAM),
spomaľovali server a browser testy padali na časový limit.

README: bez zmeny – príkaz `composer test:browser` sa používa rovnako.

## Dotknuté časti
- Migrácie: MFA stĺpce v `users`, `courses`, `blocked_days`, `bookings`, `team_members`, `faqs`, `testimonials`,
  `pages`, `settings`, `contact_messages`.
- Modely, Enums (`TimeSlot`, `BookingStatus`), Actions, `Availability`, `Seo`, `WebpImage`, middleware
  (`SecurityHeaders`, `RedirectWww`, `AddNoIndexHeader`), e-maily (4 Mailables + Markdown šablóny).
- Routy: všetky verejné routy v `Route::domain(config('app.domain'))`, Filament panel na `admin_domain`.
- Filament: 7 resources, 2 stránky, 2 widgety, téma `resources/css/filament/admin/theme.css`.
- Šablóny: `resources/views/pages/*`, `components/site/*`, `errors/*`, `mail/*`; preklady `lang/sk/*.php`.
- Konfigurácia: `config/app.php` (`admin_url`), `config/auth.php` (`admin_mfa_required`), `config/services.php`
  (`google_analytics`), `compose.yaml` (služby `queue`, `scheduler`), nginx (`/storage`).
- Nové `.env` premenné: `ADMIN_URL`, `ADMIN_MFA_REQUIRED`, `GOOGLE_ANALYTICS_ID`.

## Testy
- Unit: `TimeSlotTest`, `UntranslatedTextScannerTest` (rozšírený o šípky v direktívach).
- Feature: `AvailabilityTest`, `BookingTest` (vrátane súbehu a okrajových prípadov), `ContactTest`,
  `PublicPagesTest`, `HomePageTest`, `DomainSeparationTest`, `ErrorPagesTest`, `WebpImageTest`, `CommandsTest`,
  `EnumLabelsTest`, `Admin/*` (Livewire testy všetkých resources a stránok).
- Browser: `BookingFlowTest` (celá rezervácia, plný termín, ďalší mesiac), `AdminTest` (prihlásenie na admin
  doméne a zrušenie rezervácie), `HomePageTest` (všetky stránky bez JS chýb, písma, cookie lišta, pribúdanie pilierov a kotva Workshopy).
- Ručne: `docker compose exec app php artisan migrate:fresh --seed`, http://bezvahania.localhost,
  http://admin.bezvahania.localhost (`admin@bezvahania.localhost` / `password`), e-maily v http://localhost:8025.

## Nasadenie
Nenasadzuje sa. Pred prvým nasadením: overiť na hostingu GD s WebP, `/storage` symlink, cron pre scheduler a frontu,
SMTP; vytvoriť admina cez `app:create-admin`; `ADMIN_MFA_REQUIRED=true`; `GOOGLE_ANALYTICS_ID=G-Z690RSSEM0`;
na produkcii spustiť iba `db:seed --class=ContentSeeder`.

## Odkazy
[ADR 0005](../adr/0005-unique-slot-booking.md), [ADR 0006](../adr/0006-translatable-content-and-webp.md),
[features/](../features/README.md), [architecture.md](../architecture.md).
