<?php

declare(strict_types=1);

namespace VivaArcade\Party;

use InvalidArgumentException;

final class Party{
    /** @var array<string, int> uuid => joined timestamp */
    private array $members = [];

    public function __construct(
        private readonly string $id,
        private string $leaderUuid,
        private readonly int $maximumMembers = 8,
        ?int $leaderJoinedAt = null
    ){
        if($id === '' || $maximumMembers < 2){ throw new InvalidArgumentException('Invalid party configuration'); }
        self::assertUuid($leaderUuid);
        $this->members[$leaderUuid] = $leaderJoinedAt ?? time();
    }

    public static function assertUuid(string $uuid) : void{
        if(!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid)){
            throw new InvalidArgumentException('Invalid player UUID');
        }
    }

    public function addMember(string $uuid, ?int $now = null) : bool{
        self::assertUuid($uuid);
        if(isset($this->members[$uuid]) || count($this->members) >= $this->maximumMembers){ return false; }
        $this->members[$uuid] = $now ?? time();
        return true;
    }

    public function removeMember(string $uuid) : bool{
        if(!isset($this->members[$uuid])){ return false; }
        unset($this->members[$uuid]);
        if($uuid === $this->leaderUuid && $this->members !== []){ $this->leaderUuid = (string) array_key_first($this->members); }
        return true;
    }

    public function contains(string $uuid) : bool{ return isset($this->members[$uuid]); }
    public function isLeader(string $uuid) : bool{ return $this->leaderUuid === $uuid; }

    /** Only the current leader may promote an existing member. */
    public function transferLeadership(string $currentLeaderUuid, string $newLeaderUuid) : bool{
        if(!$this->isLeader($currentLeaderUuid) || $currentLeaderUuid === $newLeaderUuid || !$this->contains($newLeaderUuid)){
            return false;
        }
        $this->leaderUuid = $newLeaderUuid;
        return true;
    }
    public function getId() : string{ return $this->id; }
    public function getLeaderUuid() : string{ return $this->leaderUuid; }
    public function getMemberCount() : int{ return count($this->members); }
    public function getMaximumMembers() : int{ return $this->maximumMembers; }
    /** @return list<string> */
    public function getMembers() : array{ return array_keys($this->members); }
    public function isEmpty() : bool{ return $this->members === []; }
}
