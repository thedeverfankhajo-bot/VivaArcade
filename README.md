# VivaArcade

**Native Minecraft Bedrock arcade foundation for PocketMine-MP API 5**

PHP 8.1+ · Dedicated VivaArcade world · Native Bedrock Forms · UUID player profiles

> **Release scope:** this integration contains the PocketMine plugin foundation, incremental dedicated-world builder with 19 themed districts, native Bedrock forms, Solo Parkour Rush, UUID-based reward persistence, a server-side Dollar wallet ledger, and dependency-light matchmaking/party primitives. It is not yet the requested 24+ mode production arcade network.

## Implemented

- PocketMine-MP API 5 plugin entry point and registered player commands.
- Dedicated VivaArcade world load/create through the official PocketMine WorldManager.
- New-world plaza with 19 named districts plus the Main Spawn Plaza (20 mapped areas), illuminated roads, road-facing gateways, neon pylons, a raised Spawn canopy and the Parkour Rush course.
- Incremental main-thread world construction, bounded by one chunk and 180 block writes per tick by default, with versioned completion/retry state.
- Existing world data is not cleared. Interrupted builds are replayed idempotently; completed older layouts are preserved and require a reviewed migration.
- Native Bedrock JSON forms for the main menu and a Game Browser with All, Featured, Solo and Skill categories; only live modes are listed.
- Parkour Rush details show real online-player and active-run counts, configured rewards, solo run limits, direct-start status and the shared-course boundary.
- Solo Parkour Rush with server-side timer, checkpoint, fall recovery, finish and server-side reward.
- UUID-keyed JSON player profiles, per-player file locks, atomic replacement and duplicate-transaction protection.
- Server-side Dollar WalletService with positive credits, negative debits, bounded transaction amounts, source validation, transaction IDs, no-negative-balance enforcement, earned/spent totals and a 100-entry recent ledger.
- Once-per-UTC-day Dollar and XP rewards, committed atomically with a persistent daily marker and idempotent transaction ID; available through `/daily` and the native Bedrock form.
- Parkour completion updates Dollar balance, lifetime earnings, XP, statistics and its reward ledger entry in one atomic profile transaction.
- Lobby block break/place protection and active Parkour disconnect cleanup.
- Dependency-light arena ownership, matchmaking queue and isolated match-session core services.
- Live Party menu and `/party` command: create, invite online players, accept/decline expiring invitations, list members, leave, kick, transfer leadership and private party chat.
- Versioned, validated match snapshot codec with deterministic snapshot regression checks.
- World export script that refuses to invent or export a missing LevelDB world.

## Not yet implemented / not connected to the live PMMP adapter

The following requested systems are **not complete**: 24+ playable minigames, live matchmaking and independent arena instances, party queue integration, teams/private matches, durable match recovery, shop purchase flows and cosmetic inventory, daily/weekly challenges, tournaments/events, live leaderboards, admin panel, SQLite adapter, spectator mode, full inventory/state restoration, the full decorative multi-district world finish and a reviewed migration path for completed older maps.

The matchmaking queue and arena classes remain dependency-light primitives; they are not wired to live queues, countdown tasks, match instances or PocketMine player events. Party management is connected to the Native Bedrock menu and `/party` command, including disconnect cleanup. Party state is in-memory and is cleared on disconnect or server restart. The daily reward is connected to the wallet. Shop purchases and challenge/achievement reward flows are not yet connected. Do not interpret a registered core game ID as a live playable minigame.

## Requirements

- PocketMine-MP 5 on a supported PHP 8.1+ runtime.
- PHP CLI with Phar, json, hash and zlib extensions for packaging.
- sha256sum for the final checksum.

## Build

Run from the repository root:

    chmod +x build.sh
    ./build.sh

The build packages the repository's real src/, resources/ and root plugin.yml; it does not attempt to extract the damaged historical dist/source.zip. It runs PHP syntax checks, core regressions, UUID storage tests, wallet ledger tests, daily reward tests, match-snapshot tests and a 20-area/Finglish directory and gateway-orientation regression, validates the PHAR manifest and writes dist/SHA256SUMS.txt.

## Install

1. Build the PHAR using the command above.
2. Copy dist/VivaArcade.phar into the PocketMine-MP plugins/ directory.
3. Start PocketMine-MP. On first launch, the plugin asks PocketMine to create worlds/VivaArcade/ and populates the initial plaza and Parkour Rush course.
4. Join with a Bedrock client and use /arcade.

The current main source (commit 7b3e366596980ac07e13ba5c44105b609e4f8a4e) passed PHP CI run #43981599070: PHP 8.1.34 syntax checks, six regression suites (374 assertions), PHAR manifest validation and artifact upload. World workflow #43981599175 booted PocketMine-MP 5.44.3, completed the layout, stopped the server cleanly and exported a ZIP whose LevelDB contents and ZIP integrity were checked. No Bedrock client was connected; forms, interaction, movement physics and visual appearance still need client testing. See docs/TEST-RESULTS.md for checksums and artifact links.

## Commands currently registered

| Command | Description |
|---|---|
| /arcade | Open the main menu |
| /games | Browse the implemented game mode |
| /profile | View your VivaArcade profile |
| /stats | View basic game statistics |
| /balance | View Dollar balance |
| /daily | View and claim the once-per-UTC-day reward |
| /party | Open Party menu; supports create, invite, accept, decline, leave, kick, leader and chat |
| /leave | Leave active Parkour Rush |
| /spawn | Return to the VivaArcade lobby |

Other commands from the long-term specification are not registered yet.

## World and arena management

The world name is VivaArcade and is managed through PocketMine's official WorldManager. The first-run builder records its layout version and state in the plugin data folder (world-layout-v1.json).

The builder populates a new world or resumes a build explicitly marked building. It processes chunk loading and block writes in bounded scheduler batches (one chunk and 180 block writes per tick by default). It does not clear an existing map. A completed older layout is preserved and remains playable without automatic district expansion. Back up an existing world before a reviewed migration; do not delete the state file to force regeneration.

## Player data and economy

Profiles are stored under the plugin data folder with filenames based on player UUID. JSON writes use a per-player lock and atomic rename. A malformed profile is not silently replaced with defaults.

WalletService exposes server-side credit/debit operations with an allow-list of transaction types. Every operation uses a server-created transaction ID, a per-player lock and atomic profile replacement. Duplicate IDs are rejected; a debit that exceeds the current balance leaves the profile unchanged. The profile stores total Dollar earned, total Dollar spent and the latest 100 ledger entries. The general wallet API is not yet wired to shops or challenge rewards.

Parkour completion keeps its existing single atomic storage transaction so Dollar, XP, game statistics and the idempotency marker are committed together. Its current reward is configured in resources/config.yml; it is not yet the complete challenge/shop/economy feature set requested.

## Dedicated world export

A LevelDB world archive can only be exported after a real PocketMine runtime has generated and saved the world. Stop PocketMine cleanly, then run:

    VIVAARCADE_SERVER_STOPPED=1 ./scripts/export-world.sh

The script requires worlds/VivaArcade/level.dat and a non-empty worlds/VivaArcade/db/ LevelDB directory. It refuses to fabricate a world ZIP. The source repository does not contain the generated ZIP. A real PocketMine LevelDB ZIP for commit `7b3e366596980ac07e13ba5c44105b609e4f8a4e` is available as the GitHub Actions artifact documented in `docs/TEST-RESULTS.md`.

## Tests and verification boundary

Run php tests/run.php, php tests/storage.php, php tests/economy.php, php tests/daily.php, php tests/snapshot.php, php tests/world-layout.php and php tests/game-catalog.php, or use ./build.sh.

These tests cover dependency-light match/queue/party services, UUID player data, reward persistence, Dollar ledger operations, district registration and road-facing gateway coordinates. The separate world workflow verifies PocketMine startup, layout completion and LevelDB ZIP export. Neither workflow connects a Bedrock client, so form rendering, movement physics and client rendering remain unverified. See docs/STATUS.md, docs/TEST-RESULTS.md and docs/TESTING.md.


## Daily reward

The `daily-reward` section configures the server-side reward. Claims are limited to once per UTC date and reset at 00:00 UTC. Dollar, XP, level, date marker and the wallet ledger entry are committed in the same locked atomic profile write. The client cannot choose a reward amount or claim date.
