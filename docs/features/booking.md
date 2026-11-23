# Rezervácie

## Ako to funguje pre zákazníka
1. Na stránke `/rezervacia` vidí kalendár **aktuálneho a nasledujúceho mesiaca**.
2. Vyberie **deň** (sivé dni sú obsadené, zablokované adminom alebo mimo okna rezervácií).
3. Vyberie **časť dňa**: doobeda 9:00 – 13:00 alebo poobede 15:00 – 19:00.
4. Vyberie **typ workshopu** (predvolený je workshop označený v admine ako „Predvolený vo formulári“,
   alebo ten, z ktorého karty prišiel – `?workshop=<slug>`).
5. Vyplní kontakt, **adresu konania**, počet osôb (1 – 4), poznámku a súhlas s podmienkami.
6. Rezervácia sa **potvrdí hneď** (bez schvaľovania adminom), zákazník dostane e-mail a vidí potvrdenie
   na podpísanej adrese `/rezervacia/<číslo>` (platnosť 7 dní).

Formulár funguje aj bez JavaScriptu (radio prvky). Alpine.js iba prepína mesiace, zakazuje obsadené časti dňa
a zobrazuje súhrn.

## Pravidlá dostupnosti (`App\Support\Availability`)
- Okno rezervácií: od **dnes + `booking_lead_days`** (nastavenie v admine, predvolene 2 dni) do **konca
  nasledujúceho mesiaca**.
- Deň zablokovaný v admine (`blocked_days`) je nedostupný celý.
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

Lokálne ich zachytáva Mailpit (http://localhost:8025), frontu spracúva služba `queue`.

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
