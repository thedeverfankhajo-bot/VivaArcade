# Configuration

The current \`resources/config.yml\` contains the Parkour Rush completion reward:

\`\`\`yaml
parkour:
  reward-dollar: 50
  reward-xp: 30
\`\`\`

- \`parkour.reward-dollar\`: bounded by the plugin to 1–1000 Dollar per completed run.
- \`parkour.reward-xp\`: bounded by the plugin to 1–500 XP per completed run.

The reward amount is read during plugin enable and is issued only by the server after the Parkour Rush finish condition is reached. There are no configurable shop prices, challenge rewards, party limits or matchmaking timers in this revision.


## Daily reward

The default configuration also includes:

```yaml
daily-reward:
  reward-dollar: 100
  reward-xp: 25
```

- `daily-reward.reward-dollar`: 1–100,000 Dollar.
- `daily-reward.reward-xp`: 0–100,000 XP.
- Claims reset at 00:00 UTC. The client cannot choose the date or reward values.
- The day marker, Dollar balance, lifetime earnings, XP, level and ledger entry are committed in one locked atomic profile write.
