<?php

declare(strict_types=1);

namespace VivaArcade\World;

use pocketmine\plugin\PluginBase;
use pocketmine\world\World;
use pocketmine\world\WorldCreationOptions;
use pocketmine\world\generator\normal\Normal;
use RuntimeException;
use Throwable;

/**
 * Loads the dedicated world and schedules a resumable, tick-budgeted layout.
 * Existing completed maps are deliberately preserved until an explicit migration.
 */
final class ArcadeWorldManager{

    public const WORLD_NAME = 'VivaArcade';
    private const LAYOUT_VERSION = 3;

    private bool $playable = false;

    public function __construct(private readonly PluginBase $plugin){}

    /**
     * @param callable(): void $onPlayable
     */
    public function load(callable $onPlayable) : World{
        $manager = $this->plugin->getServer()->getWorldManager();
        $newWorld = !$manager->isWorldGenerated(self::WORLD_NAME);

        if($newWorld){
            $options = WorldCreationOptions::create()->setGeneratorClass(Normal::class);
            if(!$manager->generateWorld(self::WORLD_NAME, $options, false)){
                throw new RuntimeException('PocketMine could not generate the VivaArcade world');
            }
        }elseif(!$manager->isWorldLoaded(self::WORLD_NAME)){
            if(!$manager->loadWorld(self::WORLD_NAME)){
                throw new RuntimeException('PocketMine could not load the existing VivaArcade world');
            }
        }

        $world = $manager->getWorldByName(self::WORLD_NAME);
        if(!$world instanceof World){
            throw new RuntimeException('VivaArcade world is not available after load/generation');
        }

        $stateFile = $this->plugin->getDataFolder() . 'world-layout-v1.json';
        $state = null;
        $version = 0;
        if(is_file($stateFile)){
            $raw = file_get_contents($stateFile);
            if(is_string($raw)){
                try{
                    $decoded = json_decode($raw, true, 16, JSON_THROW_ON_ERROR);
                    if(is_array($decoded)){
                        $state = is_string($decoded['state'] ?? null) ? $decoded['state'] : null;
                        $version = is_int($decoded['version'] ?? null) ? $decoded['version'] : 0;
                    }
                }catch(\JsonException){
                    $this->plugin->getLogger()->warning('World layout marker is invalid; existing world blocks will be preserved.');
                }
            }
        }

        // "building" is only written by our own builder. Replaying the
        // deterministic operation stream is safe even for an older interrupted
        // layout because it edits only the reserved VivaArcade coordinates.
        $needsBuild = $newWorld || $state === 'building';
        if($needsBuild){
            $this->writeState($stateFile, 'building');
            $builder = new WorldBuilder();
            $task = new WorldBuildTask(
                $world,
                $builder->operations(),
                function() use ($stateFile, $onPlayable) : void{
                    $this->writeState($stateFile, 'complete');
                    $this->playable = true;
                    $this->plugin->getLogger()->info('VivaArcade world layout completed.');
                    $onPlayable();
                },
                function(Throwable $e) : void{
                    $this->plugin->getLogger()->error('VivaArcade world layout stopped; its owned area will be retried on the next server start.');
                    $this->plugin->getLogger()->logException($e);
                }
            );
            $this->plugin->getScheduler()->scheduleRepeatingTask($task, 1);
            $this->plugin->getLogger()->info('VivaArcade world layout is being built in bounded tick batches.');
        }else{
            $this->playable = $state === 'complete';
            if($this->playable && $version < self::LAYOUT_VERSION){
                $this->plugin->getLogger()->warning('An older completed VivaArcade map was detected and preserved without automatic edits. The updated district entrances and spawn canopy require a reviewed migration.');
            }elseif(!$this->playable){
                $this->plugin->getLogger()->warning('The existing VivaArcade world was preserved. No untracked blocks were changed; game layout is not marked as installed.');
            }
        }

        return $world;
    }

    public function isPlayable() : bool{
        return $this->playable;
    }

    private function writeState(string $path, string $state) : void{
        $json = json_encode([
            'version' => self::LAYOUT_VERSION,
            'state' => $state,
            'updated_at' => time()
        ], JSON_THROW_ON_ERROR) . PHP_EOL;
        $temporary = $path . '.tmp';
        if(file_put_contents($temporary, $json, LOCK_EX) === false || !rename($temporary, $path)){
            @unlink($temporary);
            throw new RuntimeException('Unable to save world layout state');
        }
    }
}
