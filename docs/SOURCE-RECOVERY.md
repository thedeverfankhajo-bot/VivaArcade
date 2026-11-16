# Legacy Source Archive Status

Updated: 2026-11-16

## Historical archive

The committed `dist/source.zip` remains a truncated archive (7,501 bytes)
without a ZIP central directory/end record. It is not recoverable from the
available repository history without the missing original bytes. It should
not be patched by guessing source code.

## Current source tree

The repository now contains a clean, separate PocketMine-MP API 5 foundation
under `src/`, with a plugin entry point, Native Bedrock form, Solo Parkour
Rush, UUID JSON profile storage, a first-run dedicated-world builder, and the
previously tested matchmaking/party core.

The current `build.sh` uses the checked-in `src/`, `resources/`, and root
`plugin.yml` directly. It does not extract or depend on the damaged legacy
ZIP. A successful CI build confirms PHP syntax, core/storage/snapshot tests,
PHAR packaging and manifest validation for this current foundation.

## Recovery boundary

This integration does not reconstruct the missing original Phase 1 classes or
claim the full 24+ mode network. The current source is a new, smaller
foundation; features not present in the source are listed as pending in
`docs/STATUS.md`.

A real PocketMine runtime and Bedrock client are still needed to verify startup,
world generation/loading, form delivery, movement physics, gameplay and
world export. A real LevelDB world ZIP must only be created from a world saved
by PocketMine; see `scripts/export-world.sh`.
