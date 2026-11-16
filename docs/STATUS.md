# VivaArcade implementation status

Updated: 2026-11-17

## Delivered in source
- PocketMine-MP API 5 plugin entry point, player commands and Native Bedrock forms.
- Dedicated VivaArcade world creation/loading through PocketMine WorldManager.
- New-world layout v2 with 19 themed districts, a Main Spawn Plaza, lit avenues, color pylons and gateways.
- Deterministic incremental builder (one chunk and 180 block writes per tick by default), resumable build marker and preservation of completed older worlds.
- Solo Parkour Rush with server-side timer, checkpoint, fall recovery, finish and unique reward transaction ID.
- UUID JSON profiles with per-player locks and atomic replacement.
- Dollar WalletService, bounded transaction ledger, duplicate protection and no-negative-balance debit checks.
- Once-per-UTC-day Dollar/XP reward with atomic claim state and Native Bedrock form.
- Dependency-light ArenaPool, QueueManager and MatchSession primitives.
- Live Party menu and `/party` command with UUID membership, expiring online invites, accept/decline, leave/disconnect cleanup, leader-only kick/transfer and Finglish group chat.
- Parkour progression overflow checks and no-partial-write regression coverage.

## Latest verification
- Latest feature commit on `main`: `e334d092dd71599fd414c9e4d237afef43af16d6` (Party adapter + Bedrock menu).
- CI run [#43977378958](https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43977378958): success for commit `e334d092dd71599fd414c9e4d237afef43af16d6`.
- PHP 8.1.34 and shell syntax checks: passed. Six regression suites: 293 assertions passed (core 86, storage 20, economy 19, daily 18, snapshot 13, world layout 137).
- PHAR manifest validation, packaging and artifact upload: passed. PHAR SHA-256: `84ed0f35a9be4c919734699d62c50cc1c9540a1a0931ad03f524dbd824dee16e`.
- PHAR artifact: [vivaarcade-build #14280324849](https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43977378958/artifacts/14280324849).
- PocketMine-MP 5.44.3 world workflow [#43977378888](https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43977378888): success on the same source commit; the plugin booted, the 19-district world layout completed, the server stopped cleanly, `level.dat` and non-empty LevelDB data were checked, and `unzip -t` passed.
- World ZIP SHA-256: `f2cbd7bf1edc8ddf6748c90e000657d11cdcdd669d87bcad8acccb3cb4ef2d91`.
- World artifact: [vivaarcade-world-leveldb #14280998558](https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43977378888/artifacts/14280998558).

## Partial / not production-ready
- No Bedrock client was connected; Native Bedrock form rendering, player interaction, movement timing and visual inspection remain unverified.
- Matchmaking queue and arena primitives are not connected to live PMMP queues, countdown tasks, match instances or arena cloning/reset.
- Party state is in-memory and is not restored after restart; party queue/private match integration is not implemented.
- Wallet is connected to `/balance` and daily rewards only; shop, challenge and achievement payouts are not wired.
- Main menu currently exposes implemented profile, Party, stats, Parkour Rush, daily reward and help paths only.

## Not implemented
24+ playable modes, isolated arena cloning/reset, live matchmaking, party queueing, team/private matches, shop and cosmetics, challenge catalogue, tournaments, persistent leaderboards, scoreboard, achievements, admin panel, SQLite, full inventory/state restoration, full crash recovery, load testing and Resource Pack remain unimplemented.

No Resource Pack or Bedrock `.mcworld` file is shipped. The existing generated world artifact is a PocketMine LevelDB ZIP.
