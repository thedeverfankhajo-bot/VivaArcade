# VivaArcade test results

Updated: 2026-11-16

## Verified GitHub Actions build

<https://github.com/thedeverfankhajo-bot/VivaArcade/actions/runs/43887719089>

- Source commit: `9214d6436f2d2915a3c5157e906ed5fc47749a2b`
- Workflow status: `success`
- PHP runtime: PHP 8.1
- Shell syntax checks: passed for `build.sh` and `scripts/export-world.sh`
- PHP syntax checks: passed for every source and test PHP file
- Core regression suite: 79 passed
- Storage and idempotent-reward suite: 12 passed
- Match snapshot suite: 13 passed
- District/Finglish layout suite: 117 passed
- Total assertions reported by the four suites: 221 passed
- PHAR manifest validation: passed
- PHAR build: passed
- Actions artifact upload: passed (`vivaarcade-build`, artifact ID `14234614443`)

## Generated plugin artifact

GitHub Actions uploaded the built PHAR and its checksum in the `vivaarcade-build` artifact attached to the run above.

SHA-256 of the generated `VivaArcade.phar`:

```text
67986d54e522531e5134f9ba52c1a3a1f5f96e97663962720b5d4e744ab1d076  VivaArcade.phar
```

The PHAR is a CI build artifact, not a binary committed under the repository's `dist/` directory.

## Verification boundary

- The CI workflow checks PHP syntax and the dependency-light core behavior; it does not load the plugin against a running PocketMine-MP server.
- No PocketMine-MP server startup, Bedrock client connection, native form rendering, live movement physics, real world generation, district visual inspection, or LevelDB export/import is claimed.
- The 19-district map is a deterministic operation stream and needs runtime visual verification.
- The dedicated world ZIP is intentionally not fabricated.
- The 24 requested playable game modes and the full live network systems are not complete.

Do not treat the repository as a finished arcade network until the runtime/client and outstanding feature checks have passed.
