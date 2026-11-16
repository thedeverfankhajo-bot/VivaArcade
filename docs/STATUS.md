# VivaArcade implementation status

Updated: 2026-11-16

## Delivered in source

- PMMP 5 plugin entry point, registered player commands and native Bedrock menu forms.
- Dedicated world creation/loading through PocketMine WorldManager.
- New-world layout v2: 19 named themed districts plus the Main Spawn Plaza, connected with illuminated paths, colored concrete, blackstone floors, neon pylons and gateways.
- Deterministic, incremental world construction through PocketMine scheduled task batches (one chunk and 180 block writes per tick by default).
- Versioned layout marker; interrupted builds can resume, while completed older worlds are preserved without automatic edits.
- Solo Parkour Rush with server-owned timer, checkpoint, fall recovery, finish and unique reward transaction IDs.
- UUID-keyed JSON player profiles, per-player file locks and atomic profile replacement.
- Dollar WalletService with source allow-lists, unique transaction IDs, duplicate protection, insufficient-funds rejection, earned/spent totals and a bounded ledger.
- DailyRewardService and `/daily`: once-per-UTC-day Dollar and XP rewards, server-derived claim date, atomic profile update and native Bedrock claim form.
- Dependency-light QueueManager, ArenaPool, MatchSession and PartyManager primitives.
- Versioned match snapshots and standalone regression coverage.
- World export tooling which requires a real stopped-server LevelDB world.

## Runtime verification completed

- GitHub Actions CI run [#43903997476](https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43903997476) succeeded for source commit `d6a8f093f082426d97cb8f16c28d6c3388d0d8be`.
- All PHP syntax checks passed; test suites reported 278 assertions passed; PHAR manifest validation, packaging and artifact upload passed.
- PocketMine-MP 5.44.3 ran the plugin in world workflow [#43903997245](https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43903997245). The VivaArcade layout completed, PocketMine stopped cleanly, and the resulting LevelDB ZIP passed file and archive checks.
- The generated PHAR SHA-256 is `cdbdbbf9165aea05b5753a2dc18dd2768133821679eb66abaa9c31ce3274a122`.
- The generated world ZIP SHA-256 is `c585b5658844cb79538e896e28fd95913d98e09ad1550f6bf52d1fa158851218`.

## Partial / not production-ready

- No Bedrock client was connected. Form rendering, player interaction, movement timing and visual presentation need client-side testing.
- The 19-district layout completed in PMMP, but its visual quality has not been inspected through a Bedrock client.
- Existing matchmaking/party code is not wired to live PMMP player events, forms, queues or match instances.
- WalletService powers `/balance` and the daily reward. Shop purchases, challenge rewards and achievement payouts are not connected.
- The current main menu exposes only the implemented profile, stats, game selector, daily reward and help paths.

## Not implemented

24+ playable modes, arena instance cloning/reset, live matchmaking, party invitation UI/chat/queueing, spectator mode, private/team matches, SQLite, shop and cosmetics integration, daily/weekly/seasonal challenge catalogue, tournaments/events, persistent leaderboards, scoreboard, achievements, admin panel, full inventory/state restoration, complete crash recovery and load testing remain unimplemented.

## Artifacts

The verified PHAR and world ZIP are downloadable from the two Actions runs above. The files are also provided in the generated delivery bundle. Binary artifacts are not committed to git. No Resource Pack is shipped.
