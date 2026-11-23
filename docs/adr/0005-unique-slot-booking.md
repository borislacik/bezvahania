# ADR 0005: Rezervácia celého termínu a unikátny index namiesto zámku riadku

- Stav: prijaté
- Dátum: 2026-11-23

## Kontext
Zadanie počítalo s termínmi (`CourseSession`) s kapacitou 1 – 4 miesta a zámkom `lockForUpdate()` na termíne.
Prevádzkovateľ rozhodol inak ([decisions.md](../decisions.md)): zákazník si rezervuje **celý termín**
(deň + doobeda/poobede) pre svoju skupinu, admin vopred nevypisuje termíny, iba blokuje dni.
Neexistuje teda riadok „termínu“, ktorý by sa dal zamknúť.

## Rozhodnutie
- Tabuľka `bookings` má stĺpec `active_slot` = `RRRR-MM-DD:<slot>` pre rezervácie, ktoré termín obsadzujú,
  a `NULL` pre zrušené. Na stĺpci je **unikátny index**.
- Hodnotu nastavuje model `Booking` pri každom uložení podľa stavu.
- `App\Actions\Bookings\CreateBooking` najprv overí dostupnosť (priateľská chyba), potom vloží riadok
  v transakcii; porušenie unikátneho indexu sa preloží na `SlotUnavailableException`.
- Blokované dni sú v tabuľke `blocked_days` (jeden riadok na deň).
- Typy workshopov (`courses`) sú oddelené od termínov; typ si zákazník vyberá až po termíne.

## Dôsledky
- Dvojitá rezervácia je nemožná aj pri súbežných požiadavkách (garantuje to databáza, test
  `BookingTest › never lets two bookings occupy the same slot`).
- Zrušenie termín automaticky uvoľní (`active_slot = NULL`).
- Ak by sa neskôr zaviedlo viac rezervácií na jeden termín, bude to nová migrácia a nové ADR.

## Zvažované alternatívy
- **`lockForUpdate()` na pomocnej tabuľke termínov** – zbytočná tabuľka a zložitejší kód.
- **`GET_LOCK()` v MariaDB** – závislé od spojenia, horšie testovateľné.
- **Generovaný stĺpec v DB** – funkčne rovnaké, ale menej prenositeľné; hodnotu nastavuje aplikácia.
