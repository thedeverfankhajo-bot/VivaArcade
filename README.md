# VivaArcade

Native PocketMine-MP API 5 arcade plugin for Minecraft Bedrock.

This repository contains the Phase 1 source: 15 solo mini-game modes, a PMMP-generated dedicated `VivaArcade` world, native Bedrock forms, player profiles, a transactional JSON Dollar economy, daily rewards, a shop cosmetic, leaderboards and admin commands.

## Build

Run `./build.sh` with PHP CLI and the Phar extension (`phar.readonly=0`). The script lints source, runs storage tests and builds `dist/VivaArcade.phar`.

## Install

Copy `dist/VivaArcade.phar` into PocketMine-MP 5 `plugins/`, restart the server, then use `/arcade` or `/games` in game. The world is generated and saved by PMMP on first run.

## Honest status

This is not yet the full network specification: game sessions are solo (no matchmaking), parties/friends/events/achievements/full cosmetic catalogue and a packet-backed sidebar are not implemented. The build environment does not have a PMMP server, so live plugin startup and LevelDB world integration are not verified. No fake world ZIP is supplied; export the PMMP-generated `worlds/VivaArcade/` folder after a clean shutdown.

See `docs/STATUS.md`, `docs/TEST-RESULTS.md`, `docs/INSTALLATION.md` and `docs/COMMANDS.md`.