# Branch integration record

Updated: 2026-11-16

All non-default development branch histories have been accounted for in the integrated mainline. Branches already merged into `main` remain in its ancestry; the two unmerged work streams (PocketMine runtime foundation and snapshot validation) are incorporated as additional parents of the integration commit.

| Branch | Integration result |
|---|---|
| `codex/matchmaking-party-hardening` | Already merged through PR #3; its hardening remains in main. |
| `fix/queue-disconnect-cleanup` | Already merged through PR #5; disconnected queue cleanup remains in main. |
| `feature/pmmp-live-foundation` | Already an ancestor of main; no unique commit was left behind. |
| `feat/runtime-foundation` | Plugin entry point, native form, JSON storage, dedicated-world builder, Parkour Rush, config and storage tests integrated. |
| `feature/match-snapshot-party-expiry` | Snapshot lineage retained in the integrated history. |
| `feature/match-snapshot-party-expiry-ci` | Validated snapshot codec integrated selectively while retaining newer mainline queue/disconnect code. |

The integration deliberately does not label unfinished roadmap items as complete. See `docs/STATUS.md` for implemented and pending scope.
