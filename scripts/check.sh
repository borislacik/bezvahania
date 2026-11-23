#!/usr/bin/env bash
# composer check: all quality gates. Writes the HEAD SHA to .check-passed only when
# everything passes AND the working tree is clean (docs/brief.md, section 7.2).
set -euo pipefail
cd "$(dirname "$0")/.."

rm -f .check-passed

composer lint
composer analyse
composer test
npm run build
composer test:browser
composer docs:check

if [[ -n "$(git status --porcelain)" ]]; then
    echo "check: všetko prešlo, ale pracovný strom nie je čistý – .check-passed sa nezapísal." >&2
    exit 1
fi

git rev-parse HEAD > .check-passed
echo "check: OK, .check-passed = $(cat .check-passed)"
