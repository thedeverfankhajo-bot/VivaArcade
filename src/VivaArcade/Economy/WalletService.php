<?php

declare(strict_types=1);

namespace VivaArcade\Economy;

use InvalidArgumentException;
use VivaArcade\Storage\JsonPlayerStorage;

/**
 * Server-side wallet facade. Callers must supply transaction IDs created by
 * trusted game or shop services, never values accepted from a Bedrock form.
 */
final class WalletService{

    private const CREDIT_TYPES = [
        "game_reward",
        "challenge_reward",
        "daily_reward",
        "achievement_reward",
        "admin_adjustment"
    ];

    private const DEBIT_TYPES = [
        "shop_purchase",
        "admin_adjustment"
    ];

    public function __construct(private readonly JsonPlayerStorage $storage){}

    public function getBalance(string $uuid, string $playerName) : int{
        return (int) $this->storage->getProfile($uuid, $playerName)["dollar"];
    }

    /**
     * @return array{balance:int, total_earned:int, total_spent:int, transactions:list<array<string, mixed>>}
     */
    public function getWallet(string $uuid, string $playerName) : array{
        $profile = $this->storage->getProfile($uuid, $playerName);
        return [
            "balance" => (int) $profile["dollar"],
            "total_earned" => (int) $profile["total_earned"],
            "total_spent" => (int) $profile["total_spent"],
            "transactions" => array_values($profile["transactions"])
        ];
    }

    public function credit(
        string $uuid,
        string $playerName,
        int $amount,
        string $type,
        string $transactionId
    ) : bool{
        self::assertAmount($amount);
        if(!in_array($type, self::CREDIT_TYPES, true)){
            throw new InvalidArgumentException("This transaction type cannot credit Dollar");
        }
        return $this->storage->applyDollarTransaction($uuid, $playerName, $amount, $type, $transactionId);
    }

    public function debit(
        string $uuid,
        string $playerName,
        int $amount,
        string $type,
        string $transactionId
    ) : bool{
        self::assertAmount($amount);
        if(!in_array($type, self::DEBIT_TYPES, true)){
            throw new InvalidArgumentException("This transaction type cannot debit Dollar");
        }
        return $this->storage->applyDollarTransaction($uuid, $playerName, -$amount, $type, $transactionId);
    }

    /** Claim a daily reward through the atomic profile transaction. */
    public function claimDailyReward(
        string $uuid,
        string $playerName,
        string $utcDate,
        int $dollarReward,
        int $xpReward
    ) : bool{
        if($dollarReward < 1 || $dollarReward > 100_000 || $xpReward < 0 || $xpReward > 100_000){
            throw new InvalidArgumentException("Daily reward is outside the allowed range");
        }
        return $this->storage->claimDailyReward(
            $uuid, $playerName, $utcDate, $dollarReward, $xpReward, "daily:" . $utcDate
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getRecentTransactions(string $uuid, string $playerName, int $limit = 10) : array{
        if($limit < 1 || $limit > 100){
            throw new InvalidArgumentException("Transaction history limit must be between 1 and 100");
        }
        $transactions = $this->getWallet($uuid, $playerName)["transactions"];
        return array_slice($transactions, -$limit);
    }

    private static function assertAmount(int $amount) : void{
        if($amount < 1 || $amount > 1_000_000){
            throw new InvalidArgumentException("Dollar amount must be between 1 and 1000000");
        }
    }
}
