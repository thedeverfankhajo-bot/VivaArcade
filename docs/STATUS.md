# Implementation Status

This document distinguishes runtime features from planned work.

## Implemented

| Area | Status | Notes |
|---|---|---|
| Plugin | Implemented | PMMP API 5 manifest, PHP source, PHAR build script. |
| Dedicated world | Runtime-generated | PMMP generates/saves the world; no prebuilt world archive is claimed. |
| Lobby/arenas | First playable pass | Lobby, Color Rush pads, parkour and game-specific courses. |
| Mini-games | 15 solo modes | Each has its own start, input/movement logic, timer and score. |
| Profiles | Baseline | UUID-based JSON profile, XP/level, wins/games/best score, daily streak. |
| Economy | Baseline | Locked atomic JSON writes, wallet validation, transaction ledger, cosmetic ownership. |
| Challenges/rewards | Baseline | Daily reward plus two progress-based daily challenges. |
| Shop | Minimal | One cosmetic: `neon-tag`. |
| Leaderboard | Basic | Stored profile ranking through `/top`. |
| Menus | Basic | Native Bedrock SimpleForm menu/game browser. |
| Admin | Minimal | `/arcadeadmin give` and `reload`. |
| Protection | Basic | Block/item/damage protection in arcade world and configurable boundary. |
| Score display | Basic | Score tag and action bar; not a custom sidebar. |

## Not implemented

- Multi-player matchmaking, team sessions, queueing and multiple concurrent arena instances.
- Party, friends, invites and party chat.
- Scheduled events, tournament brackets and season archives.
- Full achievements and offline public profiles.
- Weekly/monthly/community challenge schedules.
- Transaction-history UI, transfers and SQL/remote database adapter.
- Particle/trail/gadget/rank cosmetic catalogue.
- NPCs, true portal triggers, hologram service and packet-backed sidebar.
- Full admin forms, automatic backup/restore UI and per-game arena controls.
- Bedrock client QA, production load tests and a pre-exported LevelDB world ZIP.

## Production gate

Run the plugin on the exact target PMMP release, verify world creation/loading, test each mode with Bedrock clients, inspect logs, test restarts/disconnects and benchmark storage at expected concurrency before public deployment.
