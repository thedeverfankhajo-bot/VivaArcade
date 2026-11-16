# VivaArcade test results

Updated: 2026-11-16

## Automated checks configured in the build

1. PHP syntax validation for packaged source and test PHP files.
2. Existing dependency-light core regression suite (`tests/run.php`).
3. UUID storage defaults, transaction and duplicate reward checks (`tests/storage.php`).
4. Versioned match snapshot round-trip and invalid snapshot checks (`tests/snapshot.php`).
5. Nineteen-district data, Finglish-label and coordinate checks (`tests/world-layout.php`).
6. Shell syntax checks in CI.
7. PHAR creation and manifest-entry validation (`build.sh`).

## Verification boundary

- The previous main-branch CI reported a successful run for the lobby/storage integration before the incremental district-layout commits.
- The updated district test and PHAR build must be confirmed by the GitHub Actions run for the latest main commit before claiming the new code passes CI.
- No PocketMine-MP server startup, Bedrock client connection, LevelDB generation/export, live minigame physics, load test or world protection test is claimed.
- The district layout is represented by a deterministic block operation stream; it has not yet been visually inspected in a real Bedrock world.
- The dedicated world ZIP is intentionally not fabricated.

See the repository Actions run before treating this as release-ready.
