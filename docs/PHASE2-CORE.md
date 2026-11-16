# VivaArcade Phase 2 Core Primitives

The dependency-light core services are designed to be adapted to PocketMine runtime events without embedding PMMP objects into the state machines.

## Included modules

- `VivaArcade\\Core\\MatchSession`: server-authoritative lifecycle, UUID participant registry, server-side scores, connected-player checks, disconnect/reconnect state and idempotent reward claims.
- `VivaArcade\\Core\\ArenaPool`: exclusive arena reservation with owner-scoped release.
- `VivaArcade\\Core\\QueueManager`: FIFO queues, one queue per UUID, one active match per player, disconnect cleanup, reconnect routing, concurrent isolated sessions and bounded terminal-session retention.
- `VivaArcade\\Party\\Party` and `PartyManager`: UUID membership, leader-only invites/kicks, invite decline/cancellation, strict expiry and leader management.
- `MatchSession::toArray()` / `MatchSession::fromArray()`: schema-versioned JSON-safe snapshots. Restore validates identity fields, timestamps, state transitions, UUID participants, score indexes, player limits and reward-claim ownership.

## Integration boundary

These services are deterministic core primitives. They are not yet connected to a live PMMP matchmaking scheduler, player queue commands, party forms/chat, match arena cloning, reward persistence or crash recovery. Snapshot serialization is not durable storage by itself.

The runtime foundation currently exposes only Solo Parkour Rush as a live game.
