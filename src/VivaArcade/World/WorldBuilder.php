<?php

declare(strict_types=1);

namespace VivaArcade\World;

use Generator;
use pocketmine\block\Block;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Vector3;

/**
 * Idempotent, lazy layout for the owned VivaArcade world coordinates.
 *
 * Each invocation writes the same blocks to reserved coordinates. It never
 * clears chunks or edits terrain outside the arcade's declared footprint.
 */
final class WorldBuilder{

    /**
     * @return Generator<int, array<string, mixed>, mixed, void>
     */
    public function operations() : Generator{
        $chunks = [];
        $this->addChunkRectangle($chunks, -16, 16, -16, 16);
        $this->addChunkRectangle($chunks, 99, 133, -1, 3);

        foreach(DistrictDirectory::all() as $district){
            $this->addChunkRectangle(
                $chunks,
                $district['x'] - 10,
                $district['x'] + 10,
                $district['z'] - 10,
                $district['z'] + 10
            );
        }

        foreach([-72, 0, 72] as $z){
            $this->addChunkRectangle($chunks, -120, 160, $z - 1, $z + 1);
        }
        foreach([-72, -24, 24, 72] as $x){
            $this->addChunkRectangle($chunks, $x - 1, $x + 1, -120, 120);
        }
        $this->addChunkRectangle($chunks, 108, 150, 47, 49);

        ksort($chunks, SORT_STRING);
        foreach($chunks as [$chunkX, $chunkZ]){
            yield ['type' => 'chunk', 'x' => $chunkX, 'z' => $chunkZ];
        }

        yield from $this->mainPlaza();
        yield from $this->districtRoads();
        foreach(DistrictDirectory::all() as $district){
            $color = DyeColor::from($district['color']);
            yield from $this->district($district['x'], $district['z'], $color);
            yield from $this->districtAccess($district['x'], $district['z'], $color);
        }
        yield from $this->parkourCourse();

        yield ['type' => 'spawn', 'x' => 0.5, 'y' => 101.0, 'z' => 0.5];
    }

    /**
     * @param array<string, array{int, int}> $chunks
     */
    private function addChunkRectangle(array &$chunks, int $minX, int $maxX, int $minZ, int $maxZ) : void{
        for($chunkX = $minX >> 4; $chunkX <= ($maxX >> 4); ++$chunkX){
            for($chunkZ = $minZ >> 4; $chunkZ <= ($maxZ >> 4); ++$chunkZ){
                $chunks[$chunkX . ':' . $chunkZ] = [$chunkX, $chunkZ];
            }
        }
    }

    /**
     * @return Generator<int, array<string, mixed>, mixed, void>
     */
    private function mainPlaza() : Generator{
        for($x = -16; $x <= 16; ++$x){
            for($z = -16; $z <= 16; ++$z){
                $edge = abs($x) === 16 || abs($z) === 16;
                $lane = ($x % 8) === 0 || ($z % 8) === 0;
                $block = $edge ? VanillaBlocks::SEA_LANTERN() : (
                    $lane ? VanillaBlocks::CONCRETE()->setColor(\pocketmine\block\utils\DyeColor::BLUE()) : VanillaBlocks::QUARTZ()
                );
                yield $this->block($x, 100, $z, $block);
            }
        }

        for($x = -5; $x <= 5; ++$x){
            for($z = -5; $z <= 5; ++$z){
                if(abs($x) === 5 || abs($z) === 5){
                    yield $this->block(
                        $x,
                        101,
                        $z,
                        VanillaBlocks::CONCRETE()->setColor(\pocketmine\block\utils\DyeColor::CYAN())
                    );
                }
            }
        }
        yield $this->block(0, 101, 0, VanillaBlocks::SEA_LANTERN());
        yield $this->block(0, 102, 0, VanillaBlocks::GLASS());
        yield $this->block(0, 103, 0, VanillaBlocks::GLOWSTONE());

        foreach([[-13, -13], [-13, 13], [13, -13], [13, 13]] as [$x, $z]){
            for($y = 68; $y <= 99; ++$y){
                $color = $y % 5 === 0
                    ? \pocketmine\block\utils\DyeColor::CYAN()
                    : \pocketmine\block\utils\DyeColor::BLUE();
                yield $this->block($x, $y, $z, VanillaBlocks::CONCRETE()->setColor($color));
            }
            yield $this->block($x, 100, $z, VanillaBlocks::SEA_LANTERN());
            yield $this->block($x, 101, $z, VanillaBlocks::GLASS());
        }
    }

    /**
     * @return Generator<int, array<string, mixed>, mixed, void>
     */
    private function districtRoads() : Generator{
        foreach([-72, 0, 72] as $z){
            $endX = $z === 0 ? 95 : 160;
            for($x = -120; $x <= $endX; ++$x){
                foreach([-1, 0, 1] as $offset){
                    $block = $offset !== 0
                        ? VanillaBlocks::SEA_LANTERN()
                        : VanillaBlocks::CONCRETE()->setColor(\pocketmine\block\utils\DyeColor::CYAN());
                    yield $this->block($x, 100, $z + $offset, $block);
                }
            }
        }

        foreach([-72, -24, 24, 72] as $x){
            for($z = -120; $z <= 120; ++$z){
                foreach([-1, 0, 1] as $offset){
                    $block = $offset !== 0
                        ? VanillaBlocks::SEA_LANTERN()
                        : VanillaBlocks::CONCRETE()->setColor(\pocketmine\block\utils\DyeColor::BLUE());
                    yield $this->block($x + $offset, 100, $z, $block);
                }
            }
        }

        // A separate illuminated connector joins the Parkour district without
        // crossing the existing Parkour Rush jump platforms at z=0..2.
        for($x = 108; $x <= 150; ++$x){
            foreach([-1, 0, 1] as $offset){
                $block = $offset === 0
                    ? VanillaBlocks::CONCRETE()->setColor(\pocketmine\block\utils\DyeColor::MAGENTA())
                    : VanillaBlocks::SEA_LANTERN();
                yield $this->block($x, 100, 48 + $offset, $block);
            }
        }
    }

    /**
     * Connect every district entrance to the nearest east-west lit avenue.
     *
     * @return Generator<int, array<string, mixed>, mixed, void>
     */
    private function districtAccess(int $centerX, int $centerZ, DyeColor $color) : Generator{
        $roadZ = $centerZ < 0 ? -72 : 72;
        $direction = $roadZ > $centerZ ? 1 : -1;
        $accent = VanillaBlocks::CONCRETE()->setColor($color);
        for($z = $centerZ + ($direction * 11); $direction > 0 ? $z <= $roadZ : $z >= $roadZ; $z += $direction){
            foreach([-1, 0, 1] as $offset){
                $block = $offset === 0 ? $accent : VanillaBlocks::SEA_LANTERN();
                yield $this->block($centerX + $offset, 100, $z, $block);
            }
        }
    }

    /**
     * @return Generator<int, array<string, mixed>, mixed, void>
     */
    private function district(int $centerX, int $centerZ, \pocketmine\block\utils\DyeColor $color) : Generator{
        $accent = VanillaBlocks::CONCRETE()->setColor($color);
        for($dx = -10; $dx <= 10; ++$dx){
            for($dz = -10; $dz <= 10; ++$dz){
                $edge = abs($dx) === 10 || abs($dz) === 10;
                $cross = $dx === 0 || $dz === 0;
                $block = $edge ? VanillaBlocks::SEA_LANTERN() : (
                    $cross ? $accent : VanillaBlocks::POLISHED_BLACKSTONE()
                );
                yield $this->block($centerX + $dx, 100, $centerZ + $dz, $block);
            }
        }

        // Four neon pylons and a compact gateway give each district its own
        // colour identity without introducing entities or per-tick effects.
        foreach([[-8, -8], [-8, 8], [8, -8], [8, 8]] as [$dx, $dz]){
            for($y = 101; $y <= 106; ++$y){
                yield $this->block($centerX + $dx, $y, $centerZ + $dz, $accent);
            }
            yield $this->block($centerX + $dx, 107, $centerZ + $dz, VanillaBlocks::SEA_LANTERN());
        }

        foreach([-3, 3] as $dx){
            for($y = 101; $y <= 106; ++$y){
                yield $this->block($centerX + $dx, $y, $centerZ - 8, $accent);
            }
        }
        for($dx = -3; $dx <= 3; ++$dx){
            yield $this->block($centerX + $dx, 107, $centerZ - 8, VanillaBlocks::SEA_LANTERN());
        }

        for($dx = -2; $dx <= 2; ++$dx){
            for($dz = -2; $dz <= 2; ++$dz){
                if(abs($dx) === 2 || abs($dz) === 2){
                    yield $this->block($centerX + $dx, 101, $centerZ + $dz, $accent);
                }
            }
        }
        yield $this->block($centerX, 101, $centerZ, VanillaBlocks::SEA_LANTERN());
        yield $this->block($centerX, 102, $centerZ, VanillaBlocks::GLASS());
        yield $this->block($centerX, 103, $centerZ, VanillaBlocks::SEA_LANTERN());
    }

    /**
     * Preserves the existing Parkour Rush start, checkpoint and finish layout.
     * @return Generator<int, array<string, mixed>, mixed, void>
     */
    private function parkourCourse() : Generator{
        $platforms = [
            [100, 100, 0, 'start'],
            [104, 101, 0, 'normal'],
            [108, 101, 2, 'normal'],
            [112, 102, 2, 'normal'],
            [116, 102, 0, 'checkpoint'],
            [120, 103, 0, 'normal'],
            [124, 103, 2, 'normal'],
            [128, 104, 2, 'normal'],
            [132, 104, 0, 'finish']
        ];

        foreach($platforms as [$centerX, $y, $centerZ, $kind]){
            $base = match($kind){
                'start' => VanillaBlocks::CONCRETE()->setColor(\pocketmine\block\utils\DyeColor::BLUE()),
                'checkpoint' => VanillaBlocks::GOLD(),
                'finish' => VanillaBlocks::DIAMOND(),
                default => VanillaBlocks::CONCRETE()->setColor(\pocketmine\block\utils\DyeColor::CYAN())
            };
            for($dx = -1; $dx <= 1; ++$dx){
                for($dz = -1; $dz <= 1; ++$dz){
                    yield $this->block($centerX + $dx, $y, $centerZ + $dz, $base);
                }
            }
            yield $this->block($centerX, $y - 1, $centerZ, VanillaBlocks::SEA_LANTERN());
        }
    }

    /**
     * @return array{type:'block',x:int,y:int,z:int,block:Block}
     */
    private function block(int $x, int $y, int $z, Block $block) : array{
        return ['type' => 'block', 'x' => $x, 'y' => $y, 'z' => $z, 'block' => $block];
    }
}
