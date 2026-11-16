# VivaArcade

**Native PocketMine-MP API 5 arcade plugin for Minecraft Bedrock**  
PHP 8.1+ · independent `VivaArcade` world · Finglish player-facing messages

> **Delivery status: playable Phase 1, not the complete network specification.** This source bundle includes 15 solo mini-game modes and a first-run world builder. Multiplayer matchmaking, party/friends, tournaments, scheduled events, achievements, advanced cosmetic families and a packet-backed sidebar remain future work.

## Included

- API 5 plugin manifest and namespaced PHP source.
- Dedicated `VivaArcade` world generated and saved through PMMP `WorldManager`.
- Fifteen solo mini-games with individual rules and scoring.
- Native Bedrock SimpleForm menus, titles, action bars and player score tags.
- Player profiles, level/XP, game stats, daily rewards and daily challenges.
- Virtual Dollar wallet with atomic locked JSON transactions and cosmetic ownership.
- One cosmetic item (`neon-tag`), basic leaderboard and admin economy commands.
- Lobby protection, command permissions and Finglish text resources.

## Build and install

Run `./build.sh` with PHP CLI and Phar enabled (`phar.readonly=0`). Copy `dist/VivaArcade.phar` to the server's `plugins/` directory, restart PocketMine-MP 5, then use `/arcade` or `/games` in a Bedrock client.

## Honest limitations

The build environment does not contain a PocketMine-MP server runtime. PHP syntax, storage/economy tests and local PHAR packaging passed, but live plugin startup, Bedrock GUI/gameplay and LevelDB world loading have **not** been verified. No fake LevelDB world ZIP is supplied: let PMMP generate/save the world on first run, stop the server cleanly, then export `worlds/VivaArcade/`.

All 15 modes are single-player sessions in this phase. The JSON store is a small deployment adapter, not a high-scale network database. Read `docs/STATUS.md` and `docs/TEST-RESULTS.md` for the feature matrix and test scope.
