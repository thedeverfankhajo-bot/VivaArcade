<?php

declare(strict_types=1);

namespace VivaArcade\\World;

use pocketmine\\block\\utils\\DyeColor;

/**
 * Stable coordinates and player-facing Finglish names for VivaArcade districts.
 *
 * The same directory is used by the world builder and the lobby movement
 * adapter, preventing visual layout and zone detection from drifting apart.
 */
final class DistrictDirectory{

    /** @var list<array{id:string,label:string,x:int,z:int,color:DyeColor}> */
    private static ?array $districts = null;

    /**
     * @return list<array{id:string,label:string,x:int,z:int,color:DyeColor}>
     */
    public static function all() : array{
        return self::$districts ??= [
            ['id' => 'arcade-central', 'label' => 'Markaz Arcade', 'x' => -240, 'z' => -180, 'color' => DyeColor::PURPLE()],
            ['id' => 'minigame-district', 'label' => 'Mahalle MiniGame', 'x' => -120, 'z' => -180, 'color' => DyeColor::CYAN()],
            ['id' => 'challenge-tower', 'label' => 'Borje Challenge', 'x' => 0, 'z' => -180, 'color' => DyeColor::BLUE()],
            ['id' => 'parkour-district', 'label' => 'Mahalle Parkour', 'x' => 120, 'z' => -180, 'color' => DyeColor::MAGENTA()],
            ['id' => 'racing-district', 'label' => 'Masir Racing', 'x' => 240, 'z' => -180, 'color' => DyeColor::PINK()],

            ['id' => 'puzzle-district', 'label' => 'Mahalle Puzzle', 'x' => -240, 'z' => -60, 'color' => DyeColor::RED()],
            ['id' => 'build-district', 'label' => 'Mahalle Build', 'x' => -120, 'z' => -60, 'color' => DyeColor::ORANGE()],
            ['id' => 'party-district', 'label' => 'Mahalle Party', 'x' => 0, 'z' => -60, 'color' => DyeColor::YELLOW()],
            ['id' => 'shop-district', 'label' => 'Foroushgah Shop', 'x' => 120, 'z' => -60, 'color' => DyeColor::LIME()],
            ['id' => 'cosmetics-plaza', 'label' => 'Meydane Cosmetics', 'x' => 240, 'z' => -60, 'color' => DyeColor::GREEN()],

            ['id' => 'leaderboard-hall', 'label' => 'Hall of Fame', 'x' => -240, 'z' => 60, 'color' => DyeColor::LIGHT_BLUE()],
            ['id' => 'vip-lounge', 'label' => 'Lounge VIP', 'x' => -120, 'z' => 60, 'color' => DyeColor::WHITE()],
            ['id' => 'achievement-hall', 'label' => 'Hall of Achievement', 'x' => 0, 'z' => 60, 'color' => DyeColor::BLACK()],
            ['id' => 'daily-challenge-center', 'label' => 'Markaz Challenge Roozane', 'x' => 120, 'z' => 60, 'color' => DyeColor::GRAY()],
            ['id' => 'weekly-challenge-center', 'label' => 'Markaz Challenge Haftegi', 'x' => 240, 'z' => 60, 'color' => DyeColor::BROWN()],

            ['id' => 'tournament-arena', 'label' => 'Arena Tournament', 'x' => -240, 'z' => 180, 'color' => DyeColor::CYAN()],
            ['id' => 'player-hangout', 'label' => 'Pateye Bazikonan', 'x' => -120, 'z' => 180, 'color' => DyeColor::PURPLE()],
            ['id' => 'tutorial-zone', 'label' => 'Mantaqe Tutorial', 'x' => 0, 'z' => 180, 'color' => DyeColor::BLUE()],
            ['id' => 'secret-reward-area', 'label' => 'Ganje Makfi', 'x' => 120, 'z' => 180, 'color' => DyeColor::MAGENTA()]
        ];
    }

    /**
     * @return array{id:string,label:string,x:int,z:int,color:DyeColor}|null
     */
    public static function locate(float $x, float $z) : ?array{
        if(abs($x) <= 16 && abs($z) <= 16){
            return [
                'id' => 'main-spawn-plaza',
                'label' => 'Meydane Asli',
                'x' => 0,
                'z' => 0,
                'color' => DyeColor::CYAN()
            ];
        }

        foreach(self::all() as $district){
            if(abs($x - $district['x']) <= 9 && abs($z - $district['z']) <= 9){
                return $district;
            }
        }

        return null;
    }
}
