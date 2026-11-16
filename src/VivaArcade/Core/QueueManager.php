<?php

declare(strict_types=1);

namespace VivaArcade\Core;

use InvalidArgumentException;

/**
 * FIFO matchmaking coordinator. A PocketMine adapter is responsible for
 * teleporting players, scheduling countdowns and dispatching game adapters.
 */
final class QueueManager{
    /** @var array<string, array{min:int,max:int}> */
    private array $gameRules = [];
    /** @var array<string, array<string, int>> game id => uuid => joined timestamp */
    private array $queues = [];
    /** @var array<string, string> uuid => game id */
    private array $playerQueue = [];
    /** @var array<string, MatchSession> match id => session */
    private array $matches = [];

    public function registerGame(string $gameId, int $minimumPlayers, int $maximumPlayers) : void{
        if($gameId === '' || strlen($gameId) > 64 || !preg_match('/^[a-zA-Z0-9_.:-]+$/', $gameId) || $minimumPlayers < 1 || $maximumPlayers < $minimumPlayers){
            throw new InvalidArgumentException('Invalid matchmaking game rules');
        }
        $this->gameRules[$gameId] = ['min' => $minimumPlayers, 'max' => $maximumPlayers];
        $this->queues[$gameId] ??= [];
    }

    public function join(string $uuid, string $gameId, ?int $now = null) : bool{
        MatchSession::assertUuid($uuid);
        if(!isset($this->gameRules[$gameId]) || isset($this->playerQueue[$uuid])){ return false; }
        $this->queues[$gameId][$uuid] = $now ?? time();
        $this->playerQueue[$uuid] = $gameId;
        return true;
    }

    public function leave(string $uuid) : bool{
        $gameId = $this->playerQueue[$uuid] ?? null;
        if($gameId === null){ return false; }
        unset($this->queues[$gameId][$uuid], $this->playerQueue[$uuid]);
        return true;
    }

    /** @return list<MatchSession> */
    public function formMatches(ArenaPool $arenas, ?int $now = null) : array{
        $formed = [];
        $timestamp = $now ?? time();
        foreach($this->gameRules as $gameId => $rules){
            while(count($this->queues[$gameId]) >= $rules['min'] && $arenas->availableCount() > 0){
                $candidateUuids = array_slice(array_keys($this->queues[$gameId]), 0, $rules['max']);
                if(count($candidateUuids) < $rules['min']){ break; }
                $matchId = MatchSession::createId();
                $arenaId = $arenas->reserveNext($matchId);
                if($arenaId === null){ break; }
                $session = new MatchSession($matchId, $gameId, $arenaId, $rules['min'], $rules['max'], $timestamp);
                $valid = true;
                foreach($candidateUuids as $uuid){
                    if(!$session->join($uuid, $timestamp)){
                        $valid = false;
                        break;
                    }
                }
                if(!$valid || !$session->beginCountdown()){
                    $arenas->release($arenaId, $matchId);
                    break;
                }
                foreach($candidateUuids as $uuid){
                    unset($this->queues[$gameId][$uuid], $this->playerQueue[$uuid]);
                }
                $this->matches[$matchId] = $session;
                $formed[] = $session;
            }
        }
        return $formed;
    }

    public function startMatch(string $matchId, ?int $now = null) : bool{
        $match = $this->matches[$matchId] ?? null;
        return $match !== null && $match->start($now);
    }

    public function finishMatch(string $matchId, ArenaPool $arenas, ?int $now = null) : bool{
        $match = $this->matches[$matchId] ?? null;
        if($match === null || !$match->finish($now)){ return false; }
        $arenas->release($match->getArenaId(), $matchId);
        return true;
    }

    public function cancelMatch(string $matchId, ArenaPool $arenas) : bool{
        $match = $this->matches[$matchId] ?? null;
        if($match === null || !$match->cancel()){ return false; }
        $arenas->release($match->getArenaId(), $matchId);
        return true;
    }

    public function getMatch(string $matchId) : ?MatchSession{ return $this->matches[$matchId] ?? null; }
    public function getQueuedGame(string $uuid) : ?string{ return $this->playerQueue[$uuid] ?? null; }
    public function getQueueCount(string $gameId) : int{ return count($this->queues[$gameId] ?? []); }
    /** @return array<string, MatchSession> */
    public function getMatches() : array{ return $this->matches; }
}
