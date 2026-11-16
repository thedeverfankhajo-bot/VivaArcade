# Architecture

## Current runtime composition

- `VivaArcade\Main`: PocketMine plugin entry point, event adapter, command routing and service bootstrap.
- `World\ArcadeWorldManager`: creates or loads the dedicated `VivaArcade` world, reads a versioned layout marker and schedules the initial build.
- `World\WorldBuildTask`: synchronous PocketMine scheduler task with a bounded budget of 1 chunk and 180 block writes per tick by default. It reports completion/failure and is safe to retry after an interrupted build.
- `World\WorldBuilder`: lazy operation stream for the central plaza, lit district roads, 19 themed district pads/gateways and the existing Parkour Rush route.
- `World\DistrictDirectory`: stable IDs, Finglish names, coordinates and color keys for each district; also resolves player coordinates to district bounds.
- `Lobby\LobbyManager`: teleports players to the dedicated world's saved spawn location.
- `Forms\ArcadeForm`: native Bedrock JSON form implementation without FormAPI dependencies.
- `Games\ParkourRush`: live solo mode adapter, checkpoint/fall handling and server-side completion.
- `Storage\JsonPlayerStorage`: UUID-keyed player profiles, locked atomic file replacement and idempotent Parkour reward credits.
- `Core\ArenaPool`, `Core\QueueManager`, `Core\MatchSession`: dependency-light arena ownership, queue and match state primitives with snapshot validation.
- `Party\Party`, `Party\PartyManager`: UUID party and expiring invitation primitives.

## World build and migration boundary

A brand-new world is built incrementally on the PocketMine main thread. The builder loads its required chunks first, then applies a deterministic stream of fixed-coordinate block operations in small batches. It never clears chunks, and only touches the VivaArcade coordinates declared by the builder.

The layout marker records version 2. A marker left in the `building` state is replayed on restart. A completed version-1 map is deliberately preserved as-is: no automatic migration overwrites an existing lobby or custom player edits. It remains playable with its prior layout until an administrator backs it up and deliberately migrates it. A world with no valid completed marker is preserved and game entry stays disabled.

## Runtime boundary

Only Parkour Rush is currently connected to live PocketMine events. Queue, arena and party classes are not yet connected to live arena worlds, player teleportation, scheduled countdowns, forms, persistence or commands. They must not be treated as a production matchmaking or party network.

## Extension direction

New game modes should implement a common server-side game contract before they are registered with matchmaking. An adapter should own lifecycle hooks (prepare, start, tick/event handling, finish and cleanup), while the shared match service owns participants, arena reservations and state transitions. Economy transactions and persistent profiles should be injected as services rather than called through static state.

`Main` remains the composition root; world, lobby, game, storage and core responsibilities should stay in their separate namespaces.
