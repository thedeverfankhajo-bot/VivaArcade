VIVAARCADE - POCKETMINE-MP API 5
================================

CURRENT DELIVERY STATUS
-----------------------
INSTALLATION IS CURRENTLY BLOCKED.

The committed dist/source.zip is truncated and is not a valid ZIP archive.
The build cannot extract the complete plugin source, and no valid
dist/VivaArcade.phar has been produced. Do not install a placeholder or
partial artifact.

RECOVERY REQUIRED
-----------------
1. Restore/re-upload the complete original Phase 1 source archive at
   dist/source.zip.
2. Run ./build.sh and confirm all syntax tests, core tests and PHAR validation
   pass.
3. Install the resulting VivaArcade.phar into plugins/ on a test
   PocketMine-MP 5 server.
4. Verify startup, world generation/loading and every menu/game using
   Minecraft Bedrock clients before production use.

WORLD EXPORT
------------
No dedicated LevelDB world has been exported by this repository build.
After a successful runtime start, save the world and stop PocketMine-MP
cleanly, then use:

VIVAARCADE_SERVER_STOPPED=1 ./scripts/export-world.sh

The export script requires a real worlds/VivaArcade/level.dat and non-empty
worlds/VivaArcade/db/ directory. It deliberately refuses to create a fake
world archive.

PHASE 2 CORE TESTS
------------------
The dependency-light matchmaking and party core has 71 passing CI checks.
These classes are not yet wired into live PMMP commands, Bedrock Forms,
scheduler/gameplay adapters or persistent match recovery.

See README.md, docs/STATUS.md, docs/TEST-RESULTS.md and
docs/SOURCE-RECOVERY.md.
