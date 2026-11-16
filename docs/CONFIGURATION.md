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
