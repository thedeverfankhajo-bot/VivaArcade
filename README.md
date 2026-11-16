# VivaArcade

**Native Minecraft Bedrock arcade foundation for PocketMine-MP API 5**

PHP 8.1+ · Dedicated VivaArcade world · Native Bedrock Forms · UUID player profiles

> Delivery status: This branch adds a real PocketMine plugin entry point, safe first-run world builder, native form menu, Solo Parkour Rush and UUID-based JSON profile/reward persistence. It is a foundation release, not the full 24+ mode arcade network. See docs/STATUS.md.

## Implemented in this branch

- PMMP plugin entry point (VivaArcade\Main) and player commands.
- Dedicated world load/create through PocketMine WorldManager.
- Idempotent first-run plaza, decorative pylons, connector and a Parkour Rush course.
- Existing world data is not wiped or regenerated. Existing worlds are only built if the plugin layout state says a previous first-run build was interrupted; otherwise an unmarked world is preserved and the game is disabled.
- Native Bedrock JSON form for the main menu and game selector; no external FormAPI dependency.
- Solo Parkour Rush with a server-side timer, checkpoint, fall recovery, finish and reward.
- UUID-keyed JSON player profiles, advisory file locking and atomic file replacement.
- Idempotent Parkour reward transaction IDs, capped transaction history, XP, Dollar balance and basic profile statistics.
- Lobby block break/place protection.
- Disconnect cleanup for active Parkour sessions.
- Deterministic regression tests for the existing matchmaking/party core and additional storage tests.

## Requirements

- PocketMine-MP 5.
- PHP 8.1 or newer (PHP version supported by the selected PMMP release).
- PHP CLI extensions: Phar, json, hash, zlib for packaging.
- sha256sum for the final checksum file.

## Build the plugin

    chmod +x build.sh
    ./build.sh

The build creates dist/VivaArcade.phar, lints PHP, runs core and storage checks, validates required PHAR entries and creates dist/SHA256SUMS.txt.

The build no longer extracts the damaged historical dist/source.zip; the repository's src/, resources/ and root plugin.yml are authoritative.

## Install

1. Build the PHAR using the command above.
2. Copy dist/VivaArcade.phar into the PocketMine-MP plugins/ directory.
3. Start PocketMine-MP. On first launch the plugin generates and populates worlds/VivaArcade/.
4. Join with a Bedrock client and use /arcade.

The current implementation is not a production-ready public network. Test it on a disposable PMMP 5 server before deploying it.

## Commands currently registered

| Command | Description |
|---|---|
| /arcade | Open the main menu |
| /games | Browse the implemented game mode |
| /profile | View level, XP, Dollar and profile stats |
| /stats | View basic game statistics |
| /balance | View Dollar balance |
| /leave | Leave an active Parkour Rush |

/arcadeadmin, shop, daily reward, queue, party, tournament and the other requested commands are not implemented in this branch.

## World and arena management

The world name is VivaArcade and is managed through PocketMine's official WorldManager. The first-run builder records its version/state in the plugin data folder (world-layout-v1.json).

The builder only populates a newly generated world, or resumes a build explicitly marked building. It does not clear an existing world or overwrite an existing unmarked map. To install the generated layout into a pre-existing world, back it up and use a separately reviewed migration tool; do not delete the world state file to force an overwrite.

## Player data and economy

Profiles are stored under the plugin data folder, with filenames based on player UUID. JSON writes are serialized by a per-player lock file and committed by atomic rename. A malformed profile is not silently replaced with defaults.

Dollar rewards are generated and validated server-side. Parkour completion includes a unique server-created transaction ID to prevent duplicate credits. This is not yet a general-purpose economy API or full shop.

## Dedicated world export

A LevelDB world archive can only be exported after a real PocketMine runtime has generated and saved the world. Stop PocketMine cleanly, then run:

    VIVAARCADE_SERVER_STOPPED=1 ./scripts/export-world.sh

The script requires worlds/VivaArcade/level.dat and a non-empty worlds/VivaArcade/db/ LevelDB directory. It refuses to fabricate a world ZIP.

## Configuration

resources/config.yml controls the Parkour Rush Dollar and XP rewards:

    parkour:
      reward-dollar: 50
      reward-xp: 30

## Tests and runtime verification

    php tests/run.php
    php tests/storage.php

These tests exercise dependency-light core state machines and JSON reward persistence. They do not simulate PocketMine, LevelDB, Bedrock forms or in-world physics. No PMMP runtime or Bedrock client validation is claimed here.

## Troubleshooting

- Plugin does not enable: check logs/latest.log, confirm PocketMine-MP API 5 and PHP requirements, and verify the PHAR.
- Game menu says arenas are not ready: the world existed without this plugin layout state, so it was intentionally preserved. Back it up and follow a reviewed migration procedure instead of forcing a rebuild.
- Profile storage error: check filesystem permissions in plugin_data/VivaArcade/players/. Invalid JSON is deliberately not overwritten.
- World export fails: verify PocketMine is fully stopped and that the real LevelDB db/ directory and level.dat exist.

## Roadmap / not completed

24+ playable game modes, live matchmaking and arena instances, party invitations/chat, teams/private matches, persistent match recovery, events/tournaments, challenges, full cosmetics shop, leaderboard displays, admin forms, SQL adapter and Bedrock integration/load testing remain future work.
