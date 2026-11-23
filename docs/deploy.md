# Nasadenie a rollback

> Stav: pripravuje sa vo Fáze 2. Staging neexistuje, nasadzuje sa iba na produkciu a iba na výslovný pokyn.

Princíp: [ADR 0004](adr/0004-build-assets-locally.md) – lokálny build v Dockeri, upload hotového balíka cez SSH,
atomické prepnutie releasu, smoke test a automatický rollback.
