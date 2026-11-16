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

    /**
     * Export a versioned, JSON-safe snapshot for a trusted storage adapter.
     * This does not persist data by itself.
     *
     * @return array<string, mixed>
     */
    public function toArray() : array{
        return [
            'schemaVersion' => 1,
            'id' => $this->id,
            'gameId' => $this->gameId,
            'arenaId' => $this->arenaId,
            'minimumPlayers' => $this->minimumPlayers,
            'maximumPlayers' => $this->maximumPlayers,
            'createdAt' => $this->createdAt,
            'state' => $this->state,
            'startedAt' => $this->startedAt,
            'finishedAt' => $this->finishedAt,
            'participants' => $this->participants,
            'scores' => $this->scores,
            'rewardClaims' => array_keys($this->rewardClaims)
        ];
    }

    /**
     * Restore a snapshot after validating every field. Never feed client data here.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data) : self{
        if(($data['schemaVersion'] ?? null) !== 1){
            throw new InvalidArgumentException('Unsupported match snapshot schema');
        }

        foreach(['id', 'gameId', 'arenaId', 'minimumPlayers', 'maximumPlayers', 'createdAt', 'state', 'startedAt', 'finishedAt', 'participants', 'scores', 'rewardClaims'] as $key){
            if(!array_key_exists($key, $data)){
                throw new InvalidArgumentException('Missing match snapshot field: ' . $key);
            }
        }

        if(!is_string($data['id']) || !is_string($data['gameId']) || !is_string($data['arenaId']) ||
            !is_int($data['minimumPlayers']) || !is_int($data['maximumPlayers']) || !is_int($data['createdAt']) ||
            !is_string($data['state']) || (!is_int($data['startedAt']) && $data['startedAt'] !== null) ||
            (!is_int($data['finishedAt']) && $data['finishedAt'] !== null) ||
            !is_array($data['participants']) || !is_array($data['scores']) || !is_array($data['rewardClaims']) ||
            !array_is_list($data['rewardClaims'])){
            throw new InvalidArgumentException('Invalid match snapshot field types');
        }

        $session = new self(
            $data['id'],
            $data['gameId'],
            $data['arenaId'],
            $data['minimumPlayers'],
            $data['maximumPlayers'],
            $data['createdAt']
        );

        $validStates = [self::WAITING, self::COUNTDOWN, self::RUNNING, self::FINISHED, self::CANCELLED];
        if(!in_array($data['state'], $validStates, true)){
            throw new InvalidArgumentException('Invalid match snapshot state');
        }
        if(in_array($data['state'], [self::WAITING, self::COUNTDOWN], true) &&
            ($data['startedAt'] !== null || $data['finishedAt'] !== null)){
            throw new InvalidArgumentException('Invalid timestamps for pending match');
        }
        if($data['state'] === self::RUNNING && ($data['startedAt'] === null || $data['finishedAt'] !== null)){
            throw new InvalidArgumentException('Invalid timestamps for running match');
        }
        if($data['state'] === self::FINISHED && $data['finishedAt'] === null){
            throw new InvalidArgumentException('Finished match requires a finish timestamp');
        }
        if($data['state'] === self::CANCELLED && $data['finishedAt'] !== null){
            throw new InvalidArgumentException('Cancelled match cannot have a finish timestamp');
        }
        if($data['startedAt'] !== null && $data['finishedAt'] !== null && $data['finishedAt'] < $data['startedAt']){
            throw new InvalidArgumentException('Match finish timestamp precedes its start');
        }

        $participants = [];
        foreach($data['participants'] as $uuid => $participant){
            if(!is_string($uuid) || !is_array($participant) ||
                !array_key_exists('joinedAt', $participant) || !is_int($participant['joinedAt']) ||
                !array_key_exists('connected', $participant) || !is_bool($participant['connected']) ||
                !array_key_exists('eliminated', $participant) || !is_bool($participant['eliminated'])){
                throw new InvalidArgumentException('Invalid match participant snapshot');
            }
            self::assertUuid($uuid);
            $participants[$uuid] = [
                'joinedAt' => $participant['joinedAt'],
                'connected' => $participant['connected'],
                'eliminated' => $participant['eliminated']
            ];
        }
        if(count($participants) > $session->maximumPlayers){
            throw new InvalidArgumentException('Match snapshot exceeds maximum player count');
        }
        if(in_array($data['state'], [self::COUNTDOWN, self::RUNNING, self::FINISHED], true) &&
            count($participants) < $session->minimumPlayers){
            throw new InvalidArgumentException('Match snapshot has fewer participants than its minimum');
        }
        if($data['state'] === self::CANCELLED && $data['startedAt'] !== null &&
            count($participants) < $session->minimumPlayers){
            throw new InvalidArgumentException('Started cancelled match has fewer participants than its minimum');
        }

        $scores = [];
        foreach($data['scores'] as $uuid => $score){
            if(!is_string($uuid) || !is_int($score) || $score < 0 || !isset($participants[$uuid])){
                throw new InvalidArgumentException('Invalid match score snapshot');
            }
            $scores[$uuid] = $score;
        }
        if(count($scores) !== count($participants)){
            throw new InvalidArgumentException('Match snapshot participant and score indexes differ');
        }

        $rewardClaims = [];
        foreach($data['rewardClaims'] as $claim){
            if(!is_string($claim)){
                throw new InvalidArgumentException('Invalid reward claim snapshot');
            }
            $separator = strpos($claim, ':');
            if($separator === false){
                throw new InvalidArgumentException('Invalid reward claim key');
            }
            $uuid = substr($claim, 0, $separator);
            $rewardKey = substr($claim, $separator + 1);
            self::assertUuid($uuid);
            if(!isset($participants[$uuid]) || $rewardKey === '' || strlen($rewardKey) > 80 || isset($rewardClaims[$claim])){
                throw new InvalidArgumentException('Invalid or duplicate reward claim snapshot');
            }
            $rewardClaims[$claim] = true;
        }

        if($data['state'] !== self::FINISHED && $rewardClaims !== []){
            throw new InvalidArgumentException('Only a finished match may contain reward claims');
        }

        $session->state = $data['state'];
        $session->startedAt = $data['startedAt'];
        $session->finishedAt = $data['finishedAt'];
        $session->participants = $participants;
        $session->scores = $scores;
        $session->rewardClaims = $rewardClaims;
        return $session;
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
