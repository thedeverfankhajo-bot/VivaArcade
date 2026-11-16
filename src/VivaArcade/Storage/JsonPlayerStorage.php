<?php

declare(strict_types=1);

namespace VivaArcade\Storage;

use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * UUID-keyed JSON profiles with per-player advisory locks and atomic replacement.
 * All calls are intended for PocketMine's main thread.
 */
final class JsonPlayerStorage{

    private const MAX_DOLLAR_TRANSACTION = 1_000_000;

    public function __construct(private readonly string $directory){
        if(!is_dir($directory) && !mkdir($directory, 0770, true) && !is_dir($directory)){
            throw new RuntimeException("Unable to create player storage directory");
        }
    }

    /** @return array<string, mixed> */
    public function getProfile(string $uuid, string $playerName) : array{
        return $this->transact($uuid, $playerName, static fn(array &$profile) : array => $profile);
    }

    /**
     * Apply a signed Dollar delta as one idempotent, locked profile transaction.
     * Positive deltas are credits; negative deltas are debits.
     */
    public function applyDollarTransaction(
        string $uuid,
        string $playerName,
        int $delta,
        string $type,
        string $transactionId
    ) : bool{
        if($delta === 0 || $delta < -self::MAX_DOLLAR_TRANSACTION || $delta > self::MAX_DOLLAR_TRANSACTION){
            throw new InvalidArgumentException("Invalid Dollar transaction amount");
        }
        if(!preg_match('/^[a-z][a-z0-9_]{1,31}$/', $type)){
            throw new InvalidArgumentException("Invalid Dollar transaction type");
        }
        self::assertTransactionId($transactionId);

        return $this->transact(
            $uuid,
            $playerName,
            static function(array &$profile) use ($delta, $type, $transactionId) : bool{
                $key = hash("sha256", $transactionId);
                if(isset($profile["processed"][$key])){
                    return false;
                }

                $balance = (int) $profile["dollar"];
                if($delta < 0 && $balance < -$delta){
                    return false;
                }
                if($delta > 0 && $balance > PHP_INT_MAX - $delta){
                    throw new RuntimeException("Dollar balance overflow");
                }

                if($delta > 0){
                    if($profile["total_earned"] > PHP_INT_MAX - $delta){
                        throw new RuntimeException("Dollar earned total overflow");
                    }
                    $profile["total_earned"] += $delta;
                }else{
                    $spent = -$delta;
                    if($profile["total_spent"] > PHP_INT_MAX - $spent){
                        throw new RuntimeException("Dollar spent total overflow");
                    }
                    $profile["total_spent"] += $spent;
                }

                $profile["dollar"] = $balance + $delta;
                $profile["processed"][$key] = true;
                $profile["transactions"][] = [
                    "id" => $transactionId,
                    "type" => $type,
                    "amount" => $delta,
                    "balance_after" => $profile["dollar"],
                    "created_at" => time()
                ];
                if(count($profile["transactions"]) > 100){
                    $profile["transactions"] = array_slice($profile["transactions"], -100);
                }
                return true;
            }
        );
    }

    public function completeParkour(
        string $uuid,
        string $playerName,
        int $durationMs,
        int $dollarReward,
        int $xpReward,
        string $transactionId
    ) : bool{
        if($durationMs < 1 || $durationMs > 3_600_000){
            throw new InvalidArgumentException("Invalid Parkour Rush duration");
        }
        if($dollarReward < 0 || $dollarReward > 100_000 || $xpReward < 0 || $xpReward > 100_000){
            throw new InvalidArgumentException("Invalid Parkour Rush reward");
        }
        self::assertTransactionId($transactionId);

        return $this->transact(
            $uuid,
            $playerName,
            static function(array &$profile) use ($durationMs, $dollarReward, $xpReward, $transactionId) : bool{
                $key = hash("sha256", $transactionId);
                if(isset($profile["processed"][$key])){
                    return false;
                }
                if($profile["dollar"] > PHP_INT_MAX - $dollarReward ||
                    $profile["total_earned"] > PHP_INT_MAX - $dollarReward){
                    throw new RuntimeException("Parkour Dollar reward overflow");
                }

                $profile["processed"][$key] = true;
                $profile["dollar"] += $dollarReward;
                $profile["total_earned"] += $dollarReward;
                $profile["xp"] += $xpReward;
                $profile["level"] = 1 + intdiv($profile["xp"], 100);
                $profile["stats"]["games"]++;
                $profile["stats"]["wins"]++;
                $profile["stats"]["score"] += max(1, 100_000 - $durationMs);
                if($profile["stats"]["best_parkour_ms"] === 0 || $durationMs < $profile["stats"]["best_parkour_ms"]){
                    $profile["stats"]["best_parkour_ms"] = $durationMs;
                }

                $profile["transactions"][] = [
                    "id" => $transactionId,
                    "type" => "parkour_rush",
                    "amount" => $dollarReward,
                    "balance_after" => $profile["dollar"],
                    "created_at" => time()
                ];
                if(count($profile["transactions"]) > 100){
                    $profile["transactions"] = array_slice($profile["transactions"], -100);
                }
                return true;
            }
        );
    }

    /**
     * @template T
     * @param callable(array<string, mixed>&): T $callback
     * @return T
     */
    private function transact(string $uuid, string $playerName, callable $callback) : mixed{
        self::assertUuid($uuid);
        $safeName = substr(preg_replace('/[^A-Za-z0-9_ ]/', '', $playerName) ?? "", 0, 16);
        $lockPath = $this->directory . DIRECTORY_SEPARATOR . $uuid . ".lock";
        $profilePath = $this->directory . DIRECTORY_SEPARATOR . $uuid . ".json";
        $lock = fopen($lockPath, "c");
        if($lock === false){
            throw new RuntimeException("Unable to open player lock");
        }

        try{
            if(!flock($lock, LOCK_EX)){
                throw new RuntimeException("Unable to lock player profile");
            }
            $profile = $this->readProfile($profilePath, $uuid, $safeName);
            $result = $callback($profile);
            $this->writeProfile($profilePath, $profile);
            return $result;
        }finally{
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    /** @return array<string, mixed> */
    private function readProfile(string $path, string $uuid, string $playerName) : array{
        if(!is_file($path)){
            return self::newProfile($uuid, $playerName);
        }
        $raw = file_get_contents($path);
        if($raw === false){
            throw new RuntimeException("Unable to read player profile");
        }
        try{
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        }catch(Throwable $e){
            throw new RuntimeException("Player profile JSON is invalid; data was not overwritten", 0, $e);
        }
        if(!is_array($data) || ($data["uuid"] ?? null) !== $uuid){
            throw new RuntimeException("Player profile identity check failed; data was not overwritten");
        }

        $defaults = self::newProfile($uuid, $playerName);
        $data["name"] = $playerName;
        $data["dollar"] = max(0, (int) ($data["dollar"] ?? 0));
        $data["xp"] = max(0, (int) ($data["xp"] ?? 0));
        $data["level"] = max(1, (int) ($data["level"] ?? 1));
        $data["stats"] = array_merge($defaults["stats"], is_array($data["stats"] ?? null) ? $data["stats"] : []);
        $data["transactions"] = is_array($data["transactions"] ?? null) ? $data["transactions"] : [];
        $data["total_earned"] = max(0, (int) ($data["total_earned"] ?? self::sumLegacyTransactions($data["transactions"], true)));
        $data["total_spent"] = max(0, (int) ($data["total_spent"] ?? self::sumLegacyTransactions($data["transactions"], false)));
        $data["processed"] = is_array($data["processed"] ?? null) ? $data["processed"] : [];
        return $data;
    }

    /**
     * @param array<mixed> $transactions
     */
    private static function sumLegacyTransactions(array $transactions, bool $credits) : int{
        $total = 0;
        foreach($transactions as $transaction){
            if(!is_array($transaction) || !is_int($transaction["amount"] ?? null)){
                continue;
            }
            $amount = $transaction["amount"];
            if(($credits && $amount <= 0) || (!$credits && $amount >= 0)){
                continue;
            }
            if($amount === PHP_INT_MIN){
                return PHP_INT_MAX;
            }
            $value = $credits ? $amount : -$amount;
            if($total > PHP_INT_MAX - $value){
                return PHP_INT_MAX;
            }
            $total += $value;
        }
        return $total;
    }

    /** @param array<string, mixed> $profile */
    private function writeProfile(string $path, array $profile) : void{
        try{
            $json = json_encode($profile, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
        }catch(Throwable $e){
            throw new RuntimeException("Unable to encode player profile", 0, $e);
        }
        $temporary = tempnam($this->directory, ".profile-");
        if($temporary === false){
            throw new RuntimeException("Unable to allocate atomic profile file");
        }
        try{
            if(file_put_contents($temporary, $json) === false){
                throw new RuntimeException("Unable to write player profile");
            }
            @chmod($temporary, 0660);
            if(!rename($temporary, $path)){
                throw new RuntimeException("Unable to replace player profile atomically");
            }
        }finally{
            if(is_file($temporary)){
                @unlink($temporary);
            }
        }
    }

    /** @return array<string, mixed> */
    private static function newProfile(string $uuid, string $name) : array{
        return [
            "uuid" => $uuid,
            "name" => $name,
            "level" => 1,
            "xp" => 0,
            "dollar" => 0,
            "total_earned" => 0,
            "total_spent" => 0,
            "stats" => [
                "games" => 0,
                "wins" => 0,
                "score" => 0,
                "best_parkour_ms" => 0
            ],
            "transactions" => [],
            "processed" => []
        ];
    }

    private static function assertUuid(string $uuid) : void{
        if(!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid)){
            throw new InvalidArgumentException("Invalid player UUID");
        }
    }

    private static function assertTransactionId(string $transactionId) : void{
        if($transactionId === "" || strlen($transactionId) > 128 || !preg_match('/^[A-Za-z0-9_.:-]+$/', $transactionId)){
            throw new InvalidArgumentException("Invalid transaction id");
        }
    }
}
