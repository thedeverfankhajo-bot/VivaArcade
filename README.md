# VivaArcade

**Native Minecraft Bedrock arcade plugin for PocketMine-MP API 5**  
PHP 8.1+ · dedicated `VivaArcade` world · Finglish player-facing UI

> **Delivery status: Phase 2 core safeguards are implemented and verified by CI (71 checks). The Phase 1 source archive is truncated, so a complete production plugin and PHAR cannot currently be reconstructed. This is not the requested complete arcade network.**

## Current project contents

`dist/source.zip` is present but **invalid/truncated** (7,501 bytes; no ZIP end-of-central-directory record). The archive ends partway through `src/VivaArcade/Lobby/LobbyManager.php`, so it cannot be extracted or used to build a complete plugin. Older project notes describe a 15-mode Phase 1 implementation, but those files are not recoverable from the currently committed archive. Restore a complete source archive before treating that baseline as installable or playable.

This repository also contains dependency-light Phase 2 core primitives:

- isolated match state machine with server-owned participant and score state;
- FIFO queue coordinator with one-queue-per-UUID and one-active-match-per-player protection;
- exclusive arena reservation and owner-scoped release;
- connected-player minimum checks before match countdown/start, disconnect/reconnect routing and bounded terminal match history;
- UUID-based party membership with invite acceptance, decline/cancellation, strict expiry and leader management;
- 71 deterministic core checks, including connected-player minimums, invite expiry and simultaneous isolated match sessions.

These Phase 2 classes remain dependency-light core services and are not yet connected to live PMMP commands, forms, scheduler, game adapters, persistent match recovery or in-game party delivery/chat. The new safeguards are covered by deterministic core regressions, but they do not constitute a playable multiplayer network. See [docs/PHASE2-CORE.md](docs/PHASE2-CORE.md) and [docs/STATUS.md](docs/STATUS.md).

## Requirements

- PocketMine-MP 5 on a supported PHP 8.1+ runtime.
- PHP CLI with `Phar`, `json`, `hash`, and `zlib` extensions for packaging.
- `unzip` for extracting the original source bundle. `zip` is needed only for world export.

## Build

```bash
chmod +x build.sh
./build.sh
```

The build script checks the ZIP archive before extracting it. It currently **fails intentionally** because `dist/source.zip` is truncated and lacks its ZIP directory/footer. No `dist/VivaArcade.phar` has been produced. After the original complete source is restored, the build will lint PHP, run core tests, create and validate the PHAR manifest, then write SHA-256 checksums. It refuses to package an incomplete plugin.

## Install plugin

Installation is **blocked** until the complete Phase 1 source archive is restored and a PHAR is built. Do not install a placeholder or partial PHAR. Live PocketMine-MP startup and Bedrock client verification have not been performed.

## Dedicated World export

A LevelDB world archive is **not** committed because no PocketMine runtime is available in this build environment to generate and verify it. After starting the plugin on the target PMMP server, allow it to save the world, stop PocketMine cleanly, and run:

```bash
VIVAARCADE_SERVER_STOPPED=1 ./scripts/export-world.sh
```

The script checks for `level.dat` and the `db/` LevelDB directory, writes `dist/VivaArcade-World.zip`, verifies the ZIP, and prints its SHA-256. It refuses to export while the explicit stopped-server flag is absent.

## Existing player commands

Older Phase 1 documentation lists `/arcade`, `/games`, `/profile`, `/challenges`, `/rewards`, `/shop`, `/top`, and `/arcadeadmin`. These commands have not been verified from the damaged source archive and should not be treated as an installable command set.

## Verification boundary

GitHub Actions verified PHP 8.1 syntax, shell-script syntax and 71 pure-core regression checks. The PHAR build then correctly stopped because the source ZIP is malformed. No plugin startup, Bedrock forms, world/LevelDB loading, gameplay, load testing or client rendering has been verified. See [docs/TEST-RESULTS.md](docs/TEST-RESULTS.md) and [docs/SOURCE-RECOVERY.md](docs/SOURCE-RECOVERY.md).
