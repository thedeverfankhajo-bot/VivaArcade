VIVAARCADE - POCKETMINE-MP API 5
================================

CURRENT SOURCE BUILD
--------------------
The current source tree is authoritative:
  src/
  resources/
  plugin.yml

The historical dist/source.zip is truncated and is not used by the current
build. It has not been repaired and does not contain the recovered original
Phase 1 source tree.

BUILD
-----
Requirements:
- PocketMine-MP API 5 target
- PHP CLI 8.1+
- Phar, json, hash and zlib extensions
- sha256sum

From the repository root:
  chmod +x build.sh
  ./build.sh

The build runs PHP syntax checks, the core regression suite, JSON storage
checks, snapshot codec checks, PHAR creation and manifest validation.
A successful build creates:
  dist/VivaArcade.phar
  dist/SHA256SUMS.txt

INSTALL
-------
1. Copy dist/VivaArcade.phar into the PocketMine-MP plugins/ directory.
2. Start a test PocketMine-MP 5 server.
3. On first run, the plugin asks PocketMine to create worlds/VivaArcade/.
4. Join with a Bedrock client and run /arcade.

IMPORTANT SCOPE
---------------
The current source foundation has one live Solo Parkour Rush mode, native
Bedrock forms, UUID-based JSON profiles, first-run world generation and
dependency-light queue/party/match core services.

It is NOT the complete 24+ mode network. Live matchmaking and party adapters,
the full economy/shops, challenges, tournaments, leaderboards, admin panel,
SQLite adapter, complete arena systems and the full district world remain
unimplemented.

No live PocketMine startup or Bedrock client test is claimed by the CI build.
Test on a disposable server before public deployment.

WORLD EXPORT
------------
A real LevelDB world ZIP can only be generated after PocketMine has created
and saved worlds/VivaArcade/. Stop PocketMine cleanly, then run:
  VIVAARCADE_SERVER_STOPPED=1 ./scripts/export-world.sh

The export script refuses to create a fake world or archive without level.dat
and a non-empty db/ directory. No VivaArcade-World.zip is included yet.
