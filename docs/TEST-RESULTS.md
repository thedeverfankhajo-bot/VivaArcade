# VivaArcade test results

Updated: 2026-11-16

## Verified GitHub Actions build

CI run: https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43903997476

- Source commit: `d6a8f093f082426d97cb8f16c28c6d3388d0d8be`
- Branch: `main`
- Workflow status: `success`
- PHP runtime: PocketMine PHP 8.2.30
- Shell syntax checks: passed for `build.sh` and `scripts/export-world.sh`
- PHP syntax checks: passed for all packaged source and test PHP files
- Core regression suite: 79 passed
- Storage suite: 12 passed
- Economy suite: 19 passed
- Daily reward suite: 18 passed
- Match snapshot suite: 13 passed
- World layout suite: 137 passed
- Total reported assertions: 278 passed
- PHAR manifest validation: passed
- PHAR packaging and Actions artifact upload: passed

## Generated plugin artifact

Actions artifact: https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43903997476/artifacts/14243732209

SHA-256:

```text
cdbdbbf9165aea05b5753a2dc18dd2768133821679eb66abaa9c31ce3274a122  VivaArcade.phar
```

The PHAR was also opened independently after download; it contains `plugin.yml`, `src/Main.php` and `resources/config.yml`. Its plugin metadata declares PocketMine API 5.0.0 and plugin version 0.6.0.

## Verified runtime world export

World workflow: https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43903997245

- Source commit: `d6a8f093f082426d97cb8f16c28d6c3388d0d8be`
- PocketMine-MP: 5.44.3
- Plugin build with the PMMP PHP runtime: passed
- VivaArcade world layout: completed in the running server
- Server stopped before archive/export: passed
- `level.dat` and non-empty LevelDB `db/` entries: verified
- ZIP integrity check: passed
- World artifact upload: passed

Actions artifact: https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43903997245/artifacts/14245440150

SHA-256:

```text
c585b5658844cb79538e896e28fd95913d98e09ad1550f6bf52d1fa158851218  VivaArcade-World.zip
```

## Verification boundary

- No Bedrock client was connected. Native form rendering and player interaction still need a real client session.
- The generated world archive is a PocketMine LevelDB world ZIP. It has not been renamed to `.mcworld` and no Bedrock import is claimed.
- The 19-district layout completed in PocketMine, but visual inspection in a Bedrock client remains outstanding.
- The 24 requested playable modes and the full live network systems are not complete.
- No Resource Pack is claimed or shipped.

Do not treat the repository as a finished arcade network until the outstanding gameplay, multiplayer, client and feature checks have passed.
