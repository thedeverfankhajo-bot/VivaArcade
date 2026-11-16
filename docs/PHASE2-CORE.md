# VivaArcade Phase 2 Core Primitives

This contribution adds deterministic, dependency-light state machines for matchmaking, match isolation, arena ownership and parties.

## Included modules

- `VivaArcade\Core\MatchSession`: server-authoritative match lifecycle (`waiting -> countdown -> running -> finished/cancelled`), UUID participant registry, server-side scores, disconnect/reconnect state, idempotent reward claims and a validated version-1 JSON snapshot codec (`toArray()` / `fromArray()`).
- `VivaArcade\Core\ArenaPool`: exclusive arena reservation with owner-scoped release. One match cannot release another match's arena.
- `VivaArcade\Core\QueueManager`: FIFO queues, one active queue per UUID, one active match per player, configured minimum/maximum player counts and formation of multiple independent sessions while arenas are available.
- `VivaArcade\Party\Party` and `PartyManager`: UUID membership, leader-only invites/kicks, expiring invites (including exact-deadline invalidation), membership uniqueness and deterministic leadership transfer.

The snapshot codec validates state, timestamps, UUID-indexed participants, scores and reward claims before restoring a session. It is a serialization boundary for a trusted storage adapter; it does not write to disk or provide crash recovery by itself. The classes intentionally do not retain `Player` objects. A PMMP adapter should resolve UUIDs to online players when it needs to teleport, open a form or send a message. Invoke these services on PocketMine's main thread; do not call them from async workers.

## Important integration boundary

These are tested core primitives, **not a completed in-game matchmaking feature**. They are not yet wired to `/queue`, Bedrock Forms, a PMMP scheduler, a concrete mini-game adapter, party chat, persistent recovery or a live arena reset implementation. A plugin adapter must:

1. register concrete game rules and arena IDs after the dedicated world is loaded;
2. call `formMatches()` on a bounded main-thread schedule;
3. schedule and execute countdowns, call `startMatch()` and dispatch the selected game's real gameplay adapter;
4. route all score changes through the active `MatchSession`;
5. call `finishMatch()` or `cancelMatch()` from every terminal path, including disconnect and shutdown handling;
6. persist match summaries and issue economy rewards through the existing transaction service, not by trusting client input.

Do not expose these classes as proof of multiplayer readiness until those adapter and Bedrock integration tests pass.

## Tests

Run `php tests/run.php`. The current suite passes 58 deterministic checks covering queue uniqueness, arena ownership, parallel-match isolation, score validation, disconnect/reconnect, terminal transitions, duplicate reward prevention, snapshot round-trip/tamper rejection, invite expiry at the exact deadline, membership uniqueness and party leadership transfer.
