# Implementation Status

This document distinguishes shipped source, tested core logic and unverified runtime integration.

## Phase 1 source bundle (`dist/source.zip`) — integrity failure

The archive is **not extractable** and must not be used for deployment. It is 7,501 bytes, has no ZIP end-of-central-directory record, and ends inside the compressed payload for `src/VivaArcade/Lobby/LobbyManager.php`. Only earlier ZIP entries can be read. The earlier project notes describe the intended contents, but the committed archive does not provide the full code needed to verify or package them.

| Area | Status | Notes |
|---|---|---|
| Plugin | Blocked | `plugin.yml` can be inspected, but the complete main class and plugin source cannot be extracted from the damaged ZIP. |
| Dedicated world | Not generated here | Requires running the plugin on PocketMine-MP; no saved LevelDB world archive is available. |
| Lobby/arenas | Not verifiable | The source archive truncates in the lobby manager implementation. |
| Mini-games | Not verifiable | Older notes describe 15 solo modes, but the complete game files are not recoverable from the archive. |
| Profiles/economy/challenges/shop/leaderboards | Not verifiable | Earlier docs describe baseline implementations; the complete source is unavailable for review or packaging. |
| Menus/admin/protection | Not verifiable | No runtime or Bedrock client tests are possible from the available artifact. |

## Added Phase 2 core primitives

| Area | Status | Notes |
|---|---|---|
| Match lifecycle | Core tested | State machine, connected-player minimum checks before countdown/start, participant score validation, disconnect/reconnect and single reward claims. |
| Queue coordination | Core tested | FIFO queue, UUID uniqueness, minimum/maximum player limits, immediate removal of disconnected queued players, active-match disconnect/reconnect routing, concurrent isolated sessions and bounded in-memory terminal history. |
| Arena ownership | Core tested | Exclusive reservation and owner-scoped release. |
| Party membership | Core tested | UUID membership, leader, invite/decline/cancel, strict expiry, kick/leave and leadership transfer. |

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

## Build and runtime artifacts

- `dist/VivaArcade.phar`: **not produced**. CI refuses to build from the malformed source archive.
- `dist/VivaArcade-World.zip`: **not produced**. A real LevelDB world requires a running PocketMine-MP server and clean world shutdown.
- `dist/VivaArcade-Full-Package.zip`: **not produced** because the required PHAR and world archive do not exist.

See [SOURCE-RECOVERY.md](SOURCE-RECOVERY.md) for the ZIP integrity details and recovery steps.

## Production gate

Run the built PHAR on the exact target PocketMine-MP 5 release, verify world creation/loading, test every game with Bedrock clients, integrate and test the Phase 2 adapters, inspect logs, test restarts/disconnects and benchmark storage at expected concurrency before public deployment.
