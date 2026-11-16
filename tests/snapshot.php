<?php

declare(strict_types=1);

require_once __DIR__ . "/../src/VivaArcade/Core/MatchSession.php";

use VivaArcade\Core\MatchSession;

$checks = 0;
function snapshotCheck(bool $condition, string $message) : void{
    global $checks;
    ++$checks;
    if(!$condition){
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$uuid = "00000000-0000-4000-8000-000000000001";
$match = new MatchSession("snapshot-match", "parkour-rush", "arena-01", 1, 2, 1000);
snapshotCheck($match->join($uuid, 1001), "participant joins snapshot match");
snapshotCheck($match->beginCountdown(), "snapshot match enters countdown");
snapshotCheck($match->start(1010), "snapshot match starts");
snapshotCheck($match->addScore($uuid, 42), "snapshot match records score");
snapshotCheck($match->finish(1050), "snapshot match finishes");
snapshotCheck($match->claimReward($uuid, "completion"), "finished match accepts reward claim");

$encoded = $match->toArray();
$restored = MatchSession::fromArray($encoded);
snapshotCheck($restored->getId() === $match->getId(), "snapshot restores match identity");
snapshotCheck($restored->getState() === MatchSession::FINISHED, "snapshot restores terminal state");
snapshotCheck($restored->getScores() === $match->getScores(), "snapshot restores scores");
snapshotCheck($restored->getParticipants() === $match->getParticipants(), "snapshot restores participant state");
snapshotCheck(!$restored->claimReward($uuid, "completion"), "snapshot preserves idempotent reward claims");

$invalid = $encoded;
$invalid["scores"] = [];
$rejected = false;
try{
    MatchSession::fromArray($invalid);
}catch(\InvalidArgumentException){
    $rejected = true;
}
snapshotCheck($rejected, "snapshot rejects incomplete score indexes");

$invalidVersion = $encoded;
$invalidVersion["schemaVersion"] = 999;
$rejectedVersion = false;
try{
    MatchSession::fromArray($invalidVersion);
}catch(\InvalidArgumentException){
    $rejectedVersion = true;
}
snapshotCheck($rejectedVersion, "snapshot rejects unknown schema versions");

echo "PASS: {$checks} VivaArcade snapshot checks\n";
