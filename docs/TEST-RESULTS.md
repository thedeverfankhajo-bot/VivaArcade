# Test Results

Verification date: 2026-11-16.

## GitHub Actions — executed

The PR CI workflow ran on PHP **8.1.34**.

- Shell syntax: `bash -n build.sh` and `bash -n scripts/export-world.sh` — **PASS**.
- PHP syntax: `php -l` for all files in `src/` and `tests/` — **PASS**.
- Pure-core regression tests: `php tests/run.php` — **PASS: 79 checks**.
- PHAR build: **BLOCKED / FAIL (expected integrity guard)**. `unzip -t dist/source.zip` exits with code 9 because the committed ZIP is truncated and has no central directory/end-of-central-directory record. The build script stops before packaging rather than generating an incomplete PHAR.

## Source archive integrity

The committed `dist/source.zip` is 7,501 bytes. Parsing its local ZIP headers shows that it ends inside the compressed data for `src/VivaArcade/Lobby/LobbyManager.php`; the entry declares 3,248 compressed bytes while only 1,183 bytes remain in the file. There is no ZIP end-of-central-directory footer. The full Phase 1 source therefore cannot be extracted from the current commit.

## Not run / not verified

- Complete PHAR production or plugin startup in PocketMine-MP 5.
- PMMP world creation, load/save, LevelDB integrity or world export.
- Bedrock client login, forms, UI rendering or game controls.
- The older documentation's 15 solo game modes and the gameplay logic within the inaccessible part of the source archive.
- PMMP-integrated matchmaking, party UI/chat/queues, tournament flows, rewards integration or crash recovery.
- Production multiplayer load/concurrency tests and server performance.

No `VivaArcade.phar`, `VivaArcade-World.zip` or full package is claimed as created. The 79 passing tests exercise only the dependency-light Phase 2 PHP core services, not a running Minecraft server.
