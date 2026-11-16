# Security

## Current safeguards

- Player profile paths are derived only from validated UUIDs.
- Profile data is protected by a per-UUID file lock and an atomic temporary-file replacement.
- Invalid JSON and mismatched profile identity are rejected without replacing the original file.
- Parkour rewards use server-generated transaction IDs and a processed-transaction index to prevent duplicate credits.
- Reward amounts and Parkour durations are range-checked in the storage service.
- Core queue, party and match primitives validate UUIDs and state transitions.
- Lobby block placement and breaking are cancelled for players in the dedicated VivaArcade world.
- Admin permission is declared as operator-only, but an admin panel/command is not yet implemented.

## Known limitations

- JSON storage is a single-server local adapter; it is not a distributed economy ledger.
- Transaction idempotency is currently scoped to Parkour rewards.
- No SQLite backend, database backups, general-purpose wallet debit/credit API, shop receipt ledger or cross-server transaction coordination exists yet.
- Live matchmaking, party actions and arena isolation are not connected to PocketMine player state.
- No live server/client penetration test or load test is claimed.
