# Implementation Status

This document distinguishes shipped source, tested core logic and unverified runtime integration.

## Phase 1 source bundle (`dist/source.zip`)

| Area | Status | Notes |
|---|---|---|
| Plugin | Source included | PMMP API 5 manifest and PHP source bundle. |
| Dedicated world | Runtime-generated | PMMP generates/saves the world; no prebuilt world archive is claimed. |
| Lobby/arenas | First playable pass | Lobby, Color Rush pads, parkour and game-specific courses. |
| Mini-games | 15 solo modes | Each has its own start, input/movement logic, timer and score in the original bundle. |
| Profiles | Baseline | UUID-based JSON profile, XP/level, wins/games/best score, daily streak. |
| Economy | Baseline | Locked atomic JSON writes, wallet validation, transaction ledger and cosmetic ownership. |
| Challenges/rewards | Baseline | Daily reward plus two progress-based daily challenges. |
| Shop | Minimal | One cosmetic: `neon-tag`. |
| Leaderboard | Basic | Stored profile ranking through `/top`. |
| Menus | Basic | Native Bedrock SimpleForm menu/game browser. |
| Admin | Minimal | `/arcadeadmin give` and `reload`. |
| Protection | Basic | Block/item/damage protection in arcade world and configurable boundary. |
| Score display | Basic | Score tag and action bar; not a custom sidebar. |

## Added Phase 2 core primitives

| Area | Status | Notes |
|---|---|---|
| Match lifecycle | Core tested | State machine, participants, score validation, disconnect/reconnect, single reward claims and validated JSON snapshot codec. |
| Queue coordination | Core tested | FIFO queue, UUID uniqueness, minimum/maximum player limits and isolated session creation. |
| Arena ownership | Core tested | Exclusive reservation and owner-scoped release. |
| Party membership | Core tested | UUID membership, leader, invites (exact-deadline expiry), kick/leave and leadership transfer. |

**Important:** These classes are not yet wired into the PMMP plugin lifecycle, `/queue` command, Bedrock UI, scheduler, mini-game gameplay adapters or persistent crash-recovery flow. They are not represented as a playable multiplayer system yet.

## Not implemented / not verified

- 24 fully playable mini-games (only the existing 15 solo modes are included in Phase 1).
- PMMP-integrated multiplayer queues, countdown scheduling, team queues, private matches and rematches.
- Party UI, chat, party queue and online invite delivery.
- Tournament brackets, scheduled events and persistent event history.
- Full achievements and daily/weekly/monthly/seasonal challenge catalogue.
- Expanded cosmetic stores, preview/equip UI and transaction-history UI.
- SQL adapter, crash-safe match recovery and production-scale database concurrency tests.
- NPCs, portal triggers, hologram service and packet-backed sidebar.
- Full admin forms, automatic backup/restore UI and per-game arena controls.
- Bedrock client QA, production load tests and a pre-exported LevelDB world ZIP.

## Production gate

Run the built PHAR on the exact target PocketMine-MP 5 release, verify world creation/loading, test every game with Bedrock clients, integrate and test the Phase 2 adapters, inspect logs, test restarts/disconnects and benchmark storage at expected concurrency before public deployment.
