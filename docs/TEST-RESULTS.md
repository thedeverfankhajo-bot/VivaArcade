# Test Results

Executed in the local build container. These are not PocketMine-MP integration tests.

## Passed

- PHP syntax lint across all source files.
- Project sanity checks: API 5 manifest, required core files and 15 game-mode registrations.
- JSON storage/economy tests: profile creation, cosmetic debit+ownership, duplicate purchase prevention, daily reward idempotence, game stat/reward update, challenge claim idempotence and insufficient-funds guard.
- PHAR creation and archive inspection; SHA-256 signature algorithm confirmed.
- Source ZIP integrity and SHA-256 checksum verification.

## Not run

- Loading the PHAR inside a live PocketMine-MP process.
- PMMP LevelDB world generation/load integration.
- Bedrock client GUI and gameplay testing.
- Full end-to-end gameplay test for all modes.
- Multiplayer matchmaking, concurrency/load tests and crash-recovery tests.
- Export/import testing for VivaArcade-World.zip.

The build environment has PHP CLI and Phar but no PocketMine-MP server runtime. The locally built PHAR is a real archive, but live PMMP compatibility remains unverified.
