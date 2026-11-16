<?php

declare(strict_types=1);

require_once __DIR__ . "/../src/VivaArcade/Storage/JsonPlayerStorage.php";
require_once __DIR__ . "/../src/VivaArcade/Economy/WalletService.php";

use InvalidArgumentException;
use VivaArcade\Economy\WalletService;
use VivaArcade\Storage\JsonPlayerStorage;

$checks = 0;
function economyCheck(bool $condition, string $message) : void{
    global $checks;
    ++$checks;
    if(!$condition){
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "vivaarcade-economy-" . bin2hex(random_bytes(6));
$uuid = "00000000-0000-4000-8000-000000000011";

try{
    $storage = new JsonPlayerStorage($directory);
    $wallet = new WalletService($storage);

    economyCheck($wallet->getBalance($uuid, "EconomyTester") === 0, "new wallet starts at zero");
    economyCheck($wallet->credit($uuid, "EconomyTester", 125, "game_reward", "game:match-001"), "server-side game reward is credited");
    economyCheck(!$wallet->credit($uuid, "EconomyTester", 125, "game_reward", "game:match-001"), "duplicate credit is rejected");
    economyCheck($wallet->getBalance($uuid, "EconomyTester") === 125, "duplicate credit does not change balance");

    economyCheck($wallet->debit($uuid, "EconomyTester", 45, "shop_purchase", "shop:receipt-001"), "shop debit is applied");
    economyCheck($wallet->getBalance($uuid, "EconomyTester") === 80, "debit changes balance exactly once");
    economyCheck(!$wallet->debit($uuid, "EconomyTester", 81, "shop_purchase", "shop:receipt-002"), "insufficient funds are rejected");
    economyCheck($wallet->getBalance($uuid, "EconomyTester") === 80, "failed debit leaves balance unchanged");

    economyCheck($wallet->credit($uuid, "EconomyTester", 20, "challenge_reward", "challenge:daily-001"), "challenge reward is credited");
    $summary = $wallet->getWallet($uuid, "EconomyTester");
    economyCheck($summary["balance"] === 100, "wallet summary reports balance");
    economyCheck($summary["total_earned"] === 145, "wallet summary accumulates earned Dollar");
    economyCheck($summary["total_spent"] === 45, "wallet summary accumulates spent Dollar");
    economyCheck(count($summary["transactions"]) === 3, "wallet ledger keeps committed transactions");
    economyCheck($summary["transactions"][1]["amount"] === -45, "debit is stored as a negative ledger entry");
    economyCheck($summary["transactions"][1]["balance_after"] === 80, "ledger stores post-transaction balance");
    economyCheck(count($wallet->getRecentTransactions($uuid, "EconomyTester", 2)) === 2, "transaction history limit is applied");

    $invalidTypeRejected = false;
    try{
        $wallet->credit($uuid, "EconomyTester", 1, "shop_purchase", "invalid:credit");
    }catch(InvalidArgumentException){
        $invalidTypeRejected = true;
    }
    economyCheck($invalidTypeRejected, "credit rejects a debit-only transaction type");

    $invalidAmountRejected = false;
    try{
        $wallet->debit($uuid, "EconomyTester", 0, "shop_purchase", "invalid:amount");
    }catch(InvalidArgumentException){
        $invalidAmountRejected = true;
    }
    economyCheck($invalidAmountRejected, "zero-value transactions are rejected");

    $invalidLimitRejected = false;
    try{
        $wallet->getRecentTransactions($uuid, "EconomyTester", 101);
    }catch(InvalidArgumentException){
        $invalidLimitRejected = true;
    }
    economyCheck($invalidLimitRejected, "history limit is bounded");

    echo "PASS: {$checks} VivaArcade economy checks\n";
}finally{
    if(is_dir($directory)){
        foreach(glob($directory . DIRECTORY_SEPARATOR . "*") ?: [] as $file){
            if(is_file($file)){ unlink($file); }
        }
        rmdir($directory);
    }
}
