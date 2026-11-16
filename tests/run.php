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
    if(!$condition){
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
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
check($queues->startMatch($match->getId(), 1010), 'match starts');
check($match->addScore($players[0], 25), 'participant can score while running');
check(!$match->addScore($players[2], 25), 'non-participant cannot score');
check(!$match->addScore($players[0], -10), 'negative score is rejected');
check($match->disconnect($players[1]), 'disconnect is recorded');
check(!$match->addScore($players[1], 1), 'disconnected participant cannot score');
check($match->reconnect($players[1]), 'participant can reconnect');
check($queues->finishMatch($match->getId(), $pool, 1050), 'match finishes');
check($pool->ownerOf($match->getArenaId()) === null, 'arena is released on match finish');
check($match->claimReward($players[0], 'match-complete'), 'finished participant can claim');
check(!$match->claimReward($players[0], 'match-complete'), 'reward claim is idempotent');
check(!$match->claimReward($players[2], 'match-complete'), 'non-participant cannot claim');
check(!$queues->finishMatch($match->getId(), $pool, 1060), 'match cannot finish twice');

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

$invalidUuidRejected = false;
try{
    MatchSession::assertUuid('not-a-uuid');
}catch(InvalidArgumentException){
    $invalidUuidRejected = true;
}
check($invalidUuidRejected, 'invalid UUID is rejected');

echo "PASS: {$checks} VivaArcade core checks\n";
