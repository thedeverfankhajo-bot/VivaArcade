# VivaArcade Test Results

## Repository core tests

The original dependency-light tests cover queue uniqueness, arena ownership, parallel isolated match sessions, connected-player minimums, disconnect/reconnect, terminal match pruning, score validation, duplicate reward prevention and UUID-based party membership/invite flows. The source README recorded 71 passing checks on PHP 8.1.34 in GitHub Actions.

## Added storage tests

tests/storage.php covers new profile defaults, single reward credit, duplicate transaction rejection, Dollar/XP mutation, game statistics, and best-time updates.

## Required local checks

    bash -n build.sh
    bash -n scripts/export-world.sh
    php tests/run.php
    php tests/storage.php
    ./build.sh

## Runtime checks not performed here

- Starting the plugin in PocketMine-MP 5.
- Checking the exact PMMP API signatures against the runtime version.
- Generating/loading the world with PocketMine and verifying LevelDB.
- Joining with a Bedrock client, receiving forms, or testing platform physics.
- Running the Parkour Rush course in-world.
- Testing disconnects, restarts and concurrent live players.
- Load/performance and crash-recovery tests.

The current assistant environment had no network access from the shell and no installed PocketMine runtime. No runtime or Bedrock compatibility claim is made until those tests are performed.
