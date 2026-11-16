# VivaArcade test results

Updated: 2026-11-16

## Automated checks configured in the build

1. PHP syntax validation for packaged source and test PHP files.
2. Existing dependency-light core regression suite (`tests/run.php`).
3. UUID storage defaults, transaction and duplicate reward checks (`tests/storage.php`).
4. Versioned match snapshot round-trip and invalid snapshot checks (`tests/snapshot.php`).
5. Shell syntax checks in CI.
6. PHAR creation and manifest-entry validation (`build.sh`).

## Verification boundary

- The previous main-branch CI reports 79 passing pure-core regression checks.
- The runtime-foundation branch included PHP syntax, core and JSON storage tests, but did not claim a live PocketMine runtime.
- Snapshot codec tests are included in the integrated build and must be confirmed by the CI run for the integration commit.
- No PocketMine-MP server startup, Bedrock client connection, LevelDB generation/export, live minigame physics, load test or world protection test is claimed.
- The dedicated world ZIP is intentionally not fabricated.

See the repository Actions run for the integrated commit before treating this as release-ready.
