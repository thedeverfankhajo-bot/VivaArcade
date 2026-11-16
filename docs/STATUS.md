# VivaArcade implementation status

Updated: 2026-11-17

## Delivered in source
- PocketMine-MP API 5 plugin entry point, player commands and Native Bedrock forms.
- Dedicated VivaArcade world creation/loading through PocketMine WorldManager.
- New-world layout v3 with 19 named districts plus Main Spawn (20 mapped areas), lit avenues, road-facing gateways and a raised neon Spawn canopy.
- Deterministic incremental builder (one chunk and 180 block writes per tick by default), resumable version-3 marker and preservation of completed older worlds; completed pre-v3 layouts are not auto-migrated.
- Solo Parkour Rush with server-side timer, checkpoint, fall recovery, finish and unique reward transaction ID.
- UUID JSON profiles with per-player locks and atomic replacement.
- Dollar WalletService, bounded transaction ledger, duplicate protection and no-negative-balance debit checks.
- Once-per-UTC-day Dollar/XP reward with atomic claim state and Native Bedrock form.
- Dependency-light ArenaPool, QueueManager and MatchSession primitives.
- Live Party menu and `/party` command with UUID membership, expiring online invites, accept/decline, leave/disconnect cleanup, leader-only kick/transfer and Finglish group chat.
- Native Game Browser with truthful All, Featured, Solo and Skill filters; only the implemented Parkour Rush mode appears, with live online-player/active-run counts, configured reward details and an explicit shared-course disclosure.
- Parkour progression overflow checks and no-partial-write regression coverage.

## Latest verification
- Current feature commit on main: 7b3e366596980ac07e13ba5c44105b609e4f8a4e (district gateway alignment and Spawn canopy).
- PHP CI #43981599070 passed on this commit; PHP 8.1.34 and shell/PHP syntax checks passed.
- Six regression suites: 374 assertions passed (core 86, storage 20, economy 19, daily 18, snapshot 13, world layout 218).
- PHAR manifest validation, packaging and artifact upload passed. SHA-256: 8428f40ee07f898a7e795f5cc6e947f5a8423dc0956fc4f7ae9ff76c371d303c.
- PHAR artifact: https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43981599070/artifacts/14283173555
- PocketMine-MP 5.44.3 world workflow #43981599175 passed on the same commit; plugin booted, layout completed, server stopped cleanly, LevelDB files were verified and unzip -t passed.
- World ZIP SHA-256: f6de82a73a4217378409a8b11b8e0dbe016a4aa9220d0223063d4dd02ec76caa.
- World artifact: https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43981599175/artifacts/14281779262
- Non-fatal warning remains in tests/economy.php: importing global InvalidArgumentException with a use statement has no effect; tests pass.

## Partial / not production-ready
- No Bedrock client was connected; Native Bedrock form rendering, player interaction, movement timing and visual inspection remain unverified.
- Matchmaking queue and arena primitives are not connected to live PMMP queues, countdown tasks, match instances or arena cloning/reset.
- Party state is in-memory and is not restored after restart; party queue/private match integration is not implemented.
- Wallet is connected to `/balance` and daily rewards only; shop, challenge and achievement payouts are not wired.
- Main menu currently exposes implemented profile, Party, stats, Parkour Rush, daily reward and help paths only.

## Not implemented
24+ playable modes, isolated arena cloning/reset, live matchmaking, party queueing, team/private matches, shop and cosmetics, challenge catalogue, tournaments, persistent leaderboards, scoreboard, achievements, admin panel, SQLite, full inventory/state restoration, full crash recovery, load testing and Resource Pack remain unimplemented.

No Resource Pack or Bedrock `.mcworld` file is shipped. The current generated world artifact is a PocketMine LevelDB ZIP, not a Bedrock-import-tested package.
