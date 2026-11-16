# Source Archive Recovery

## Problem

The committed `dist/source.zip` cannot be extracted. The archive is 7,501 bytes and does not contain a ZIP end-of-central-directory record or central directory. A local-header parse reaches `src/VivaArcade/Lobby/LobbyManager.php`, whose header declares 3,248 compressed bytes, but only 1,183 bytes remain in the archive.

This means the archive is truncated inside a PHP source file. It is not safe to repair by guessing the missing bytes or writing replacement classes: the missing source may contain important PocketMine API 5 behavior and game/world logic.

## Required maintainer action

1. Recover the original complete source tree from the developer's working copy or backup.
2. Recreate `dist/source.zip` from that complete source tree, preserving the original relative paths (`plugin.yml`, `src/`, `resources/`, `composer.json`, and `LICENSE`).
3. Verify the archive locally with `unzip -t dist/source.zip`.
4. Check that the archive listing includes the plugin main class (declared in `plugin.yml` as `VivaArcade\\Main`), all game implementations, the world/lobby builder and their dependencies.
5. Commit the repaired archive, then run `./build.sh` and inspect the resulting PHAR contents.
6. Start the PHAR on a test PocketMine-MP 5 server and verify it with Bedrock clients before claiming runtime compatibility.

## Why no substitute archive was generated

The available repository has no earlier valid version of this ZIP in its commit history. The container also has no PocketMine-MP runtime or LevelDB world, and does not have the SQLite PDO extension. Reconstructing the missing Phase 1 implementation from filenames or documentation would be speculative and could introduce fake APIs, broken game rules or unsafe storage behavior.

The Phase 2 core services in `src/VivaArcade/Core/` and `src/VivaArcade/Party/` remain independently testable. Their 71 passing tests do not replace the missing plugin implementation and do not constitute a playable server.
