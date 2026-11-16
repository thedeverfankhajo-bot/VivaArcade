<?php

declare(strict_types=1);

require_once __DIR__ . "/../src/VivaArcade/Storage/JsonPlayerStorage.php";
require_once __DIR__ . "/../src/VivaArcade/Economy/WalletService.php";
require_once __DIR__ . "/../src/VivaArcade/Challenges/DailyRewardService.php";

use VivaArcade\Challenges\DailyRewardService;
use VivaArcade\Economy\WalletService;
use VivaArcade\Storage\JsonPlayerStorage;

$checks = 0;
function dailyCheck(bool $condition, string $message) : void{
    global $checks;
    ++$checks;
    if(!$condition){
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "vivaarcade-daily-" . bin2hex(random_bytes(6));
$uuid = "00000000-0000-4000-8000-000000000021";
$utc = new \\DateTimeZone("UTC");
$dayOne = new \\DateTimeImmutable("2026-11-16 12:00:00", $utc);
$dayTwo = new \\DateTimeImmutable("2026-11-17 00:00:01", $utc);

try{
    $storage = new JsonPlayerStorage($directory);
    $wallet = new WalletService($storage);
    $daily = new DailyRewardService($storage, $wallet, 80, 60);
    $before = $daily->getStatus($uuid, "DailyTester", $dayOne);
    dailyCheck($before["date"] === "2026-11-16" && !$before["claimed"], "new day begins claimable");
    dailyCheck($before["dollar"] === 80 && $before["xp"] === 60, "configured reward values are reported");
    dailyCheck($daily->claim($uuid, "DailyTester", $dayOne), "first daily reward claim succeeds");
    dailyCheck(!$daily->claim($uuid, "DailyTester", $dayOne), "same-day repeated claim is rejected");

    $afterFirst = $storage->getProfile($uuid, "DailyTester");
    dailyCheck($afterFirst["daily_claimed_on"] === "2026-11-16", "UTC claim date is persisted");
    dailyCheck($afterFirst["dollar"] === 80 && $afterFirst["xp"] === 60, "Dollar and XP are credited together");
    dailyCheck($afterFirst["level"] === 1, "level remains consistent below the threshold");
    dailyCheck(count($afterFirst["transactions"]) === 1, "daily claim creates one wallet ledger entry");
    dailyCheck($afterFirst["transactions"][0]["type"] === "daily_reward", "ledger identifies the daily reward source");
    dailyCheck($afterFirst["transactions"][0]["balance_after"] === 80, "ledger records the post-claim balance");

    $reloaded = new DailyRewardService($storage, new WalletService($storage), 80, 60);
    dailyCheck($reloaded->getStatus($uuid, "DailyTester", $dayOne)["claimed"], "claim survives service recreation");
    dailyCheck($reloaded->claim($uuid, "DailyTester", $dayTwo), "claim becomes available after UTC midnight");
    dailyCheck(!$reloaded->claim($uuid, "DailyTester", $dayTwo), "next-day claim is still idempotent");
    $afterSecond = $storage->getProfile($uuid, "DailyTester");
    dailyCheck($afterSecond["dollar"] === 160 && $afterSecond["xp"] === 120, "second daily reward is credited exactly once");
    dailyCheck($afterSecond["level"] === 2, "level advances when accumulated XP reaches 100");
    dailyCheck($afterSecond["total_earned"] === 160, "daily rewards update lifetime Dollar earnings");
    dailyCheck(count($afterSecond["transactions"]) === 2, "ledger retains both daily reward transactions");

    $invalidConfigRejected = false;
    try{
        new DailyRewardService($storage, $wallet, 0, 10);
    }catch(\\InvalidArgumentException){
        $invalidConfigRejected = true;
    }
    dailyCheck($invalidConfigRejected, "invalid daily reward configuration is rejected");
    echo "PASS: {$checks} VivaArcade daily reward checks\\n";
}finally{
    if(is_dir($directory)){
        foreach(glob($directory . DIRECTORY_SEPARATOR . "*") ?: [] as $file){
            if(is_file($file)){ unlink($file); }
        }
        rmdir($directory);
    }
}
