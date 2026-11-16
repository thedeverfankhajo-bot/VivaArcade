# Architecture

## Current runtime composition

- \`VivaArcade\Main\`: PocketMine plugin entry point, event adapter, command routing and service bootstrap.
- \`World\ArcadeWorldManager\`: creates or loads the dedicated \`VivaArcade\` world and persists the world-layout installation state.
- \`World\WorldBuilder\`: builds the currently shipped plaza, connector and Parkour Rush course.
- \`Lobby\LobbyManager\`: teleports players to the dedicated world's persisted spawn location.
- \`Forms\ArcadeForm\`: native Bedrock JSON form implementation without FormAPI dependencies.
- \`Games\ParkourRush\`: live solo mode adapter, checkpoint/fall handling and server-side completion.
- \`Storage\JsonPlayerStorage\`: UUID-keyed player profiles, locked atomic file replacement and idempotent Parkour reward credits.
- \`Core\ArenaPool\`, \`Core\QueueManager\`, \`Core\MatchSession\`: dependency-light arena ownership, queue and match state primitives with snapshot validation.
- \`Party\Party\`, \`Party\PartyManager\`: UUID party and expiring invitation primitives.

## Runtime boundary

Only Parkour Rush is currently connected to live PocketMine events. Queue, arena and party classes are not yet connected to live arena worlds, player teleportation, scheduled countdowns, forms, persistence or commands. They must not be treated as a production matchmaking or party network.

## Extension direction

New game modes should implement a common server-side game contract before they are registered with matchmaking. An adapter should own lifecycle hooks (prepare, start, tick/event handling, finish and cleanup), while the shared match service owns participants, arena reservations and state transitions. Economy transactions and persistent profiles should be injected as services rather than called through static state.

\`Main\` remains the composition root; world, lobby, game, storage and core responsibilities should stay in their separate namespaces.
