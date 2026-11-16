# Testing

## Automated checks

Run from the repository root:

    php -l src/VivaArcade/Main.php
    php tests/run.php
    php tests/storage.php
    php tests/economy.php
    php tests/daily.php
    php tests/snapshot.php
    php tests/world-layout.php
    php tests/game-catalog.php
    ./build.sh

The build script checks PHP syntax for packaged source and test PHP files, runs queue/arena/party regressions, UUID storage and reward tests, Dollar wallet ledger tests, UTC daily reward claim tests, match-snapshot validation and the dependency-light district-directory regression. It then creates the PHAR, validates its manifest entries and generates SHA-256.

## What the automated tests cover

- Arena reservations are exclusive and only the owner can release them.
- Queue membership, disconnection, match formation, state transitions and duplicate score/reward guards.
- Party membership, expiring invites, leadership succession, kick and invite cancellation.
- JSON profile defaults, unique Parkour reward credits, wallet/XP/stat persistence, invalid UUID/reward rejection and corrupted-profile preservation.
- Dollar credits and debits, duplicate transaction IDs, insufficient-funds protection, transaction source validation, lifetime totals, signed ledger entries and transaction history limits.
- Daily rewards: UTC date boundary, same-day duplicate prevention, persistent claim state, atomic Dollar/XP credit, level calculation and ledger entries.
- Versioned match snapshot round-trip and rejection of invalid schemas and inconsistent indexes.
- Game Browser catalog: only implemented modes/categories are exposed; solo limits, direct-start status and shared-course metadata are validated.
- Nineteen district definitions, unique IDs/centers, valid color keys, ASCII Finglish names, center lookup, spawn lookup and outside-map lookup.

## Not covered yet

- PocketMine server boot and plugin enable.
- Native Bedrock form rendering and client interaction.
- Real world generation, incremental task timing, world layout visuals and LevelDB export/import.
- Movement/physics timing for Parkour Rush on a live Bedrock client.
- Shop integration, weekly/seasonal challenges and achievement reward invocation.
- Matchmaking countdowns, arena clone/reset, party chat/queue and multiplayer game state.
- Performance, concurrency across multiple server processes, backup restore and recovery testing.

Do not advertise a successful server/client integration test until it has actually been run on a PocketMine-MP 5 server and a Bedrock client.
