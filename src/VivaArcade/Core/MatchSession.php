<?php

declare(strict_types=1);

namespace VivaArcade\Core;

use InvalidArgumentException;

/** A server-authoritative, arena-isolated match state machine. */
final class MatchSession{
    public const WAITING = 'waiting';
    public const COUNTDOWN = 'countdown';
    public const RUNNING = 'running';
    public const FINISHED = 'finished';
    public const CANCELLED = 'cancelled';

    /** @var array<string, array{joinedAt:int, connected:bool, eliminated:bool}> */
    private array $participants = [];
    /** @var array<string, int> */
    private array $scores = [];
    /** @var array<string, true> */
    private array $rewardClaims = [];
    private string $state = self::WAITING;
    private ?int $startedAt = null;
    private ?int $finishedAt = null;

    public function __construct(
        private readonly string $id,
        private readonly string $gameId,
        private readonly string $arenaId,
        private readonly int $minimumPlayers,
        private readonly int $maximumPlayers,
        private readonly int $createdAt = 0
    ){
        self::assertToken($id, 'match id');
        self::assertToken($gameId, 'game id');
        self::assertToken($arenaId, 'arena id');
        if($minimumPlayers < 1 || $maximumPlayers < $minimumPlayers){
            throw new InvalidArgumentException('Invalid player limits');
        }
    }

    public static function createId() : string{
        return bin2hex(random_bytes(16));
    }

    public static function assertUuid(string $uuid) : void{
        if(!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid)){
            throw new InvalidArgumentException('Invalid player UUID');
        }
    }

    private static function assertToken(string $value, string $field) : void{
        if($value === '' || strlen($value) > 64 || !preg_match('/^[a-zA-Z0-9_.:-]+$/', $value)){
            throw new InvalidArgumentException('Invalid ' . $field);
        }
    }

    public function join(string $uuid, ?int $now = null) : bool{
        self::assertUuid($uuid);
        if($this->state !== self::WAITING || isset($this->participants[$uuid]) || count($this->participants) >= $this->maximumPlayers){
            return false;
        }
        $this->participants[$uuid] = ['joinedAt' => $now ?? time(), 'connected' => true, 'eliminated' => false];
        $this->scores[$uuid] = 0;
        return true;
    }

    public function beginCountdown() : bool{
        if($this->state !== self::WAITING || $this->getConnectedPlayerCount() < $this->minimumPlayers){ return false; }
        $this->state = self::COUNTDOWN;
        return true;
    }

    public function start(?int $now = null) : bool{
        if($this->state !== self::COUNTDOWN || $this->getConnectedPlayerCount() < $this->minimumPlayers){ return false; }
        $this->state = self::RUNNING;
        $this->startedAt = $now ?? time();
        return true;
    }

    public function addScore(string $uuid, int $amount) : bool{
        if($this->state !== self::RUNNING || $amount < 0 || !isset($this->participants[$uuid]) || !$this->participants[$uuid]['connected'] || $this->participants[$uuid]['eliminated']){ return false; }
        $sum = $this->scores[$uuid] + $amount;
        $this->scores[$uuid] = is_int($sum) ? $sum : PHP_INT_MAX;
        return true;
    }

    public function setScore(string $uuid, int $score) : bool{
        if($this->state !== self::RUNNING || $score < 0 || !isset($this->participants[$uuid]) || !$this->participants[$uuid]['connected'] || $this->participants[$uuid]['eliminated']){ return false; }
        $this->scores[$uuid] = $score;
        return true;
    }

    public function eliminate(string $uuid) : bool{
        if($this->state !== self::RUNNING || !isset($this->participants[$uuid]) || $this->participants[$uuid]['eliminated']){ return false; }
        $this->participants[$uuid]['eliminated'] = true;
        return true;
    }

    public function disconnect(string $uuid) : bool{
        if(!isset($this->participants[$uuid]) || !$this->participants[$uuid]['connected'] || in_array($this->state, [self::FINISHED, self::CANCELLED], true)){ return false; }
        $this->participants[$uuid]['connected'] = false;
        return true;
    }

    public function reconnect(string $uuid) : bool{
        if(!in_array($this->state, [self::COUNTDOWN, self::RUNNING], true) || !isset($this->participants[$uuid]) || $this->participants[$uuid]['connected'] || $this->participants[$uuid]['eliminated']){ return false; }
        $this->participants[$uuid]['connected'] = true;
        return true;
    }

    public function finish(?int $now = null) : bool{
        if(!in_array($this->state, [self::RUNNING, self::COUNTDOWN], true)){ return false; }
        $this->state = self::FINISHED;
        $this->finishedAt = $now ?? time();
        return true;
    }

    public function cancel() : bool{
        if(in_array($this->state, [self::FINISHED, self::CANCELLED], true)){ return false; }
        $this->state = self::CANCELLED;
        return true;
    }

    public function claimReward(string $uuid, string $rewardKey) : bool{
        if($this->state !== self::FINISHED || !isset($this->participants[$uuid]) || $rewardKey === '' || strlen($rewardKey) > 80){ return false; }
        $claim = $uuid . ':' . $rewardKey;
        if(isset($this->rewardClaims[$claim])){ return false; }
        $this->rewardClaims[$claim] = true;
        return true;
    }

    public function getId() : string{ return $this->id; }
    public function getGameId() : string{ return $this->gameId; }
    public function getArenaId() : string{ return $this->arenaId; }
    public function getState() : string{ return $this->state; }
    public function getStartedAt() : ?int{ return $this->startedAt; }
    public function getFinishedAt() : ?int{ return $this->finishedAt; }
    public function getMinimumPlayers() : int{ return $this->minimumPlayers; }
    public function getMaximumPlayers() : int{ return $this->maximumPlayers; }
    public function getCreatedAt() : int{ return $this->createdAt; }
    public function getPlayerCount() : int{ return count($this->participants); }
    public function getConnectedPlayerCount() : int{
        $count = 0;
        foreach($this->participants as $participant){
            if($participant['connected'] && !$participant['eliminated']){ ++$count; }
        }
        return $count;
    }
    public function isParticipant(string $uuid) : bool{ return isset($this->participants[$uuid]); }
    public function isConnected(string $uuid) : bool{ return ($this->participants[$uuid]['connected'] ?? false) === true; }
    /** @return list<string> */
    public function getParticipantUuids() : array{ return array_keys($this->participants); }
    /** @return array<string, int> */
    public function getScores() : array{ return $this->scores; }
    /** @return array<string, array{joinedAt:int, connected:bool, eliminated:bool}> */
    public function getParticipants() : array{ return $this->participants; }

    public function getDuration(?int $now = null) : int{
        if($this->startedAt === null){ return 0; }
        return max(0, ($this->finishedAt ?? ($now ?? time())) - $this->startedAt);
    }
}
