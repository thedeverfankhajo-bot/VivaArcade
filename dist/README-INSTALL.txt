VIVAARCADE - POCKETMINE-MP API 5
================================

INSTALL
-------
1. Use a PocketMine-MP 5 server and the PHP version required by that build.
2. Copy VivaArcade.phar into plugins/.
3. Start PMMP. The plugin creates or loads the dedicated VivaArcade world and
   builds/saves its lobby and arena structures on first world generation.
4. Join with Bedrock Edition and use /arcade or /games.

PLAYER COMMANDS
---------------
/arcade      Main menu
/games       Browse 15 solo mini-game modes
/profile     Player profile
/challenges  Daily challenge progress
/rewards     Claim daily reward
/shop        Cosmetic catalogue
/top         Leaderboards

ADMIN COMMANDS
--------------
/arcadeadmin help
/arcadeadmin give <online-player> <amount>
/arcadeadmin reload

STATUS / LIMITATIONS
--------------------
The PHAR build, PHP syntax tests, sanity tests and storage/economy tests ran
locally. The build environment does not contain PocketMine-MP, so live plugin
startup, Bedrock GUI/play tests and LevelDB world loading were NOT verified.
No fake World ZIP is included. Run the plugin on PMMP, stop it cleanly after
the world has saved, then export worlds/VivaArcade/.
All 15 game modes are solo. Parties/friends, matchmaking, scheduled events,
achievements, advanced cosmetics and a custom sidebar are not included.
See docs/STATUS.md.

BUILD FROM SOURCE
-----------------
Run ./build.sh (PHP CLI + Phar extension and phar.readonly=0 required).
