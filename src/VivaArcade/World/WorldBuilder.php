<?php

declare(strict_types=1);

namespace VivaArcade\World;

use pocketmine\block\VanillaBlocks;
use pocketmine\math\Vector3;
use pocketmine\world\World;

/** Idempotent builder for fixed, reserved VivaArcade lobby and Parkour Rush coordinates. */
final class WorldBuilder{

    public function build(World $world) : void{
        foreach([-1, 0, 1] as $chunkX){
            foreach([-1, 0, 1] as $chunkZ){
                $world->loadChunk($chunkX, $chunkZ);
            }
        }
        for($chunkX = 1; $chunkX <= 8; ++$chunkX){
            foreach([-1, 0] as $chunkZ){
                $world->loadChunk($chunkX, $chunkZ);
            }
        }

        $this->buildPlaza($world);
        $this->buildPylons($world);
        $this->buildConnector($world);
        $this->buildParkourCourse($world);
        $world->setSpawnLocation(new Vector3(0.5, 101, 0.5));
    }

    private function buildPlaza(World $world) : void{
        for($x = -16; $x <= 16; ++$x){
            for($z = -16; $z <= 16; ++$z){
                $edge = abs($x) === 16 || abs($z) === 16;
                $lane = ($x % 8) === 0 || ($z % 8) === 0;
                $block = $edge ? VanillaBlocks::SEA_LANTERN() : ($lane ? VanillaBlocks::CONCRETE()->setColor(DyeColor::BLUE) : VanillaBlocks::QUARTZ());
                $world->setBlockAt($x, 100, $z, $block, false);
            }
        }

        for($x = -5; $x <= 5; ++$x){
            for($z = -5; $z <= 5; ++$z){
                if(abs($x) === 5 || abs($z) === 5){
                    $world->setBlockAt($x, 101, $z, VanillaBlocks::CONCRETE()->setColor(DyeColor::CYAN), false);
                }
            }
        }
        $world->setBlockAt(0, 101, 0, VanillaBlocks::SEA_LANTERN(), false);
        $world->setBlockAt(0, 102, 0, VanillaBlocks::GLASS(), false);
        $world->setBlockAt(0, 103, 0, VanillaBlocks::GLOWSTONE(), false);
    }

    private function buildPylons(World $world) : void{
        foreach([[-13, -13], [-13, 13], [13, -13], [13, 13]] as [$x, $z]){
            for($y = 68; $y <= 99; ++$y){
                $block = ($y % 5 === 0) ? VanillaBlocks::CONCRETE()->setColor(DyeColor::CYAN) : VanillaBlocks::CONCRETE()->setColor(DyeColor::BLUE);
                $world->setBlockAt($x, $y, $z, $block, false);
            }
            $world->setBlockAt($x, 100, $z, VanillaBlocks::SEA_LANTERN(), false);
            $world->setBlockAt($x, 101, $z, VanillaBlocks::GLASS(), false);
        }
    }

    private function buildConnector(World $world) : void{
        for($x = 17; $x <= 95; ++$x){
            for($z = -2; $z <= 2; ++$z){
                $block = abs($z) === 2 ? VanillaBlocks::SEA_LANTERN() : (($z === 0) ? VanillaBlocks::CONCRETE()->setColor(DyeColor::CYAN) : VanillaBlocks::QUARTZ());
                $world->setBlockAt($x, 100, $z, $block, false);
            }
        }
    }

    private function buildParkourCourse(World $world) : void{
        $platforms = [
            [100, 100, 0, "start"],
            [104, 101, 0, "normal"],
            [108, 101, 2, "normal"],
            [112, 102, 2, "normal"],
            [116, 102, 0, "checkpoint"],
            [120, 103, 0, "normal"],
            [124, 103, 2, "normal"],
            [128, 104, 2, "normal"],
            [132, 104, 0, "finish"]
        ];

        foreach($platforms as [$centerX, $y, $centerZ, $kind]){
            $base = match($kind){
                "start" => VanillaBlocks::CONCRETE()->setColor(DyeColor::BLUE),
                "checkpoint" => VanillaBlocks::GOLD(),
                "finish" => VanillaBlocks::DIAMOND(),
                default => VanillaBlocks::CONCRETE()->setColor(DyeColor::CYAN)
            };
            for($dx = -1; $dx <= 1; ++$dx){
                for($dz = -1; $dz <= 1; ++$dz){
                    $world->setBlockAt($centerX + $dx, $y, $centerZ + $dz, $base, false);
                }
            }
            $world->setBlockAt($centerX, $y - 1, $centerZ, VanillaBlocks::SEA_LANTERN(), false);
        }
    }
}
