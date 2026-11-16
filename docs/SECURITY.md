# Security

## Current safeguards

- Player profile paths are derived only from validated UUIDs.
- Profile data is protected by a per-UUID advisory lock and an atomic temporary-file replacement.
- Invalid JSON and mismatched profile identity are rejected without replacing the original file.
- Parkour rewards use server-generated transaction IDs and a processed-transaction index to prevent duplicate credits.
- General Dollar transactions use unique transaction IDs, a transaction-type allow-list in WalletService, bounded amounts and a processed-transaction index.
- Dollar debits are checked against the locked current balance; insufficient funds do not mutate the profile.
- Dollar balance, lifetime earned/spent totals and the bounded recent ledger are persisted in the same atomic profile write.
- Integer overflow is rejected for wallet balance and lifetime transaction totals.
- Core queue, party and match primitives validate UUIDs and state transitions.
- Lobby block placement and breaking are cancelled for players in the dedicated VivaArcade world.
- Admin permission is declared as operator-only, but an admin panel/command is not yet implemented.

## Known limitations

- JSON storage is a single-server local adapter; it is not a distributed economy ledger.
- The wallet is a server-side service API, not a complete shop or reward integration. Daily rewards, challenge payouts, cosmetics purchases and admin economy tools are not yet connected.
- No SQLite backend, automated database backups, cross-server transaction coordination or recovery procedure for filesystem/device failure exists yet.
- The retained processed-transaction index grows over time; a future durable ledger migration must preserve idempotency across compaction.
- Live matchmaking, party actions and arena isolation are not connected to PocketMine player state.
- No live server/client penetration test or load test is claimed.
