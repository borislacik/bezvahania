# chore/new-change-skill – skill pre postup jednej požiadavky

## Požiadavka
Vytvoriť navrhnutý skill `new-change` (zadanie, sekcia 10.3). Používateľ s ním súhlasil 23. 11. 2026.

## Riešenie
Skill `.claude/skills/new-change/SKILL.md` skladá pravidlá z `CLAUDE.md` a zadania do jedného poradia:
kontrola stacku a čistého `main` → vetva → implementácia → testy → dokumentácia (záznam, CHANGELOG, README, ADR)
→ commit a `composer check` → zhrnutie → merge a push iba po súhlase. Obsahuje šablónu záznamu v `docs/changes/`.
Pravidlá neduplikuje do detailu, odkazuje na `CLAUDE.md`, `docs/brief.md` a `docs/decisions.md`.

## Dotknuté časti
- `.claude/skills/new-change/SKILL.md` (nový)
- `CLAUDE.md` – odkaz na skill
- `README.md` – zoznam skills, odkaz na GitHub repozitár
- `CHANGELOG.md`

## Testy
Bez zmeny kódu. `composer docs:check` overuje frontmatter skillu (`name`, `description`) a odkazy.

## Nasadenie
Nič sa nenasadzuje.

## Odkazy
[Záznam Fázy 1](2026-11-23-chore-phase-1-skeleton.md)
