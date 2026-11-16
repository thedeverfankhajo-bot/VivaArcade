# Commands

All commands below are registered by the current PocketMine-MP API 5 plugin manifest. They require the \`vivaarcade.command\` permission and player execution.

| Command | Current behavior |
|---|---|
| \`/arcade\` | Opens the native Bedrock main menu |
| \`/games\` | Opens the implemented game selector (currently Parkour Rush only) |
| \`/profile\` | Opens the saved UUID profile summary |
| \`/stats\` | Sends the current game and Parkour statistics |
| \`/balance\` | Displays the current Dollar balance |
| \`/daily\` | Opens the native Daily Reward form; one claim per UTC date |
| \`/leave\` | Leaves the active Parkour Rush session and returns to lobby |
| \`/spawn\` | Leaves active Parkour Rush, then teleports to the saved lobby spawn |

## Notes

- Game-facing output uses Finglish.
- \`/spawn\` is a navigation command, not a match recovery or spectator command.
- \`/shop\`, \`/party\`, \`/top\`, and \`/arcadmin\` are not registered in this revision.
- The PartyManager and matchmaking classes are currently standalone core services, not live command integrations.

## Party

- `/party` - Open the Native Bedrock Party menu.
- `/party create` - Create a party.
- `/party invite <player>` - Invite an online player; invitations are UUID-bound and expire after the configured TTL.
- `/party accept` / `/party decline` - Respond to a pending invitation.
- `/party leave` - Leave the current party; leadership transfers to the first remaining member.
- `/party kick <player>` - Leader-only removal of an online member.
- `/party leader <player>` - Leader-only transfer to an existing online member.
- `/party chat <message>` - Send a short Finglish message to online party members.

Party membership is session-scoped and cleared on disconnect or server restart. Party matchmaking, party queueing and private matches are not included.
