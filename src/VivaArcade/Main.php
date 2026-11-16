<?php

declare(strict_types=1);

namespace VivaArcade;

use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\world\Position;
use pocketmine\world\World;
use VivaArcade\Forms\ArcadeForm;
use VivaArcade\Games\ParkourRush;
use VivaArcade\Storage\JsonPlayerStorage;
use VivaArcade\World\ArcadeWorldManager;
use Throwable;

final class Main extends PluginBase implements Listener{

    private ?World $arcadeWorld = null;
    private ?JsonPlayerStorage $storage = null;
    private ?ParkourRush $parkour = null;
    private bool $worldPlayable = false;

    /** @var array<string, float> */
    private array $lastFormAt = [];

    protected function onEnable() : void{
        try{
            $this->saveDefaultConfig();
            $this->storage = new JsonPlayerStorage($this->getDataFolder() . "players");
            $worldManager = new ArcadeWorldManager($this);
            $this->arcadeWorld = $worldManager->load();
            $this->worldPlayable = $worldManager->isPlayable();

            $parkourConfig = $this->getConfig()->get("parkour", []);
            if(!is_array($parkourConfig)){
                $parkourConfig = [];
            }
            $this->parkour = new ParkourRush(
                $this->arcadeWorld,
                $this->storage,
                max(1, min(1000, (int) ($parkourConfig["reward-dollar"] ?? 50))),
                max(1, min(500, (int) ($parkourConfig["reward-xp"] ?? 30)))
            );

            $this->getServer()->getPluginManager()->registerEvents($this, $this);
            $status = $this->worldPlayable ? "World layout ready." : "Existing world preserved; layout setup is required before games can start.";
            $this->getLogger()->info("VivaArcade enabled. " . $status);
        }catch(Throwable $e){
            $this->getLogger()->critical("VivaArcade could not initialize its dedicated world.");
            $this->getLogger()->logException($e);
            $this->getServer()->getPluginManager()->disablePlugin($this);
        }
    }

    protected function onDisable() : void{
        if($this->parkour !== null && $this->arcadeWorld !== null){
            foreach($this->getServer()->getOnlinePlayers() as $player){
                $this->parkour->leave($player, true);
            }
        }
        $this->lastFormAt = [];
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
        if(!$sender instanceof Player){
            $sender->sendMessage("In dastoor faghat dar dakhele bazi ghabel ejra ast.");
            return true;
        }
        if(!$sender->hasPermission("vivaarcade.command")){
            $sender->sendMessage("Shoma ejaze estefade az VivaArcade ra nadarid.");
            return true;
        }

        switch(strtolower($command->getName())){
            case "arcade":
                $this->openMainMenu($sender);
                return true;
            case "games":
                $this->openGamesMenu($sender);
                return true;
            case "profile":
                $this->showProfile($sender);
                return true;
            case "stats":
                $this->showStatistics($sender);
                return true;
            case "balance":
                $this->showBalance($sender);
                return true;
            case "leave":
                if($this->parkour?->leave($sender, true) !== true){
                    $sender->sendMessage("Shoma dar hich bazi faali nistid.");
                }
                return true;
        }
        return false;
    }

    public function onJoin(PlayerJoinEvent $event) : void{
        $player = $event->getPlayer();
        if($this->arcadeWorld !== null){
            $spawn = $this->arcadeWorld->getSpawnLocation();
            $player->teleport(new Position($spawn->x, $spawn->y, $spawn->z, $this->arcadeWorld));
        }
        $player->sendMessage("Be VivaArcade khosh amadid! Baraye didane menu /arcade ra bezanid.");
    }

    public function onMove(PlayerMoveEvent $event) : void{
        $this->parkour?->handleMove($event);
    }

    public function onQuit(PlayerQuitEvent $event) : void{
        $uuid = $event->getPlayer()->getUniqueId()->toString();
        $this->parkour?->forget($uuid);
        unset($this->lastFormAt[$uuid]);
    }

    public function onBlockBreak(BlockBreakEvent $event) : void{
        if($this->arcadeWorld !== null && $event->getPlayer()->getWorld() === $this->arcadeWorld){
            $event->cancel();
            $event->getPlayer()->sendActionBarMessage("Sakht-o-saz dar donyaye VivaArcade ghayre faal ast.");
        }
    }

    public function onBlockPlace(BlockPlaceEvent $event) : void{
        if($this->arcadeWorld !== null && $event->getPlayer()->getWorld() === $this->arcadeWorld){
            $event->cancel();
            $event->getPlayer()->sendActionBarMessage("Sakht-o-saz dar donyaye VivaArcade ghayre faal ast.");
        }
    }

    private function openMainMenu(Player $player) : void{
        $this->sendForm($player, new ArcadeForm(
            "VivaArcade",
            "Be VivaArcade khosh amadid!\nBebin. Bazi kon. Challenge bede. Be bord beres!",
            ["Bazi", "Profile", "Amar", "Bastan"],
            function(Player $target, int $choice) : void{
                if($choice === 0){
                    $this->openGamesMenu($target);
                }elseif($choice === 1){
                    $this->showProfile($target);
                }elseif($choice === 2){
                    $this->showStatistics($target);
                }
            }
        ));
    }

    private function openGamesMenu(Player $player) : void{
        if(!$this->worldPlayable){
            $player->sendMessage("Game arenas are not ready for this existing world.");
            return;
        }
        $this->sendForm($player, new ArcadeForm(
            "Entekhab Bazi",
            "Mode mored nazar ra baraye bazi entekhab kon.",
            ["Parkour Rush", "Bargasht", "Bastan"],
            function(Player $target, int $choice) : void{
                if($choice === 0){
                    if(!$this->parkour?->start($target)){
                        $target->sendMessage("Shoma alan dar yek bazi hastid.");
                    }
                }elseif($choice === 1){
                    $this->openMainMenu($target);
                }
            }
        ));
    }

    private function showProfile(Player $player) : void{
        $profile = $this->storage?->getProfile($player->getUniqueId()->toString(), $player->getName());
        if($profile === null){
            $player->sendMessage("Profile dar hale hazer dar dastres nist.");
            return;
        }
        $stats = $profile["stats"];
        $best = $stats["best_parkour_ms"] > 0 ? number_format($stats["best_parkour_ms"] / 1000, 2) . "s" : "Sabt nashode";
        $content = "Bazikon: " . $player->getName()
            . "\nSath: " . $profile["level"]
            . "\nXP: " . $profile["xp"]
            . "\nMojoodi Dollar: $" . $profile["dollar"]
            . "\nTedad Bazi: " . $stats["games"]
            . "\nTedad Barande: " . $stats["wins"]
            . "\nBehtarin Zamane Parkour: " . $best;

        $this->sendForm($player, new ArcadeForm(
            "Profile Shoma",
            $content,
            ["Bazi", "Bargasht", "Bastan"],
            function(Player $target, int $choice) : void{
                if($choice === 0){
                    $this->openGamesMenu($target);
                }elseif($choice === 1){
                    $this->openMainMenu($target);
                }
            }
        ));
    }

    private function showStatistics(Player $player) : void{
        $profile = $this->storage?->getProfile($player->getUniqueId()->toString(), $player->getName());
        if($profile === null){
            $player->sendMessage("Amar dar hale hazer dar dastres nist.");
            return;
        }
        $stats = $profile["stats"];
        $player->sendMessage("Amar Shoma");
        $player->sendMessage("Bazi ha: " . $stats["games"] . " | Barande ha: " . $stats["wins"] . " | XP: " . $profile["xp"]);
        $player->sendMessage("Behtarin Zamane Parkour: " . ($stats["best_parkour_ms"] > 0 ? number_format($stats["best_parkour_ms"] / 1000, 2) . "s" : "Sabt nashode"));
    }

    private function showBalance(Player $player) : void{
        $profile = $this->storage?->getProfile($player->getUniqueId()->toString(), $player->getName());
        if($profile === null){
            $player->sendMessage("Mojoodi dar hale hazer dar dastres nist.");
            return;
        }
        $player->sendMessage("Mojoodi Shoma: $" . $profile["dollar"]);
    }

    private function sendForm(Player $player, ArcadeForm $form) : void{
        $uuid = $player->getUniqueId()->toString();
        $now = microtime(true);
        if(isset($this->lastFormAt[$uuid]) && ($now - $this->lastFormAt[$uuid]) < 0.35){
            $player->sendActionBarMessage("Lotfan baraye baz kardan menu kami sabr konid.");
            return;
        }
        $this->lastFormAt[$uuid] = $now;
        $player->sendForm($form);
    }
}
