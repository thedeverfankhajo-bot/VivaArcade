# VivaArcade test results

Updated: 2026-11-17

## CI build for 0.7.0

Run: https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43977378958

- Commit: `e334d092dd71599fd414c9e4d237afef43af16d6`
- Branch: `main`; status: `success`
- PHP runtime: 8.1.34
- Shell syntax checks: passed
- PHP syntax checks: passed for all source and test files
- Core: 86 passed
- Storage: 20 passed
- Economy: 19 passed
- Daily reward: 18 passed
- Match snapshot: 13 passed
- World layout: 137 passed
- Total: 293 assertions passed
- PHAR manifest validation, packaging and artifact upload: passed

The logs contain one pre-existing non-fatal PHP warning in `tests/economy.php`: a global `use InvalidArgumentException` statement has no effect.

## Plugin artifact

Artifact: https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43977378958/artifacts/14280324849

```text
84ed0f35a9be4c919734699d62c50cc1c9540a1a0931ad03f524dbd824dee16e  VivaArcade.phar
```

The workflow validated the PHAR manifest and required files. Metadata declares PocketMine API 5.0.0 and plugin version 0.7.0.

## Real PocketMine world artifact

World workflow: https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43977378888

- Source commit: `e334d092dd71599fd414c9e4d237afef43af16d6`
- PocketMine-MP: 5.44.3
- Plugin built with the official PocketMine PHP runtime
- VivaArcade world layout completed
- Server stopped cleanly before export
- `level.dat` and non-empty LevelDB `db/` directory verified
- ZIP integrity check passed

Artifact: https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43977378888/artifacts/14280998558

```text
f2cbd7bf1edc8ddf6748c90e000657d11cdcdd669d87bcad8acccb3cb4ef2d91  VivaArcade-World.zip
```

This is a PocketMine LevelDB ZIP, not a Bedrock `.mcworld`.

## Verification boundary

- No Bedrock client was connected; forms and in-game player interactions have not been client-tested.
- The generated 19-district world has not been visually inspected in a Bedrock client.
- 24 playable modes and the requested full multiplayer/network systems are not complete.
- Party UI/commands, invitations and party chat are connected, but party queueing/private matches and restart restoration are not implemented.
- No Resource Pack is shipped.
