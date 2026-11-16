<?php

declare(strict_types=1);

namespace VivaArcade\Lobby;

use pocketmine\player\Player;
use pocketmine\world\Position;
use pocketmine\world\World;

/**
 * Owns the player's return path to the dedicated arcade lobby.
 *
 * Game services should release their own player state before calling teleport().
 */
final class LobbyManager{

    public function __construct(private readonly World $world){}

    public function teleport(Player $player) : bool{
        $spawn = $this->world->getSpawnLocation();
        return $player->teleport(new Position($spawn->x, $spawn->y, $spawn->z, $this->world));
    }

    public function getWorld() : World{
        return $this->world;
    }
}
