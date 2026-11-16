<?php

declare(strict_types=1);

namespace VivaArcade\Party;

/** UUID-based party and expiring invite coordinator. */
final class PartyManager{
    /** @var array<string, Party> party id => party */
    private array $parties = [];
    /** @var array<string, string> member uuid => party id */
    private array $membership = [];
    /** @var array<string, array{partyId:string, expiresAt:int}> invitee uuid => invite */
    private array $invites = [];

    public function __construct(private readonly int $maximumMembers = 8, private readonly int $inviteTtlSeconds = 60){
        if($maximumMembers < 2 || $maximumMembers > 32 || $inviteTtlSeconds < 5 || $inviteTtlSeconds > 3600){ throw new \InvalidArgumentException('Invalid party manager limits'); }
    }

    public function create(string $leaderUuid, ?int $now = null) : ?Party{
        Party::assertUuid($leaderUuid);
        if(isset($this->membership[$leaderUuid])){ return null; }
        $id = bin2hex(random_bytes(12));
        $party = new Party($id, $leaderUuid, $this->maximumMembers, $now);
        $this->parties[$id] = $party;
        $this->membership[$leaderUuid] = $id;
        return $party;
    }

    public function invite(string $leaderUuid, string $targetUuid, ?int $now = null) : bool{
        Party::assertUuid($leaderUuid);
        Party::assertUuid($targetUuid);
        $party = $this->getPartyFor($leaderUuid);
        if($party === null || !$party->isLeader($leaderUuid) || $leaderUuid === $targetUuid || $party->contains($targetUuid) || isset($this->membership[$targetUuid]) || $party->getMemberCount() >= $party->getMaximumMembers()){ return false; }
        $timestamp = $now ?? time();
        $this->invites[$targetUuid] = ['partyId' => $party->getId(), 'expiresAt' => $timestamp + $this->inviteTtlSeconds];
        return true;
    }

    public function accept(string $targetUuid, ?int $now = null) : bool{
        Party::assertUuid($targetUuid);
        if(isset($this->membership[$targetUuid])){ return false; }
        $invite = $this->invites[$targetUuid] ?? null;
        unset($this->invites[$targetUuid]);
        if($invite === null || $invite['expiresAt'] <= ($now ?? time())){ return false; }
        $party = $this->parties[$invite['partyId']] ?? null;
        if($party === null || !$party->addMember($targetUuid, $now)){ return false; }
        $this->membership[$targetUuid] = $party->getId();
        return true;
    }

    public function leave(string $uuid) : bool{
        Party::assertUuid($uuid);
        $partyId = $this->membership[$uuid] ?? null;
        if($partyId === null){ return false; }
        unset($this->membership[$uuid]);
        $party = $this->parties[$partyId] ?? null;
        if($party === null){ return false; }
        $party->removeMember($uuid);
        if($party->isEmpty()){ unset($this->parties[$partyId]); }
        return true;
    }

    /** Transfer leadership while retaining the party membership indexes. */
    public function transferLeadership(string $leaderUuid, string $targetUuid) : bool{
        Party::assertUuid($leaderUuid);
        Party::assertUuid($targetUuid);
        $party = $this->getPartyFor($leaderUuid);
        if($party === null || !$party->isLeader($leaderUuid) || $leaderUuid === $targetUuid || !$party->contains($targetUuid)){
            return false;
        }
        return $party->transferLeadership($leaderUuid, $targetUuid);
    }

    public function kick(string $leaderUuid, string $targetUuid) : bool{
        Party::assertUuid($leaderUuid);
        Party::assertUuid($targetUuid);
        $party = $this->getPartyFor($leaderUuid);
        if($party === null || !$party->isLeader($leaderUuid) || $leaderUuid === $targetUuid || !$party->contains($targetUuid)){ return false; }
        unset($this->membership[$targetUuid]);
        return $party->removeMember($targetUuid);
    }

    public function getPartyFor(string $uuid) : ?Party{
        $partyId = $this->membership[$uuid] ?? null;
        return $partyId === null ? null : ($this->parties[$partyId] ?? null);
    }

    public function declineInvite(string $targetUuid) : bool{
        Party::assertUuid($targetUuid);
        if(!isset($this->invites[$targetUuid])){ return false; }
        unset($this->invites[$targetUuid]);
        return true;
    }

    public function cancelInvite(string $leaderUuid, string $targetUuid) : bool{
        Party::assertUuid($leaderUuid);
        Party::assertUuid($targetUuid);
        $party = $this->getPartyFor($leaderUuid);
        $invite = $this->invites[$targetUuid] ?? null;
        if($party === null || !$party->isLeader($leaderUuid) || $invite === null || $invite['partyId'] !== $party->getId()){ return false; }
        unset($this->invites[$targetUuid]);
        return true;
    }

    public function getPendingInviteParty(string $targetUuid, ?int $now = null) : ?Party{
        Party::assertUuid($targetUuid);
        $invite = $this->invites[$targetUuid] ?? null;
        if($invite === null || $invite['expiresAt'] <= ($now ?? time())){ return null; }
        return $this->parties[$invite['partyId']] ?? null;
    }

    public function getInviteExpiry(string $uuid) : ?int{ return $this->invites[$uuid]['expiresAt'] ?? null; }
    public function getPartyCount() : int{ return count($this->parties); }
    public function cleanupExpiredInvites(?int $now = null) : int{
        $timestamp = $now ?? time();
        $removed = 0;
        foreach($this->invites as $uuid => $invite){
            if($invite['expiresAt'] <= $timestamp){ unset($this->invites[$uuid]); ++$removed; }
        }
        return $removed;
    }

    /** @return array<string, Party> */
    public function getParties() : array{ return $this->parties; }
}
