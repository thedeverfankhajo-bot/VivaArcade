<?php

declare(strict_types=1);

namespace VivaArcade\World;

/**
 * Stable coordinates and player-facing Finglish names for VivaArcade districts.
 */
final class DistrictDirectory{

    /** @var list<array{id:string,label:string,x:int,z:int,color:string}> */
    private static ?array $districts = null;

    /**
     * @return list<array{id:string,label:string,x:int,z:int,color:string}>
     */
    public static function all() : array{
        return self::$districts ??= [
            ['id' => 'arcade-central', 'label' => 'Markaz Arcade', 'x' => -96, 'z' => -96, 'color' => 'purple'],
            ['id' => 'minigame-district', 'label' => 'Mahalle MiniGame', 'x' => -48, 'z' => -96, 'color' => 'cyan'],
            ['id' => 'challenge-tower', 'label' => 'Borje Challenge', 'x' => 0, 'z' => -96, 'color' => 'blue'],
            ['id' => 'parkour-district', 'label' => 'Mahalle Parkour', 'x' => 160, 'z' => 48, 'color' => 'magenta'],
            ['id' => 'racing-district', 'label' => 'Mahalle Racing', 'x' => 96, 'z' => -96, 'color' => 'pink'],

            ['id' => 'puzzle-district', 'label' => 'Mahalle Puzzle', 'x' => -96, 'z' => -48, 'color' => 'red'],
            ['id' => 'build-district', 'label' => 'Mahalle Build', 'x' => -48, 'z' => -48, 'color' => 'orange'],
            ['id' => 'party-district', 'label' => 'Mahalle Party', 'x' => 0, 'z' => -48, 'color' => 'yellow'],
            ['id' => 'shop-district', 'label' => 'Foroushgah Shop', 'x' => 48, 'z' => -48, 'color' => 'lime'],
            ['id' => 'cosmetics-plaza', 'label' => 'Meydane Cosmetics', 'x' => 96, 'z' => -48, 'color' => 'green'],

            ['id' => 'leaderboard-hall', 'label' => 'Hall of Fame', 'x' => -96, 'z' => 48, 'color' => 'light_blue'],
            ['id' => 'vip-lounge', 'label' => 'Lounge VIP', 'x' => -48, 'z' => 48, 'color' => 'white'],
            ['id' => 'achievement-hall', 'label' => 'Hall of Achievement', 'x' => 0, 'z' => 48, 'color' => 'black'],
            ['id' => 'daily-challenge-center', 'label' => 'Challenge Roozane', 'x' => 48, 'z' => 48, 'color' => 'gray'],
            ['id' => 'weekly-challenge-center', 'label' => 'Challenge Haftegi', 'x' => 96, 'z' => 48, 'color' => 'brown'],

            ['id' => 'tournament-arena', 'label' => 'Arena Tournament', 'x' => -96, 'z' => 96, 'color' => 'cyan'],
            ['id' => 'player-hangout', 'label' => 'Pateye Bazikonan', 'x' => -48, 'z' => 96, 'color' => 'purple'],
            ['id' => 'tutorial-zone', 'label' => 'Mantaqe Tutorial', 'x' => 0, 'z' => 96, 'color' => 'blue'],
            ['id' => 'secret-reward-area', 'label' => 'Ganje Makfi', 'x' => 48, 'z' => 96, 'color' => 'magenta']
        ];
    }

    /**
     * @return array{id:string,label:string,x:int,z:int,color:string}|null
     */
    public static function locate(float $x, float $z) : ?array{
        if(abs($x) <= 16 && abs($z) <= 16){
            return [
                'id' => 'main-spawn-plaza',
                'label' => 'Meydane Asli',
                'x' => 0,
                'z' => 0,
                'color' => 'cyan'
            ];
        }

        foreach(self::all() as $district){
            if(abs($x - $district['x']) <= 10 && abs($z - $district['z']) <= 10){
                return $district;
            }
        }
        return null;
    }
}
