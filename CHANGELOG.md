# Changelog

## 0.6.0 - 2026-11-16

### Added
- Added DailyRewardService with a once-per-UTC-day reward and native Bedrock claim form.
- Added an atomic storage transaction for the daily marker, Dollar balance, lifetime earnings, XP, level and unique ledger entry.
- Added `/daily` and a Finglish Daily Reward entry in the main menu, plus a Finglish Help screen.
- Added daily reward regression tests for duplicate claims, UTC rollover, persistence, XP, level and ledger updates.

### Changed
- Build now runs the daily reward regression suite before PHAR packaging.
- Plugin metadata version is now 0.6.0.

### Verification boundary
- The daily reward logic has standalone PHP regression coverage. Live PocketMine startup, Bedrock client form rendering and LevelDB world export need separate runtime confirmation.

## 0.5.0 - 2026-11-16

### Added
- Added Economy/WalletService with server-side credit/debit operations and transaction-source allow-lists.
- Added an idempotent Dollar ledger with signed entries, post-transaction balances, earned/spent lifetime totals and a bounded 100-entry history.
- Added balance-underflow and integer-overflow guards to wallet transactions.
- Added standalone wallet tests for credits, duplicate IDs, purchases, insufficient funds, source validation and wallet summaries.

### Changed
- Parkour Rush completion now updates total Dollar earnings and records the post-reward balance while preserving its single atomic reward/profile transaction.
- Existing profiles migrate missing earned/spent totals from their retained transaction history without discarding profile data.
- Main now composes WalletService and uses it for the /balance command.
- Build now runs the economy regression suite before creating the PHAR.
- Plugin metadata version is now 0.5.0.

### Verification boundary
- This source update adds dependency-light wallet coverage. Full PocketMine-MP startup, Bedrock client, world rendering, live shop integration and runtime PHAR installation still require separate verification.

## 0.4.0 - 2026-11-16

### Added
- Added a reusable directory for 19 themed VivaArcade districts with unique IDs, coordinates, Finglish labels and color keys.
- Added a lazy world-layout operation stream for the central spawn plaza, illuminated avenues, district floors, pylons, gateways and the existing Parkour Rush course.
- Added a scheduler-driven world builder with separate per-tick budgets for chunk loading and block writes.
- Added a standalone regression test for district count, unique identifiers/centers, safe labels, color keys and coordinate lookup.

### Changed
- New worlds now build the version-2 district layout incrementally after plugin enable.
- The world layout marker is written atomically and completed only after every operation finishes.
- Interrupted builds are replayed from the deterministic operation stream.
- Existing completed version-1 worlds are preserved instead of being overwritten by automatic migration.
- The east-west avenue layout and district access paths avoid overwriting the existing Parkour Rush jump platforms.

### Verification
- Automated checks are wired into build.sh and GitHub Actions.
- PocketMine server startup, Bedrock client behavior, real world rendering and LevelDB export still require runtime verification.
