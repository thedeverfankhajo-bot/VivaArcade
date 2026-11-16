# Testing

## Automated checks

Run from the repository root:

\`\`\`bash
php -l src/VivaArcade/Main.php
php tests/run.php
php tests/storage.php
php tests/snapshot.php
./build.sh
\`\`\`

The build script checks PHP syntax for packaged source and test files, runs the core queue/arena/party tests, runs UUID storage/reward regressions, runs match-snapshot validation, then creates the PHAR and validates its manifest entries before generating SHA-256.

## What the automated tests cover

- Arena reservations are exclusive and only the owner can release them.
- Queue membership, disconnection, match formation, state transitions and duplicate score/reward guards.
- Party membership, expiring invites, leadership succession, kick and invite cancellation.
- JSON profile defaults, unique Parkour reward credits, wallet/XP/stat persistence, invalid UUID/reward rejection and corrupted-profile preservation.
- Versioned match snapshot round-trip and rejection of invalid schemas and inconsistent indexes.

## Not covered yet

- PocketMine server boot and plugin enable.
- Native Bedrock form rendering and client interaction.
- Real world generation, world layout visuals and LevelDB export/import.
- Movement/physics timing for Parkour Rush on a live Bedrock client.
- Matchmaking countdowns, arena clone/reset, party chat/queue and multiplayer game state.
- Performance, concurrency across multiple server processes, backup restore and recovery testing.

Do not advertise a successful server/client integration test until it has actually been run on a PocketMine-MP 5 server and a Bedrock client.
