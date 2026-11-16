# VivaArcade

**Native Minecraft Bedrock arcade plugin for PocketMine-MP API 5**  
PHP 8.1+ · dedicated `VivaArcade` world · Finglish player-facing UI

> **Delivery status: Phase 1 gameplay plus tested Phase 2 core primitives. This is not yet the complete arcade-network specification.**

## Current project contents

The existing `dist/source.zip` is the authoritative Phase 1 source bundle and is preserved unchanged. It contains the original PMMP API 5 plugin, 15 solo mini-game modes, first-run dedicated-world builder, native Bedrock forms, UUID player profiles, daily rewards/challenges, locked JSON Dollar economy, one cosmetic and basic leaderboards.

This repository also contains dependency-light Phase 2 core primitives:

- isolated match state machine with server-owned participant and score state;
- FIFO queue coordinator with one-queue-per-UUID and one-active-match-per-player protection;
- exclusive arena reservation and owner-scoped release;
- UUID-based party membership with strict invite-expiry deadlines and leader management;
- validated version-1 MatchSession snapshots for trusted storage adapters (serialization only; not persistent recovery);
- 58 deterministic core checks, including simultaneous isolated match sessions.

These Phase 2 classes are not yet connected to live PMMP commands, forms, scheduler, game adapters or persistent crash recovery. See [docs/PHASE2-CORE.md](docs/PHASE2-CORE.md) and [docs/STATUS.md](docs/STATUS.md).

## Requirements

- PocketMine-MP 5 on a supported PHP 8.1+ runtime.
- PHP CLI with `Phar`, `json`, `hash`, and `zlib` extensions for packaging.
- `unzip` for extracting the original source bundle. `zip` is needed only for world export.

## Build

```bash
chmod +x build.sh
./build.sh
```

The build script validates `dist/source.zip`, extracts it into a temporary staging directory, overlays repository `src/` and `resources/` files without deleting the original files, lints every PHP file, runs the core tests, creates a real `dist/VivaArcade.phar`, validates the PHAR manifest and writes SHA-256 checksums.

The build intentionally fails when the source archive is missing or invalid rather than producing a partial PHAR.

## Install plugin

1. Build `dist/VivaArcade.phar` using the command above.
2. Copy it to the PocketMine-MP server's `plugins/` directory.
3. Start the server and inspect the server console for plugin errors.
4. Connect using Minecraft Bedrock and use `/arcade` or `/games` from the Phase 1 source.

The plugin generates/loads the `VivaArcade` world through PocketMine's world manager. Do not manually create a placeholder `worlds/VivaArcade` folder.

## Dedicated World export

A LevelDB world archive is **not** committed because no PocketMine runtime is available in this build environment to generate and verify it. After starting the plugin on the target PMMP server, allow it to save the world, stop PocketMine cleanly, and run:

```bash
VIVAARCADE_SERVER_STOPPED=1 ./scripts/export-world.sh
```

The script checks for `level.dat` and the `db/` LevelDB directory, writes `dist/VivaArcade-World.zip`, verifies the ZIP, and prints its SHA-256. It refuses to export while the explicit stopped-server flag is absent.

## Existing player commands

The current Phase 1 plugin documents `/arcade`, `/games`, `/profile`, `/challenges`, `/rewards`, `/shop`, `/top`, and `/arcadeadmin`. Consult `dist/README-INSTALL.txt` and the in-game help for the exact commands provided by the original source bundle.

## Verification boundary

Local PHP syntax and pure-core tests can run without PocketMine. They do not prove plugin startup, Bedrock form compatibility, world/LevelDB loading, gameplay correctness, concurrency safety under load or client rendering. These require an actual PMMP 5 server and Bedrock clients. See [docs/TEST-RESULTS.md](docs/TEST-RESULTS.md).
