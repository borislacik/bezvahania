#!/usr/bin/env bash
# Stops Playwright servers left behind by interrupted or timed-out browser test runs.
# They pile up in the app container, eat its memory and make the next browser test run time out.
# Only node processes running "playwright run-server" are stopped (never the calling shell).
set -euo pipefail

for proc in /proc/[0-9]*; do
    pid="${proc#/proc/}"
    [[ "$pid" == "$$" ]] && continue

    executable=$(tr '\0' '\n' < "$proc/cmdline" 2>/dev/null | head -n1 || true)
    cmdline=$(tr '\0' ' ' < "$proc/cmdline" 2>/dev/null || true)

    if [[ "$(basename -- "$executable")" == "node" && "$cmdline" == *"playwright"*"run-server"* ]]; then
        kill "$pid" 2>/dev/null || true
    fi
done
