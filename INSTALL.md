# VivaArcade installation

## Requirements

- PocketMine-MP 5.x with a PHP 8.1+ runtime supported by that PMMP release.
- PHP CLI with the Phar, json, hash and zlib extensions for building.
- A clean, backed-up test server for the first launch.

## Build the plugin

From the repository root:

~~~bash
chmod +x build.sh
./build.sh
~~~

The build script runs PHP syntax checks, core regressions, storage checks, match snapshot validation, the 19-district directory test and PHAR manifest validation. It writes:

- dist/VivaArcade.phar
- dist/SHA256SUMS.txt

A successful CI build is not a substitute for testing the plugin in a real PocketMine server and Bedrock client.

## Install

1. Copy dist/VivaArcade.phar into the server's plugins/ folder.
2. Start PocketMine-MP 5 and watch the console for the VivaArcade world-build messages.
3. On a new server/world, PocketMine creates the VivaArcade world and the plugin incrementally constructs its initial plaza, district paths and 19 themed district pads.
4. Join using a Bedrock client and run /arcade.

The initial build is scheduled in bounded main-thread batches (one chunk and 180 block writes per tick by default). The lobby is marked playable only after the task finishes successfully.

## Existing VivaArcade worlds

- A layout marker with state building is replayed safely on restart.
- A completed version-1 map is preserved without automatic changes. It will not gain the expanded districts until a reviewed migration is performed.
- A world with no valid completed layout marker is preserved and game entry remains disabled.
- Back up the entire world and plugin data folder before performing any manual migration. Do not delete the layout marker as a regeneration shortcut.

## World export

To export a real LevelDB world, stop PocketMine cleanly and run:

~~~bash
VIVAARCADE_SERVER_STOPPED=1 ./scripts/export-world.sh
~~~

The script requires a real worlds/VivaArcade/level.dat and a non-empty worlds/VivaArcade/db/ directory. This source checkout does not contain a runtime-generated world archive.

## Current feature boundary

The current revision includes a live solo Parkour Rush implementation and foundation services. It does not yet provide the requested 24 playable games, live multiplayer matchmaking, integrated party UI/chat, complete Dollar ledger, shop, challenges, leaderboard, achievements, admin panel or resource pack. Do not deploy it as a finished arcade network.
