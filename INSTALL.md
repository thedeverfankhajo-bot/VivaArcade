# VivaArcade installation

## Requirements
- PocketMine-MP 5.x with a PHP 8.1+ runtime supported by that PMMP release.
- PHP CLI with Phar, json, hash and zlib extensions for building.
- A clean, backed-up test server for first installation.

## Build the plugin

From the repository root:

~~~bash
chmod +x build.sh
./build.sh
~~~

The build executes syntax checks, core, storage, economy, daily reward, match snapshot and world-layout tests, then validates the PHAR and writes:
- `dist/VivaArcade.phar`
- `dist/SHA256SUMS.txt`

Latest verified 0.6.1 CI run: [#43976065690](https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43976065690). Download and extract artifact [#14280029041](https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43976065690/artifacts/14280029041).

## Install
1. Extract `VivaArcade.phar` from the CI artifact.
2. Copy it into PocketMine's `plugins/` directory.
3. Start PocketMine-MP 5 and wait for the VivaArcade world build message.
4. On a new world, PocketMine creates `worlds/VivaArcade/` and the plugin incrementally constructs the plaza, district paths and 19 district areas.
5. Join with a Bedrock client and run `/arcade`.
6. Run `/daily` for the once-per-UTC-day Dollar and XP reward.

The world builder is scheduled in bounded main-thread batches (one chunk and 180 block writes per tick by default). The lobby is marked playable only after successful completion.

## Existing VivaArcade worlds
- A layout marker with state `building` is replayed safely on restart.
- A completed older layout is preserved; no automatic migration overwrites existing map data.
- A world without a valid completed layout marker is preserved and game entry remains disabled.
- Back up the entire world and plugin data folder before manual migration. Do not delete the marker to force regeneration.

## World export

A real PocketMine-MP 5.44.3 LevelDB world was generated and validated for the 0.6.1 source in [run #43976065617](https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43976065617). Download artifact [#14280173997](https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43976065617/artifacts/14280173997). It contains `VivaArcade/level.dat` and a non-empty `VivaArcade/db/` LevelDB directory. SHA-256: `9386528855c907aa84c74d8e40f9a3fedfa3a28adc144ffeed0335cd4accacf1`.

The archive is a PocketMine LevelDB ZIP, not a Bedrock `.mcworld`. Its archive integrity and required LevelDB files were verified after PocketMine stopped cleanly.

For your own server, stop PocketMine cleanly and run:

~~~bash
VIVAARCADE_SERVER_STOPPED=1 ./scripts/export-world.sh
~~~

## Current feature boundary

The current revision includes one live solo Parkour Rush mode, daily rewards, profile/economy foundations and dependency-light matchmaking/party primitives. It does not yet provide 24 playable games, live multiplayer matchmaking, integrated party UI/chat, a shop, challenge catalogue, leaderboards, achievements, scoreboard, admin panel or resource pack. Do not deploy it as a finished arcade network.
