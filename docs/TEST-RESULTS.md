# Test Results

## GitHub Actions verification

The pull-request workflow ran on 2026-11-16 using PHP 8.1.34.

- Shell syntax checks for `build.sh` and `scripts/export-world.sh`: **PASS**.
- PHP syntax lint for all tracked Phase 2 PHP sources and tests: **PASS**.
- Pure-core test suite: `php tests/run.php` — **PASS: 60 checks**. Coverage includes queue uniqueness, arena reservation ownership, parallel-match isolation, match lifecycle, active-match player locks, score validation, disconnect/reconnect, duplicate reward prevention, validated snapshot round-trips and tamper rejection, exact-deadline party invite expiry, membership uniqueness and party leadership transfer.
- PHAR packaging: **FAIL**. `unzip -t dist/source.zip` reports a missing end-of-central-directory record. The committed archive begins with ZIP data but is not a structurally readable ZIP archive, so extraction and PHAR creation cannot safely continue.

## Not run / not verified

- Rebuilding `dist/VivaArcade.phar` until a valid `dist/source.zip` is supplied.
- Loading the plugin inside a live PocketMine-MP 5 process.
- PMMP LevelDB world generation, restart/load, or export/import.
- Minecraft Bedrock client GUI and gameplay testing.
- End-to-end tests for the original 15 game modes (their source bundle could not be extracted because the ZIP fails integrity validation).
- Runtime integration of queue, party and snapshot services with PMMP commands, forms, scheduler and concrete game adapters.
- Multiplayer load/concurrency tests and persistent crash-recovery tests.
- Exporting `dist/VivaArcade-World.zip`.

No PHAR or LevelDB world archive is claimed as built. The dedicated world must be generated and saved by an actual PocketMine-MP runtime, then exported only after a clean server shutdown using `scripts/export-world.sh`.
