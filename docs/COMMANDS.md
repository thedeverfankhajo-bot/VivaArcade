# Commands

All commands below are registered by the current PocketMine-MP API 5 plugin manifest. They require the \`vivaarcade.command\` permission and player execution.

| Command | Current behavior |
|---|---|
| \`/arcade\` | Opens the native Bedrock main menu |
| \`/games\` | Opens the implemented game selector (currently Parkour Rush only) |
| \`/profile\` | Opens the saved UUID profile summary |
| \`/stats\` | Sends the current game and Parkour statistics |
| \`/balance\` | Displays the current Dollar balance |
| \`/leave\` | Leaves the active Parkour Rush session and returns to lobby |
| \`/spawn\` | Leaves active Parkour Rush, then teleports to the saved lobby spawn |

## Notes

- Game-facing output uses Finglish.
- \`/spawn\` is a navigation command, not a match recovery or spectator command.
- \`/shop\`, \`/party\`, \`/daily\`, \`/top\`, and \`/arcadmin\` are not registered in this revision.
- The PartyManager and matchmaking classes are currently standalone core services, not live command integrations.
