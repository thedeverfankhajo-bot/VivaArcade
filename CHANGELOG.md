# Changelog

## 0.4.0 - 2026-11-16

### Added
- Added a reusable directory for 19 themed VivaArcade districts with unique IDs, coordinates, Finglish labels and color keys.
- Added a lazy world-layout operation stream for the central spawn plaza, illuminated avenues, district floors, pylons, gateways and the existing Parkour Rush course.
- Added a scheduler-driven world builder with separate per-tick budgets for chunk loading and block writes.
- Added a standalone regression test for district count, unique identifiers/centers, safe labels, color keys and coordinate lookup.

### Changed
- New worlds now build the version-2 district layout incrementally after plugin enable.
- The world layout marker is written atomically and completed only after every operation finishes.
- Interrupted builds are replayed from the deterministic operation stream.
- Existing completed version-1 worlds are preserved instead of being overwritten by automatic migration.
- The east-west avenue layout and district access paths avoid overwriting the existing Parkour Rush jump platforms.

### Verification
- Automated checks are wired into build.sh and GitHub Actions.
- PocketMine server startup, Bedrock client behavior, real world rendering and LevelDB export still require runtime verification.
