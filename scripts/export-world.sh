#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
WORLD_DIR="${1:-$ROOT/worlds/VivaArcade}"
OUT="${2:-$ROOT/dist/VivaArcade-World.zip}"

if [[ "${VIVAARCADE_SERVER_STOPPED:-0}" != "1" ]]; then
  echo "Stop PocketMine-MP cleanly, then rerun with VIVAARCADE_SERVER_STOPPED=1" >&2
  exit 1
fi
[[ -d "$WORLD_DIR" ]] || { echo "World directory not found: $WORLD_DIR" >&2; exit 1; }
[[ -s "$WORLD_DIR/level.dat" ]] || { echo "level.dat is missing or empty; this does not look like a saved PMMP world" >&2; exit 1; }
[[ -d "$WORLD_DIR/db" ]] || { echo "LevelDB directory db/ is missing; refusing to export an incomplete world" >&2; exit 1; }
[[ -n "$(find "$WORLD_DIR/db" -type f -print -quit)" ]] || { echo "LevelDB directory db/ is empty; refusing to export an incomplete world" >&2; exit 1; }
for required in CURRENT LOCK LOG; do
  [[ -f "$WORLD_DIR/db/$required" ]] || { echo "LevelDB metadata file db/$required is missing" >&2; exit 1; }
done
[[ -n "$(find "$WORLD_DIR/db" -maxdepth 1 -type f -name 'MANIFEST-*' -print -quit)" ]] || { echo "LevelDB MANIFEST is missing" >&2; exit 1; }
command -v zip >/dev/null || { echo "zip utility is required" >&2; exit 1; }
command -v unzip >/dev/null || { echo "unzip utility is required" >&2; exit 1; }
mkdir -p "$(dirname "$OUT")"
rm -f "$OUT"
WORLD_PARENT="$(cd "$(dirname "$WORLD_DIR")" && pwd)"
WORLD_NAME="$(basename "$WORLD_DIR")"
(cd "$WORLD_PARENT" && zip -qr "$OUT" "$WORLD_NAME")
unzip -t "$OUT" >/dev/null
unzip -Z1 "$OUT" | grep -Fx "$WORLD_NAME/level.dat" >/dev/null
unzip -Z1 "$OUT" | grep -E "^$WORLD_NAME/db/(CURRENT|MANIFEST-[^/]+)$" >/dev/null
printf 'World archive created: %s\n' "$OUT"
sha256sum "$OUT"
