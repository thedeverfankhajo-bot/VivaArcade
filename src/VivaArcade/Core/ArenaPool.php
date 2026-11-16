<?php

declare(strict_types=1);

namespace VivaArcade\Core;

use InvalidArgumentException;

/** In-memory arena ownership table. An arena can belong to at most one match. */
final class ArenaPool{
    /** @var array<string, true> */
    private array $arenas = [];
    /** @var array<string, string> arena id => match id */
    private array $reservations = [];

    public function register(string $arenaId) : void{
        if($arenaId === '' || strlen($arenaId) > 64 || !preg_match('/^[a-zA-Z0-9_.:-]+$/', $arenaId)){
            throw new InvalidArgumentException('Invalid arena id');
        }
        $this->arenas[$arenaId] = true;
    }

    public function unregister(string $arenaId) : bool{
        if(isset($this->reservations[$arenaId])){ return false; }
        unset($this->arenas[$arenaId]);
        return true;
    }

    public function reserveNext(string $matchId) : ?string{
        if($matchId === '' || strlen($matchId) > 64){
            throw new InvalidArgumentException('Invalid match id');
        }
        $ids = array_keys($this->arenas);
        sort($ids, SORT_STRING);
        foreach($ids as $arenaId){
            if(!isset($this->reservations[$arenaId])){
                $this->reservations[$arenaId] = $matchId;
                return $arenaId;
            }
        }
        return null;
    }

    public function reserve(string $arenaId, string $matchId) : bool{
        if(!isset($this->arenas[$arenaId]) || isset($this->reservations[$arenaId])){ return false; }
        $this->reservations[$arenaId] = $matchId;
        return true;
    }

    /** An old match cannot release an arena that has already been reassigned. */
    public function release(string $arenaId, string $matchId) : bool{
        if(($this->reservations[$arenaId] ?? null) !== $matchId){ return false; }
        unset($this->reservations[$arenaId]);
        return true;
    }

    public function ownerOf(string $arenaId) : ?string{ return $this->reservations[$arenaId] ?? null; }
    public function availableCount() : int{ return count($this->arenas) - count($this->reservations); }
    /** @return list<string> */
    public function getArenaIds() : array{ return array_keys($this->arenas); }
}
