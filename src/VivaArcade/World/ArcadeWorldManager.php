<?php

declare(strict_types=1);

namespace VivaArcade\World;

use pocketmine\plugin\PluginBase;
use pocketmine\world\World;
use pocketmine\world\WorldCreationOptions;
use pocketmine\world\generator\normal\Normal;
use RuntimeException;

/** Loads the dedicated world; only a newly created or interrupted VivaArcade layout is built. */
final class ArcadeWorldManager{

    public const WORLD_NAME = "VivaArcade";

    private bool $playable = false;

    public function __construct(private readonly PluginBase $plugin){}

    public function load() : World{
        $manager = $this->plugin->getServer()->getWorldManager();
        $newWorld = !$manager->isWorldGenerated(self::WORLD_NAME);

        if($newWorld){
            $options = WorldCreationOptions::create()->setGeneratorClass(Normal::class);
            if(!$manager->generateWorld(self::WORLD_NAME, $options, false)){
                throw new RuntimeException("PocketMine could not generate the VivaArcade world");
            }
        }elseif(!$manager->isWorldLoaded(self::WORLD_NAME)){
            if(!$manager->loadWorld(self::WORLD_NAME)){
                throw new RuntimeException("PocketMine could not load the existing VivaArcade world");
            }
        }

        $world = $manager->getWorldByName(self::WORLD_NAME);
        if(!$world instanceof World){
            throw new RuntimeException("VivaArcade world is not available after load/generation");
        }

        $stateFile = $this->plugin->getDataFolder() . "world-layout-v1.json";
        $state = null;
        if(is_file($stateFile)){
            $raw = file_get_contents($stateFile);
            if(is_string($raw)){
                $decoded = json_decode($raw, true);
                $state = is_array($decoded) ? ($decoded["state"] ?? null) : null;
            }
        }

        if($newWorld || $state === "building"){
            $this->writeState($stateFile, "building");
            (new WorldBuilder())->build($world);
            $this->writeState($stateFile, "complete");
            $this->playable = true;
            $this->plugin->getLogger()->info("Created the initial VivaArcade plaza and Parkour Rush course.");
        }else{
            $this->playable = $state === "complete";
            if(!$this->playable){
                $this->plugin->getLogger()->warning("The existing VivaArcade world was preserved. No untracked blocks were changed; game layout is not marked as installed.");
            }
        }

        return $world;
    }

    public function isPlayable() : bool{
        return $this->playable;
    }

    private function writeState(string $path, string $state) : void{
        $json = json_encode(["version" => 1, "state" => $state, "updated_at" => time()], JSON_THROW_ON_ERROR) . PHP_EOL;
        $temporary = $path . ".tmp";
        if(file_put_contents($temporary, $json, LOCK_EX) === false || !rename($temporary, $path)){
            @unlink($temporary);
            throw new RuntimeException("Unable to save world layout state");
        }
    }
}
