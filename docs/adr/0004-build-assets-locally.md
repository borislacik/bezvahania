# ADR 0004: Build assetov a `vendor/` lokálne, na server ide hotový balík

- Stav: prijaté (deploy skript vo Fáze 2)
- Dátum: 2026-11-23

## Kontext
Na zdieľanom hostingu nepredpokladáme Node.js a nechceme, aby server sťahoval balíky z Packagistu či npm.

## Rozhodnutie
- Deploy balík sa zostaví lokálne v Docker kontajneri z `git archive HEAD`: `composer install --no-dev
  --optimize-autoloader`, `npm ci && npm run build`. Bez `node_modules`, `tests` a dev súborov.
- Nasadí sa iba commit, pre ktorý prešiel `composer check` (`.check-passed` = `HEAD`).
- Písma sa sťahujú pri builde a servírujú z vlastnej domény (žiadne CDN tretích strán, GDPR).

## Dôsledky
- Na serveri beží presne to, čo prešlo testami.
- Build závisí od lokálneho Dockeru, nie od hostingu.

## Zvažované alternatívy
- **`composer install` a build na serveri** – závislosť na hostingu, Node na serveri nie je.
- **CI build (GitHub Actions)** – CI podľa rozhodnutia nebude.
