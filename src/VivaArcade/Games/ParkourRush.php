<?php

declare(strict_types=1);

namespace VivaArcade\Games;

use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\player\Player;
use pocketmine\world\Position;
use pocketmine\world\World;
use VivaArcade\Storage\JsonPlayerStorage;

/** Solo Parkour Rush with a server-side timer, checkpoint, finish and idempotent reward. */
final class ParkourRush{

    /** @var array<string, array{started:float, checkpoint:bool, id:string}> */
    private array $sessions = [];

    public function __construct(
        private readonly World $world,
        private readonly JsonPlayerStorage $storage,
        private readonly int $rewardDollar = 50,
        private readonly int $rewardXp = 30
    ){}

    public function start(Player $player) : bool{
        $uuid = $player->getUniqueId()->toString();
        if(isset($this->sessions[$uuid])){
            return false;
        }
        $this->sessions[$uuid] = [
            "started" => microtime(true),
            "checkpoint" => false,
            "id" => bin2hex(random_bytes(16))
        ];
        $player->teleport(new Position(100.5, 101, 0.5, $this->world));
        $player->sendMessage("Parkour Rush started! Reach the diamond platform. Checkpoint is gold.");
        $player->sendActionBarMessage("Timer started!");
        return true;
    }

    public function handleMove(PlayerMoveEvent $event) : void{
        $player = $event->getPlayer();
        $uuid = $player->getUniqueId()->toString();
        if(!isset($this->sessions[$uuid])){
            return;
        }
        $to = $event->getTo();
        if($to->getWorld() !== $this->world){
            unset($this->sessions[$uuid]);
            return;
        }

        if($to->y < 85){
            $this->respawnAtCheckpoint($player, $uuid);
            return;
        }

        if(!$this->sessions[$uuid]["checkpoint"] && $to->x >= 115.0){
            $this->sessions[$uuid]["checkpoint"] = true;
            $player->sendMessage("Checkpoint reached!");
            $player->sendActionBarMessage("Checkpoint saved!");
        }

        if($to->x >= 131.0 && $to->y >= 105.0){
            $this->finish($player, $uuid);
        }
    }

    public function leave(Player $player, bool $teleport = true) : bool{
        $uuid = $player->getUniqueId()->toString();
        if(!isset($this->sessions[$uuid])){
            return false;
        }
        unset($this->sessions[$uuid]);
        if($teleport){
            $this->sendToSpawn($player);
        }
        $player->sendMessage("You left Parkour Rush.");
        return true;
    }

    public function forget(string $uuid) : void{
        unset($this->sessions[$uuid]);
    }

    private function respawnAtCheckpoint(Player $player, string $uuid) : void{
        if($this->sessions[$uuid]["checkpoint"]){
            $player->teleport(new Position(116.5, 103, 0.5, $this->world));
        }else{
            $player->teleport(new Position(100.5, 101, 0.5, $this->world));
        }
        $player->sendActionBarMessage("Try again!");
    }

    private function finish(Player $player, string $uuid) : void{
        $session = $this->sessions[$uuid];
        unset($this->sessions[$uuid]);

        $durationMs = max(1, (int) round((microtime(true) - $session["started"]) * 1000));
        $reward = max(10, $this->rewardDollar - intdiv($durationMs, 10_000));
        try{
            $credited = $this->storage->completeParkour(
                $uuid,
                $player->getName(),
                $durationMs,
                $reward,
                $this->rewardXp,
                "parkour-rush:" . $session["id"]
            );
        }catch(\Throwable $e){
            $player->sendMessage("Your result could not be saved. Please contact an admin.");
            $this->sendToSpawn($player);
            return;
        }

        if($credited){
            $player->sendMessage("Game Completed! Time: " . number_format($durationMs / 1000, 2) . "s");
            $player->sendMessage("Reward: $" . $reward . " and " . $this->rewardXp . " XP.");
            $player->sendActionBarMessage("New Record attempt saved!");
        }else{
            $player->sendMessage("This game result was already processed.");
        }
        $this->sendToSpawn($player);
    }

    private function sendToSpawn(Player $player) : void{
        $spawn = $this->world->getSpawnLocation();
        $player->teleport(new Position($spawn->x, $spawn->y, $spawn->z, $this->world));
    }
}
