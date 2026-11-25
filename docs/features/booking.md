# Rezervácie

## Ako to funguje pre zákazníka
1. Na stránke `/rezervacia` vidí kalendár **aktuálneho a nasledujúceho mesiaca**.
2. Vyberie **deň** (sivé dni sú obsadené, zablokované adminom alebo mimo okna rezervácií; deň s jedným voľným
   termínom ostáva voľný).
3. Vyberie **časť dňa**: doobeda 9:00 – 13:00 alebo poobede 15:00 – 19:00.
4. Vyberie **typ workshopu** (predvolený je workshop označený v admine ako „Predvolený vo formulári“,
   alebo ten, z ktorého karty prišiel – `?workshop=<slug>`).
5. Vyplní kontakt, **adresu konania**, prípadne **fakturačné údaje** (iba ak sú iné – zaškrtávacie pole), počet osôb (2 – 4, `Booking::MIN_PARTICIPANTS`/`MAX_PARTICIPANTS`), poznámku a súhlas s obchodnými a storno podmienkami.
6. Rezervácia sa **potvrdí hneď** (bez schvaľovania adminom), zákazník dostane e-mail a vidí potvrdenie
   na podpísanej adrese `/rezervacia/<číslo>` (platnosť 7 dní).

Formulár funguje aj bez JavaScriptu (radio prvky). Alpine.js iba prepína mesiace, zakazuje obsadené časti dňa
a zobrazuje súhrn.

## Pravidlá dostupnosti (`App\Support\Availability`)
- Okno rezervácií: od **dnes + `booking_lead_days`** (nastavenie v admine, predvolene 2 dni) do **konca
  nasledujúceho mesiaca**.
- Admin blokuje celý deň alebo iba jeden termín (`blocked_days.slot` = `day`, `morning`, `afternoon`, jeden riadok
  na dátum a časť dňa). Zablokovaný termín je nedostupný; celý deň blokuje oba termíny.
- Časť dňa s aktívnou rezerváciou je obsadená. Zrušená rezervácia termín uvoľní.

## Ochrana pred dvojitou rezerváciou
Rezervácia má stĺpec `active_slot` (`RRRR-MM-DD:morning|afternoon`), ktorý je vyplnený, kým rezervácia
termín obsadzuje, a `NULL` po zrušení. Na stĺpci je **unikátny index**, takže ani dve súbežné požiadavky
nemôžu vytvoriť dve rezervácie na ten istý termín. Pozri [ADR 0005](../adr/0005-unique-slot-booking.md).

## Stavy (`App\Enums\BookingStatus`)
| Stav | Význam | Obsadzuje termín |
|---|---|---|
| `confirmed` | potvrdená | áno |
| `completed` | uskutočnená (admin po workshope) | áno |
| `no_show` | neuskutočnená (zákazník neprišiel) | áno |
| `cancelled` | zrušená (adminom, s dôvodom) | nie |

## E-maily (fronta, Markdown šablóny v `resources/views/mail`)
| E-mail | Komu | Kedy |
|---|---|---|
| `BookingConfirmedMail` | zákazník | nová rezervácia (web aj admin, ak je zapnuté „Poslať potvrdenie“) |
| `NewBookingAdminMail` | `notification_email` z nastavení | nová rezervácia z webu |
| `BookingCancelledMail` | zákazník | zrušenie rezervácie adminom |
| `ContactMessageAdminMail` | `notification_email` | správa z kontaktného formulára |

Odosielateľ všetkých e-mailov je `MAIL_FROM_ADDRESS` = `rezervacie@bezvahania.sk` (iba automatické e-maily).
E-maily zákazníkom majú Reply-To na kontaktný e-mail (`contact_email`, `info@bezvahania.sk`), upozornenia
prevádzkovateľovi idú na `notification_email` (predvolene `info@bezvahania.sk`). Telefón a e-mail na webe sa
menia v admine (**Nastavenia → Kontakt a prevádzkovateľ**).

Lokálne ich zachytáva Mailpit (http://localhost:8025), frontu spracúva služba `queue`.

## Kontaktné formuláre
- Formulár v bloku „Máte špecifické požiadavky alebo firmu?“ (`components/site/contact-cta.blade.php`), na úvodnej
  stránke je to sekcia `#kontakt`, zobrazuje sa aj na detaile workshopu: meno, e-mail, telefón (nepovinne), správa.
- Kontaktné formuláre **nemajú zaškrtávacie pole súhlasu**, iba informačnú vetu o spracovaní osobných údajov
  (`components/site/privacy-notice.blade.php`). `consent_at` pri správe zaznamenáva čas odoslania s touto informáciou.
  Rezervačný formulár zaškrtávacie pole má (súhlas s obchodnými a storno podmienkami).
- Posiela na `contact.store` (`POST /kontakt`): správa sa uloží do admina (**Správy z webu**) a príde e-mailom na
  `notification_email` (predvolene `info@bezvahania.sk`) s Reply-To na odosielateľa.
- Po odoslaní (aj pri chybe) sa návštevník vráti na tú istú stránku k formuláru `#kontaktny-formular`
  (`App\Support\ContactRedirect`, iba v rámci vlastnej domény).

## Ochrana formulárov
- Rate limit: rezervácia 5 za minútu, kontakt 3 za minútu (na IP).
- Honeypot pole `website` (skryté, vyplnené = odmietnuté).
- Súhlas s podmienkami a spracovaním údajov (čas a verzia súhlasu sa ukladajú pri rezervácii).

## GDPR
- Rezervácie sa **anonymizujú 3 roky po termíne** (`app:anonymize-bookings`, denne cez scheduler).
- Správy z kontaktného formulára sa **mažú po 12 mesiacoch** (`model:prune`, denne).
- Osobné údaje sa nezapisujú do logov.

## Otvorené
- Zrušenie rezervácie zákazníkom (odkaz v e-maile, lehota) – upresní prevádzkovateľ.
- Pripomienkový e-mail pred workshopom – zatiaľ nie je.

## Zrušenie rezervácie
- Zákazník si rezerváciu na webe sám nezruší; zrušenie alebo presun oznámi e-mailom (storno podmienky vyžadujú
  preukázateľné oznámenie, rozhoduje deň doručenia). Rezerváciu potom zruší admin (akcia **Zrušiť rezerváciu**).
- Storno pravidlá (bez poplatku 7 a viac dní pred termínom, 3 – 6 dní 20 %, menej ako 72 hodín 40 %) sú na stránke
  `/storno-podmienky` a skrátene v potvrdzovacom e-maile, na potvrdzovacej stránke, vo formulári a v FAQ.
  Pri zmene podmienok treba upraviť aj texty v `lang/sk/booking.php`, `lang/sk/mail.php` a otázku v `ContentSeeder`.
