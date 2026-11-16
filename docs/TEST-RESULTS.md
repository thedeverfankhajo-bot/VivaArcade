# VivaArcade test results

Updated: 2026-11-17

## CI build for 0.6.1

Run: https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43976065690

- Commit: `dd4f558d2a741ad0e28d2ada46d863d26557b1aa`
- Branch: `main`; status: `success`
- PHP runtime: 8.1.34
- Shell syntax checks: passed
- PHP syntax checks: passed for all source and test files
- Core: 79 passed
- Storage: 20 passed, including four overflow and no-partial-write cases
- Economy: 19 passed
- Daily reward: 18 passed
- Match snapshot: 13 passed
- World layout: 137 passed
- Total: 286 assertions passed
- PHAR manifest validation, packaging and artifact upload: passed

The logs contain one pre-existing non-fatal PHP warning in `tests/economy.php`: a global `use InvalidArgumentException` statement has no effect.

## Plugin artifact

Artifact: https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43976065690/artifacts/14280029041

```text
621b521d59c8334aa9538ba9681615a69e7950a478f944defc22e6c1a4c9d3d7  VivaArcade.phar
```

The CI workflow verified the PHAR manifest and required files. Metadata declares PocketMine API 5.0.0 and plugin version 0.6.1.

## Real PocketMine world artifact

World workflow for source commit `dd4f558d2a741ad0e28d2ada46d863d26557b1aa`: https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43976065617

- PocketMine-MP: 5.44.3
- Layout completed in the running server
- Server stopped before export
- `level.dat` and a non-empty LevelDB `db/` directory verified
- ZIP integrity and artifact upload passed

Artifact: https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43976065617/artifacts/14280173997

```text
9386528855c907aa84c74d8e40f9a3fedfa3a28adc144ffeed0335cd4accacf1  VivaArcade-World.zip
```

The world was generated with PocketMine-MP 5.44.3 using the version 0.6.1 plugin, saved after a clean server shutdown, and passed LevelDB file checks plus `unzip -t`. This is a PocketMine LevelDB ZIP, not a Bedrock `.mcworld`.

## Verification boundary
- No Bedrock client was connected; forms and actual player interactions still need client testing.
- The generated 19-district world has not been visually inspected in a Bedrock client.
- 24 playable modes and the full live multiplayer/network systems are not complete.
- No Resource Pack is shipped.
