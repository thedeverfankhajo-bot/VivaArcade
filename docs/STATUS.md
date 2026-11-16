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
- Dependency-light ArenaPool, QueueManager, MatchSession and PartyManager primitives.
- Parkour progression overflow checks and no-partial-write regression coverage.

## Latest verification
- Latest code commit on `main`: `dd4f558d2a741ad0e28d2ada46d863d26557b1aa`.
- CI run [#43976065690](https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43976065690): success.
- PHP 8.1.34 syntax and shell syntax checks: passed.
- Six regression suites: 286 assertions passed (core 79, storage 20, economy 19, daily 18, snapshot 13, world layout 137).
- PHAR manifest validation, packaging and artifact upload: passed.
- PHAR SHA-256: `621b521d59c8334aa9538ba9681615a69e7950a478f944defc22e6c1a4c9d3d7`.
- CI artifact: [vivaarcade-build #14280029041](https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43976065690/artifacts/14280029041).
- PocketMine-MP 5.44.3 world workflow [#43976065617](https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43976065617) passed on the same source commit. The plugin built with the official PMMP PHP runtime, the world layout completed, the server stopped cleanly, and LevelDB files plus ZIP integrity were verified.
- World ZIP SHA-256: `9386528855c907aa84c74d8e40f9a3fedfa3a28adc144ffeed0335cd4accacf1`.
- World artifact: [vivaarcade-world-leveldb #14280173997](https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43976065617/artifacts/14280173997).

## Partial / not production-ready
- No Bedrock client was connected; UI rendering, player interaction, movement timing and visual inspection remain unverified.
- Existing queue and party code is not connected to live PMMP events, commands, forms, queues or match instances.
- Wallet is connected to `/balance` and daily rewards only; shop, challenge and achievement payouts are not wired.
- Main menu currently exposes implemented profile, stats, Parkour Rush, daily reward and help paths only.

## Not implemented
24+ playable modes, isolated arena cloning/reset, live matchmaking, party invitation UI/chat/queueing, team/private matches, shop and cosmetics, challenge catalogue, tournaments, persistent leaderboards, scoreboard, achievements, admin panel, SQLite, full inventory/state restoration, full crash recovery, load testing and Resource Pack remain unimplemented.

No Resource Pack or Bedrock `.mcworld` file is shipped. The existing generated world artifact is a PocketMine LevelDB ZIP.
