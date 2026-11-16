# VivaArcade Implementation Status

This status is intentionally conservative. A file or menu is not counted as gameplay until its rule flow is implemented.

## Implemented in feat/runtime-foundation

| Area | Status | Notes |
|---|---|---|
| Plugin entry point | Source added | VivaArcade\Main, PMMP plugin lifecycle and commands |
| Dedicated world | Source added, runtime unverified | WorldManager load/generate; first-run layout state marker |
| World builder | Source added, runtime unverified | Main plaza, pylons, illuminated connector and fixed Parkour Rush platforms |
| Bedrock forms | Source added, runtime unverified | Native PMMP Form interface, main and game-selection forms |
| Parkour Rush | Gameplay source added, runtime unverified | Solo timer, checkpoint, fall respawn, finish condition |
| UUID player profiles | Source added, unit tested | JSON, exclusive flock, temporary file + atomic rename |
| Parkour rewards | Source added, unit tested | Server-created idempotency key, Dollar, XP, game statistics and best time |
| Lobby protection | Source added, runtime unverified | Cancels place/break in dedicated arcade world |
| Existing Phase 2 match/party primitives | Core tested | Remain dependency-light and not wired to live gameplay |
| Build script | Updated, not run in this container | Builds PHAR from repository source without damaged legacy archive |

## Not implemented / not verified

- The remaining 23 requested mini-games.
- Difficulty tiers, spectators, scoring framework and per-game arena reset adapters.
- PMMP-integrated multiplayer queues, countdown scheduling, team/private matches, rematches and results UI.
- Party forms, delivery, party queue and party chat.
- Tournament brackets, scheduled events and persistent event history.
- Daily/weekly/monthly/seasonal challenges and achievement catalogue.
- Full cosmetics inventory, purchase/equip/preview UI and general transaction history UI.
- General-purpose economy transaction API and SQLite storage adapter.
- Persistent match recovery and cross-restart match state.
- Floating text/holograms, leaderboard wall and real scoreboard UI.
- Admin panel, complete moderation/audit commands and full backup/restore flow.
- Complete world districts and dedicated lobbies for all game categories.
- Runtime startup, LevelDB validation, Bedrock client UI/physics QA and performance/load testing.
- dist/VivaArcade-World.zip and dist/VivaArcade-Full-Package.zip.

## Delivery policy

Do not describe this branch as the requested complete arcade network. Build and validate it on the exact target PocketMine-MP 5 release, and exercise it with Bedrock clients before production. Add game modes as independently testable adapters, then connect the existing queue and party primitives through main-thread PMMP services.
