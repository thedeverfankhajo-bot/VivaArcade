<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/VivaArcade/Core/MatchSession.php';
require_once __DIR__ . '/../src/VivaArcade/Core/ArenaPool.php';
require_once __DIR__ . '/../src/VivaArcade/Core/QueueManager.php';
require_once __DIR__ . '/../src/VivaArcade/Party/Party.php';
require_once __DIR__ . '/../src/VivaArcade/Party/PartyManager.php';

use VivaArcade\Core\ArenaPool;
use VivaArcade\Core\MatchSession;
use VivaArcade\Core\QueueManager;
use VivaArcade\Party\PartyManager;

$checks = 0;
function check(bool $condition, string $message) : void{
    global $checks;
    ++$checks;
    if(!$condition){ fwrite(STDERR, "FAIL: {$message}\n"); exit(1); }
}

$players = [
    '00000000-0000-4000-8000-000000000001',
    '00000000-0000-4000-8000-000000000002',
    '00000000-0000-4000-8000-000000000003',
    '00000000-0000-4000-8000-000000000004'
];

$pool = new ArenaPool();
$pool->register('parkour-01');
$pool->register('parkour-02');
check($pool->reserve('parkour-01', 'match-a'), 'first arena reservation');
check(!$pool->reserve('parkour-01', 'match-b'), 'cannot reserve an occupied arena');
check(!$pool->release('parkour-01', 'match-b'), 'foreign match cannot release arena');
check($pool->release('parkour-01', 'match-a'), 'owner can release arena');

$queues = new QueueManager();
$queues->registerGame('parkour-rush', 2, 2);
$queues->join($players[0], 'parkour-rush', 1000);
$queues->join($players[1], 'parkour-rush', 1001);
check(!$queues->join($players[0], 'parkour-rush', 1002), 'player cannot queue twice');
check($queues->getQueueCount('parkour-rush') === 2, 'queue count');
$matches = $queues->formMatches($pool, 1003);
check(count($matches) === 1, 'one match formed');
$match = $matches[0];
check($match->getState() === MatchSession::COUNTDOWN, 'match enters countdown');
check($match->getPlayerCount() === 2, 'match has expected players');
check($pool->ownerOf($match->getArenaId()) === $match->getId(), 'arena is bound to exact match');
check($queues->getActiveMatchFor($players[0]) === $match, 'participant indexed to active match');
check(!$queues->join($players[0], 'parkour-rush', 1004), 'active participant cannot enter another queue');
check($queues->startMatch($match->getId(), 1010), 'match starts');
check($match->addScore($players[0], 25), 'participant can score while running');
check(!$match->addScore($players[2], 25), 'non-participant cannot score');
check(!$match->addScore($players[0], -10), 'negative score is rejected');
check($match->disconnect($players[1]), 'disconnect is recorded');
check(!$match->addScore($players[1], 1), 'disconnected participant cannot score');
check($match->reconnect($players[1]), 'participant can reconnect');
check($queues->finishMatch($match->getId(), $pool, 1050), 'match finishes');
check($pool->ownerOf($match->getArenaId()) === null, 'arena is released on match finish');
check($queues->getActiveMatchFor($players[0]) === null, 'active match index is cleared on finish');
check($queues->join($players[0], 'parkour-rush', 1051), 'participant may queue again after match ends');
check($queues->leave($players[0]), 'requeued participant can leave queue');
check($match->claimReward($players[0], 'match-complete'), 'finished participant can claim');
check(!$match->claimReward($players[0], 'match-complete'), 'reward claim is idempotent');
check(!$match->claimReward($players[2], 'match-complete'), 'non-participant cannot claim');
$snapshot = $match->toArray();
$restoredMatch = MatchSession::fromArray($snapshot);
check($restoredMatch->getId() === $match->getId() && $restoredMatch->getState() === MatchSession::FINISHED, 'match snapshot restores identity and terminal state');
check($restoredMatch->getScores() === $match->getScores() && $restoredMatch->getParticipants() === $match->getParticipants(), 'match snapshot restores scores and participant state');
check(!$restoredMatch->claimReward($players[0], 'match-complete'), 'match snapshot preserves idempotent reward claims');
$invalidSnapshot = $snapshot;
unset($invalidSnapshot['scores'][$players[0]]);
$snapshotRejected = false;
try{ MatchSession::fromArray($invalidSnapshot); }catch(InvalidArgumentException){ $snapshotRejected = true; }
check($snapshotRejected, 'match snapshot rejects incomplete score indexes');
check(!$queues->finishMatch($match->getId(), $pool, 1060), 'match cannot finish twice');

$parallelQueues = new QueueManager();
$parallelQueues->registerGame('reaction-test', 2, 2);
foreach($players as $index => $uuid){ $parallelQueues->join($uuid, 'reaction-test', 3000 + $index); }
$parallelMatches = $parallelQueues->formMatches($pool, 3004);
check(count($parallelMatches) === 2, 'two arena instances can form concurrent matches');
check($parallelMatches[0]->getArenaId() !== $parallelMatches[1]->getArenaId(), 'concurrent matches use distinct arenas');
check(array_intersect($parallelMatches[0]->getParticipantUuids(), $parallelMatches[1]->getParticipantUuids()) === [], 'concurrent matches have disjoint players');
check(!$parallelQueues->join($players[0], 'reaction-test', 3005), 'active participant cannot be queued twice across sessions');
check($parallelQueues->cancelMatch($parallelMatches[0]->getId(), $pool), 'first concurrent match can be cancelled');
check($parallelQueues->cancelMatch($parallelMatches[1]->getId(), $pool), 'second concurrent match can be cancelled');

$parties = new PartyManager(4, 30);
$party = $parties->create($players[0], 2000);
check($party !== null, 'party creation');
check(!$parties->create($players[0], 2001), 'member cannot create a second party');
check($parties->invite($players[0], $players[1], 2002), 'leader can invite');
check(!$parties->accept($players[2], 2003), 'uninvited player cannot join');
check($parties->accept($players[1], 2004), 'invite can be accepted');
check($party->getMemberCount() === 2, 'party member count');
check($parties->invite($players[0], $players[2], 2005), 'second invite is created');
check($parties->cleanupExpiredInvites(2036) === 1, 'expired invite is removed');
check(!$parties->accept($players[2], 2037), 'expired invite cannot be accepted');
check($parties->leave($players[0]), 'leader can leave');
check($party->getLeaderUuid() === $players[1], 'leadership transfers to remaining member');
check($parties->invite($players[1], $players[2], 2040), 'new leader can invite');
check($parties->accept($players[2], 2041), 'new invite can be accepted');
check($parties->kick($players[1], $players[2]), 'leader can kick a member');
check($parties->getPartyFor($players[2]) === null, 'kicked member is removed from index');
check($parties->invite($players[1], $players[3], 2100), 'party invite is created for expiry-boundary check');
check(!$parties->accept($players[3], 2130), 'party invite is expired at the exact expiry timestamp');
check($parties->invite($players[1], $players[2], 2140), 'party invite is created for cleanup-boundary check');
check($parties->cleanupExpiredInvites(2170) === 1, 'cleanup removes invites at the exact expiry timestamp');

$invalidUuidRejected = false;
try{ MatchSession::assertUuid('not-a-uuid'); }catch(InvalidArgumentException){ $invalidUuidRejected = true; }
check($invalidUuidRejected, 'invalid UUID is rejected');

echo "PASS: {$checks} VivaArcade core checks\n";
