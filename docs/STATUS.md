# VivaArcade implementation status

Updated: 2026-11-16

## Delivered in source

- PMMP 5 plugin entry point, registered player commands and native Bedrock menu forms.
- Dedicated world creation/loading through PocketMine WorldManager.
- New-world layout v2: 19 named themed districts plus the Main Spawn Plaza, connected with illuminated paths, colored concrete, blackstone floors, neon pylons and gateways.
- A deterministic lazy world-layout operation stream processed by a PocketMine scheduler task. Its default budget is one chunk and 180 block writes per tick; no giant synchronous block loop runs during plugin enable.
- Versioned layout marker. New worlds are built incrementally; a build interrupted while marked building is safely replayed. Completed version-1 worlds are preserved without automatic edits.
- Existing Parkour Rush start/checkpoint/finish coordinates are retained and the new public road network is routed around the course.
- Solo Parkour Rush with server-owned timer, checkpoint, fall recovery, finish and unique reward transaction IDs.
- UUID-keyed JSON player profile storage with per-player file locking and atomic replacement.
- Server-side Dollar WalletService with source allow-lists, unique transaction IDs, idempotent credits/debits, insufficient-funds rejection and balance/earned/spent totals.
- A bounded recent Dollar ledger that records signed amounts and post-transaction balances; Parkour completion retains its all-in-one reward/profile commit.
- Existing profiles migrate missing lifetime totals from retained transactions without discarding existing player data.
- Lobby block break/place protection and disconnect cleanup for active Parkour sessions.
- Dedicated LobbyManager owns lobby teleport routing; /spawn exits Parkour Rush before returning a player to the saved world spawn.
- Dependency-light QueueManager, ArenaPool, MatchSession and PartyManager.
- Versioned match snapshots with validation of identities, states, timestamps, participants, score indexes, player limits and reward claims.
- World export script that requires an actual stopped-server LevelDB world before packaging.

## Partial / not production-ready

- The PMMP runtime foundation, incremental world builder and Parkour implementation are source-committed but have not been tested inside a running PocketMine-MP 5 server with a Bedrock client.
- The 19-district layout is scheduled only for brand-new worlds or a layout marker left in the building state. A completed older map is preserved; it does not automatically gain the new districts.
- District names/coordinates and Finglish labels have a standalone regression test; the 3D world construction still needs runtime visual verification.
- Existing matchmaking/party code is not wired to PMMP commands, forms, scheduler, live player state or match adapters.
- WalletService is implemented and /balance reads through it, but no shop purchase, daily payout, challenge payout or achievement payout invokes credit/debit yet.
- Implemented player-facing messages and menus use Finglish; the larger requested menu set is not yet present.

## Not implemented

24+ playable modes, arena instance cloning/reset, live matchmaking, party invitation UI/chat/queueing, spectator mode, private/team matches, SQLite, shop and cosmetics integration, daily/weekly/seasonal challenges, tournaments/events, persistent leaderboard displays, admin panel, complete crash recovery/inventory restoration, and full performance/load testing remain unimplemented.

## Build and release

The build script now includes the Dollar wallet regression suite in addition to core, storage, snapshot and world-layout tests. The previous successful Actions artifact documented below predates this wallet change and is not evidence that the current source revision has built.

The earlier Actions run [#43887719089](https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43887719089) completed successfully for source commit 9214d6436f2d2915a3c5157e906ed5fc47749a2b; it built and validated dist/VivaArcade.phar and uploaded the PHAR plus checksum as artifact vivaarcade-build. Its SHA-256 was 67986d54e522531e5134f9ba52c1a3a1f5f96e97663962720b5d4e744ab1d076.

That earlier artifact is not the current build. The historical dist/source.zip remains damaged and is no longer used by build.sh. No dist/VivaArcade-World.zip is committed because no real PMMP runtime export has been generated.
