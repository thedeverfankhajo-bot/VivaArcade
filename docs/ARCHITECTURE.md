# Architecture

## Current runtime composition

- VivaArcade\\Main: PocketMine plugin entry point, event adapter, command routing and service bootstrap.
- World\\ArcadeWorldManager: creates or loads the dedicated VivaArcade world, reads a versioned layout marker and schedules the initial build.
- World\\WorldBuildTask: synchronous PocketMine scheduler task with a bounded budget of 1 chunk and 180 block writes per tick by default. It reports completion/failure and is safe to retry after an interrupted build.
- World\\WorldBuilder: lazy operation stream for the central plaza, lit district roads, 19 themed district pads/gateways and the existing Parkour Rush route.
- World\\DistrictDirectory: stable IDs, Finglish names, coordinates and color keys for each district; also resolves player coordinates to district bounds.
- Lobby\\LobbyManager: teleports players to the dedicated world's saved spawn location.
- Forms\\ArcadeForm: native Bedrock JSON form implementation without FormAPI dependencies.
- Games\\ParkourRush: live solo mode adapter, checkpoint/fall handling and server-side completion.
- Storage\\JsonPlayerStorage: UUID-keyed profiles, per-player locks, atomic file replacement, migration defaults and idempotent reward storage.
- Economy\\WalletService: server-side Dollar facade with credit/debit source allow-lists, unique transaction IDs, non-negative balance enforcement, atomic daily reward claims and recent-ledger reads.
- Challenges\\DailyRewardService: UTC day policy and reward status; it delegates every claim to WalletService and the locked JSON profile transaction.
- Core\\ArenaPool, Core\\QueueManager, Core\\MatchSession: dependency-light arena ownership, queue and match state primitives with snapshot validation.
- Party\\Party, Party\\PartyManager: UUID party and expiring invitation primitives.

## Storage and economy boundary

WalletService delegates all writes to JsonPlayerStorage. A signed Dollar delta, idempotency marker, lifetime total and ledger entry are written while holding the same per-player lock and are committed through the existing atomic file-replacement path. A rejected duplicate or insufficient-funds debit does not change the wallet.

Parkour completion remains a specialized storage operation because it must atomically update the Dollar balance, XP, level, game statistics, best time and reward idempotency marker together. It also updates lifetime earnings and writes a post-reward balance to the ledger. Daily rewards use a separate once-per-UTC-date transaction that atomically writes the day marker, Dollar/XP changes, level, idempotency key and ledger entry.

Existing profiles receive missing total-earned/total-spent fields from their retained transaction entries. Storage remains a local JSON adapter; it is not a multi-server database or a distributed ledger.

## World build and migration boundary

A brand-new world is built incrementally on the PocketMine main thread. The builder loads its required chunks first, then applies a deterministic stream of fixed-coordinate block operations in small batches. It never clears chunks, and only touches the VivaArcade coordinates declared by the builder.

The layout marker records version 2. A marker left in the building state is replayed on restart. A completed version-1 map is deliberately preserved as-is: no automatic migration overwrites an existing lobby or custom player edits. It remains playable with its prior layout until an administrator backs it up and deliberately migrates it. A world with no valid completed marker is preserved and game entry stays disabled.

## Runtime boundary

Only Parkour Rush is currently connected to live PocketMine events. WalletService powers /balance and the daily reward claim; shop and challenge transactions are not connected. Queue, arena and party classes are not yet connected to live arena worlds, player teleportation, scheduled countdowns, forms, persistence or commands. They must not be treated as a production matchmaking or party network.

## Extension direction

New game modes should implement a common server-side game contract before they are registered with matchmaking. An adapter should own lifecycle hooks (prepare, start, tick/event handling, finish and cleanup), while the shared match service owns participants, arena reservations and state transitions. Economy transactions and persistent profiles should be injected as services rather than called through static state.

Main remains the composition root; world, lobby, game, economy, storage and core responsibilities should stay in their separate namespaces.
