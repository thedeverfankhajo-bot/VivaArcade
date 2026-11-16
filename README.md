# VivaArcade

**Native Minecraft Bedrock arcade foundation for PocketMine-MP API 5**

PHP 8.1+ · Dedicated `VivaArcade` world · Native Bedrock Forms · UUID player profiles

> **Release scope:** this integration adds a PocketMine plugin foundation, first-run dedicated-world builder, native Bedrock forms, Solo Parkour Rush and UUID-based reward persistence. Dependency-light matchmaking, party and match-snapshot services are also retained and tested. This is not yet the requested 24+ mode production arcade network.

## Implemented

- PocketMine-MP API 5 plugin entry point and registered player commands.
- Dedicated `VivaArcade` world load/create through the official PocketMine WorldManager.
- First-run plaza, decorative pylons, connector and Parkour Rush course builder.
- Existing world data is not cleared. Existing unmarked worlds are preserved and gameplay is disabled until a reviewed migration has been completed.
- Native Bedrock JSON form for the main menu and game selector; no external FormAPI dependency.
- Solo Parkour Rush with server-side timer, checkpoint, fall recovery, finish and server-side reward.
- UUID-keyed JSON player profiles, per-player file locks, atomic replacement and duplicate-transaction protection.
- Lobby block break/place protection and active Parkour disconnect cleanup.
- Dependency-light arena ownership, matchmaking queue, isolated match-session and UUID party core services.
- Versioned, validated match snapshot codec with deterministic snapshot regression checks.
- World export script that refuses to invent or export a missing LevelDB world.

## Not yet implemented / not connected to the live PMMP adapter

The following requested systems are **not complete**: 24+ playable minigames, live matchmaking and independent arena instances, party forms/chat and party queue integration, teams/private matches, durable match recovery, a general-purpose economy transaction API and cosmetic shops, daily/weekly challenges, tournaments/events, live leaderboards, admin panel, SQLite adapter, spectator mode, full inventory/state restoration, and the large multi-district world design.

The existing queue and party classes are core primitives; they are not yet wired to live commands, forms, a scheduler or PocketMine player events. Do not interpret a registered core game ID as a live playable minigame.

## Requirements

- PocketMine-MP 5 on a supported PHP 8.1+ runtime.
- PHP CLI with `Phar`, `json`, `hash` and `zlib` extensions for packaging.
- `sha256sum` for the final checksum.

## Build

```bash
chmod +x build.sh
./build.sh
```

The build packages the repository's real `src/`, `resources/` and root `plugin.yml`; it no longer attempts to extract the damaged historical `dist/source.zip`. It runs PHP syntax checks, core regressions, UUID storage tests and match-snapshot tests, validates the PHAR manifest and writes `dist/SHA256SUMS.txt`.

## Install

1. Build the PHAR using the command above.
2. Copy `dist/VivaArcade.phar` into the PocketMine-MP `plugins/` directory.
3. Start PocketMine-MP. On first launch, the plugin asks PocketMine to create `worlds/VivaArcade/` and populates the initial plaza and Parkour Rush course.
4. Join with a Bedrock client and use `/arcade`.

The code has not yet been validated on a live PocketMine server or Bedrock client. Test on a disposable PMMP 5 server before public deployment.

## Commands currently registered

| Command | Description |
|---|---|
| `/arcade` | Open the main menu |
| `/games` | Browse the implemented game mode |
| `/profile` | View level, XP, Dollar and profile statistics |
| `/stats` | View basic game statistics |
| `/balance` | View Dollar balance |
| `/leave` | Leave active Parkour Rush |

Other commands from the long-term specification are not registered yet.

## World and arena management

The world name is `VivaArcade` and is managed through PocketMine's official WorldManager. The first-run builder records its layout version and state in the plugin data folder (`world-layout-v1.json`).

The builder populates a new world, or resumes a build explicitly marked `building`. It does not clear an existing unmarked map. Back up an existing world before a reviewed migration; do not delete the state file to force regeneration.

## Player data and economy

Profiles are stored under the plugin data folder with filenames based on player UUID. JSON writes use a per-player lock file and atomic rename. A malformed profile is not silently replaced with defaults.

Parkour completion uses a unique server-created transaction ID to prevent duplicate credits. The current Dollar field is a limited Parkour reward balance, not yet the full economy, shop or transaction-history feature set requested.

## Dedicated world export

A LevelDB world archive can only be exported after a real PocketMine runtime has generated and saved the world. Stop PocketMine cleanly, then run:

```bash
VIVAARCADE_SERVER_STOPPED=1 ./scripts/export-world.sh
```

The script requires `worlds/VivaArcade/level.dat` and a non-empty `worlds/VivaArcade/db/` LevelDB directory. It refuses to fabricate a world ZIP. No runtime-generated world ZIP is included with this source integration.

## Tests and verification boundary

```bash
php tests/run.php
php tests/storage.php
php tests/snapshot.php
```

These tests cover dependency-light match/queue/party services and JSON reward persistence. They do not simulate PocketMine startup, LevelDB, Bedrock forms, in-world movement physics, load or client rendering. See [docs/STATUS.md](docs/STATUS.md), [docs/TEST-RESULTS.md](docs/TEST-RESULTS.md) and [docs/BRANCH-MERGE.md](docs/BRANCH-MERGE.md).
