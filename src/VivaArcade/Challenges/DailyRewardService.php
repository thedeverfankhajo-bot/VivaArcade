<?php

declare(strict_types=1);

namespace VivaArcade\Challenges;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use VivaArcade\Economy\WalletService;
use VivaArcade\Storage\JsonPlayerStorage;

/** Once-per-UTC-day rewards. The wallet adapter owns the atomic claim transaction. */
final class DailyRewardService{

    private readonly DateTimeZone $timezone;

    public function __construct(
        private readonly JsonPlayerStorage $storage,
        private readonly WalletService $wallet,
        private readonly int $dollarReward = 100,
        private readonly int $xpReward = 25
    ){
        if($dollarReward < 1 || $dollarReward > 100_000 || $xpReward < 0 || $xpReward > 100_000){
            throw new InvalidArgumentException('Invalid daily reward configuration');
        }
        $this->timezone = new DateTimeZone('UTC');
    }

    /** @return array{date:string,claimed:bool,dollar:int,xp:int} */
    public function getStatus(string $uuid, string $playerName, ?DateTimeImmutable $now = null) : array{
        $date = ($now ?? new DateTimeImmutable('now', $this->timezone))
            ->setTimezone($this->timezone)->format('Y-m-d');
        $profile = $this->storage->getProfile($uuid, $playerName);
        return [
            'date' => $date,
            'claimed' => ($profile['daily_claimed_on'] ?? null) === $date,
            'dollar' => $this->dollarReward,
            'xp' => $this->xpReward
        ];
    }

    /** Uses a server-derived UTC date; form/client data never selects the reward. */
    public function claim(string $uuid, string $playerName, ?DateTimeImmutable $now = null) : bool{
        $date = ($now ?? new DateTimeImmutable('now', $this->timezone))
            ->setTimezone($this->timezone)->format('Y-m-d');
        return $this->wallet->claimDailyReward(
            $uuid, $playerName, $date, $this->dollarReward, $this->xpReward
        );
    }
}
