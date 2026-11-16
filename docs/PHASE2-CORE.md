# VivaArcade Phase 2 Core Primitives

This contribution adds deterministic, dependency-light state machines for matchmaking, match isolation, arena ownership and parties.

## Included modules

- `VivaArcade\Core\MatchSession`: server-authoritative match lifecycle (`waiting -> countdown -> running -> finished/cancelled`), UUID participant registry, server-side scores, connected-player checks before countdown/start, disconnect/reconnect state and idempotent reward claims.
- `VivaArcade\Core\ArenaPool`: exclusive arena reservation with owner-scoped release. One match cannot release another match's arena.
- `VivaArcade\Core\QueueManager`: FIFO queues, one active queue per UUID, one active match per player, configured minimum/maximum player counts, immediate removal of disconnected queued players, active-match disconnect/reconnect routing, concurrent isolated sessions and bounded retention of terminal match summaries in memory.
- `VivaArcade\Party\Party` and `PartyManager`: UUID membership, leader-only invites/kicks/cancellation, invite decline, strict expiry at the expiry timestamp, membership uniqueness and deterministic leadership transfer.

The classes intentionally do not retain `Player` objects. A PMMP adapter should resolve UUIDs to online players when it needs to teleport, open a form or send a message. Invoke these services on PocketMine's main thread; do not call them from async workers.

## Important integration boundary

These are tested core primitives, **not a completed in-game matchmaking feature**. They are not yet wired to `/queue`, Bedrock Forms, a PMMP scheduler, a concrete mini-game adapter, party chat, persistent recovery or a live arena reset implementation. A plugin adapter must:

1. register concrete game rules and arena IDs after the dedicated world is loaded;
2. call `formMatches()` on a bounded main-thread schedule;
3. schedule and execute countdowns, call `startMatch()` and dispatch the selected game's real gameplay adapter;
4. route disconnect/reconnect through `QueueManager::disconnectPlayer()` and `reconnectPlayer()`, and restore or safely remove the player's PMMP state;
5. route all score changes through the active `MatchSession`;
6. call `finishMatch()` or `cancelMatch()` from every terminal path, including disconnect and shutdown handling;
7. persist match summaries and issue economy rewards through the existing transaction service, not by trusting client input.

Do not expose these classes as proof of multiplayer readiness until those adapter and Bedrock integration tests pass.

## Tests

Run `php tests/run.php`. The deterministic core suite covers queue uniqueness and offline queued-player cleanup, arena ownership, parallel-match isolation, connected-player minimum checks, disconnect/reconnect routing, terminal-session pruning, score validation, duplicate reward prevention, invite acceptance/decline/cancellation/expiry, membership uniqueness and party leadership transfer.
