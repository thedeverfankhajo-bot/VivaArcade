# VivaArcade implementation status

Updated: 2026-11-16

## Delivered in source

- PMMP 5 plugin entry point, registered player commands and native Bedrock menu forms.
- Dedicated world creation/loading through PocketMine WorldManager.
- First-run world layout builder with spawn plaza, pylons, connector and Solo Parkour course.
- Solo Parkour Rush with server-owned timer, checkpoint, fall recovery, finish and unique reward transaction IDs.
- UUID-keyed JSON player profile storage with file locking and atomic replacement.
- Lobby block break/place protection and disconnect cleanup for active Parkour sessions.
- Dedicated `LobbyManager` owns lobby teleport routing; `/spawn` exits Parkour Rush before returning a player to the saved world spawn.
- Dependency-light QueueManager, ArenaPool, MatchSession and PartyManager.
- Versioned match snapshots with validation of identities, states, timestamps, participants, score indexes, player limits and reward claims.
- Export script that requires an actual stopped-server LevelDB world before packaging.

## Partial / not production-ready

- The PMMP runtime foundation and Parkour implementation are source-committed but have not been tested inside a running PocketMine-MP 5 server with a Bedrock client.
- Existing matchmaking/party code is not wired to PMMP commands, forms, scheduler, live player state or match adapters.
- JSON storage currently covers basic player profiles and Parkour rewards only. It is not the complete wallet/ledger system.
- The generated world currently contains only the initial plaza and Parkour course, not the full multi-district network.
- Implemented player-facing messages and menus use Finglish; the larger requested menu set is not yet present.

## Not implemented

24+ playable modes, arena instance cloning/reset, live matchmaking, party invitation UI/chat/queueing, spectator mode, private/team matches, SQLite, full atomic economy/shops/cosmetics, daily/weekly/seasonal challenges, tournaments/events, persistent leaderboard displays, admin panel, complete crash recovery/inventory restoration, and full performance/load testing. The `/spawn` command is a lobby-navigation utility and does not imply those unimplemented systems are available.

## Build and release

`build.sh` packages from repository source and checks PHP syntax, core tests, storage tests, snapshot tests and PHAR manifest entries. It writes a checksum for the generated PHAR.

The historical `dist/source.zip` remains damaged but is no longer used by the build. No `dist/VivaArcade-World.zip` is committed because no real PMMP runtime export has been generated. A PHAR is not claimed as verified until the build workflow succeeds.
