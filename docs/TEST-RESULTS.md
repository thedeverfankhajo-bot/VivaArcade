# VivaArcade test results

## Current main verification - categorized Game Browser

Commit: https://github.com/thedeverfankhajo-bot/VivaArcade/commit/d731e9fb794ae56b24e98392f294cf7d85b52cc9

CI run: https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43982942080

- Branch: main; status: success
- PHP runtime: 8.1.34
- Shell syntax checks: passed
- PHP syntax checks: passed for all source and test PHP files, including the new catalog suite
- Core: 86 passed
- Storage: 20 passed
- Economy: 19 passed
- Daily reward: 18 passed
- Match snapshot: 13 passed
- World layout: 218 passed
- Game catalog: 15 passed
- Total: 389 assertions passed across seven suites
- PHAR manifest validation, packaging and artifact upload: passed
- Non-fatal pre-existing warning: tests/economy.php imports global InvalidArgumentException with a no-effect use statement.

### Plugin artifact

Artifact: https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43982942080/artifacts/14284450909

```text
b070a66c052a4a20e8ed0ca2f07000aeae8a41779f13d1faf5bbf34dded93b1a  VivaArcade.phar
```

The build validated the PHAR manifest and required plugin files. This CI run did not start a PocketMine server or connect a Bedrock client.

## Previous real PocketMine world verification - layout update

Commit: https://github.com/thedeverfankhajo-bot/VivaArcade/commit/7b3e366596980ac07e13ba5c44105b609e4f8a4e

### PHP and PHAR CI

Run: https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43981599070

- Branch: main; status: success
- PHP runtime: 8.1.34
- Shell syntax checks: passed
- PHP syntax checks: passed for all source and test files
- Core: 86 passed
- Storage: 20 passed
- Economy: 19 passed
- Daily reward: 18 passed
- Match snapshot: 13 passed
- World layout: 218 passed
- Total: 374 assertions passed
- PHAR manifest validation, packaging and artifact upload: passed
- Non-fatal warning: tests/economy.php imports global InvalidArgumentException with a no-effect use statement.

### Plugin artifact

Artifact: https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43981599070/artifacts/14283173555

```text
8428f40ee07f898a7e795f5cc6e947f5a8423dc0956fc4f7ae9ff76c371d303c  VivaArcade.phar
```

The workflow validates the PHAR manifest and required files. Plugin metadata continues to declare PocketMine API 5.0.0.

### Real PocketMine world artifact

World workflow: https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43981599175

- PocketMine-MP: 5.44.3
- Official PocketMine PHP runtime used to build the plugin.
- Plugin booted in PocketMine; the world builder reported layout completion.
- Server stopped cleanly before export.
- level.dat, a non-empty LevelDB db/ directory and CURRENT, LOCK, LOG and MANIFEST files were present.
- ZIP integrity check passed.

Artifact: https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43981599175/artifacts/14281779262

```text
f6de82a73a4217378409a8b11b8e0dbe016a4aa9220d0223063d4dd02ec76caa  VivaArcade-World.zip
```

This is a PocketMine LevelDB ZIP, not a Bedrock .mcworld.

## Earlier verification history
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
