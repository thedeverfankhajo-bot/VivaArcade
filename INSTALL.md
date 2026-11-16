# VivaArcade installation

## Requirements

- PocketMine-MP 5.x with a PHP 8.1+ runtime supported by that PMMP release.
- PHP CLI with the Phar, json, hash and zlib extensions for building.
- A clean, backed-up test server for first installation.

## Build the plugin

From the repository root:

~~~bash
chmod +x build.sh
./build.sh
~~~

The build script runs PHP syntax checks, core regressions, storage checks, wallet/economy tests, daily reward tests, match snapshot validation, the 19-district directory test and PHAR manifest validation. It writes:

- `dist/VivaArcade.phar`
- `dist/SHA256SUMS.txt`

A verified CI build is available at [run #43903997476](https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43903997476).

## Install

1. Copy `dist/VivaArcade.phar` into the server's `plugins/` folder.
2. Start PocketMine-MP 5 and watch the console for the VivaArcade world-build messages.
3. On a new world, PocketMine creates `worlds/VivaArcade/` and the plugin incrementally constructs its initial plaza, district paths and 19 themed district pads.
4. Join using a Bedrock client and run `/arcade`.
5. Run `/daily` to open the once-per-UTC-day Dollar and XP reward form.

The initial build is scheduled in bounded main-thread batches (one chunk and 180 block writes per tick by default). The lobby is marked playable only after the task finishes successfully.

## Existing VivaArcade worlds

- A layout marker with state `building` is replayed safely on restart.
- A completed version-1 map is preserved without automatic changes. It will not gain the expanded districts until a reviewed migration is performed.
- A world with no valid completed layout marker is preserved and game entry remains disabled.
- Back up the entire world and plugin data folder before any manual migration. Do not delete the layout marker as a regeneration shortcut.

## World export

A real PocketMine-MP 5.44.3 LevelDB world was generated and validated in [Actions run #43903997245](https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43903997245). Download the `vivaarcade-world-leveldb` artifact from that run. It contains `VivaArcade/level.dat` and a non-empty `VivaArcade/db/` LevelDB directory.

For a later export from your own server, stop PocketMine cleanly and run:

~~~bash
VIVAARCADE_SERVER_STOPPED=1 ./scripts/export-world.sh
~~~

The script requires a real `worlds/VivaArcade/level.dat` and a non-empty `worlds/VivaArcade/db/` directory. The archive is a PocketMine LevelDB ZIP, not a Bedrock `.mcworld` package.

## Current feature boundary

The current revision includes one live solo Parkour Rush mode, the daily reward flow, and foundation services. It does not yet provide 24 playable games, live multiplayer matchmaking, integrated party UI/chat, a shop, challenge catalogue, leaderboards, achievements, scoreboard, admin panel or resource pack. Do not deploy it as a finished arcade network.
