<?php

declare(strict_types=1);

namespace VivaArcade\Games;

/**
 * Lists only game modes that are currently backed by live server gameplay.
 * Roadmap-only modes must not appear in the player-facing browser.
 */
final class GameCatalog{

    /** @var array<string, string> */
    private const CATEGORIES = [
        'all' => 'All Games',
        'featured' => 'Featured Games',
        'solo' => 'Solo Games',
        'skill' => 'Skill Games'
    ];

    /** @var array<string, array<string, mixed>> */
    private const GAMES = [
        'parkour-rush' => [
            'id' => 'parkour-rush',
            'name' => 'Parkour Rush',
            'description' => 'Run the neon course, reach the checkpoint, and cross the finish line. Your time is recorded by the server.',
            'difficulty' => 'Skill-based',
            'minimum_players' => 1,
            'maximum_players' => 1,
            'estimated_duration' => 'Varies by skill',
            'queue_status' => 'Direct Start (No Queue)',
            'arena_label' => '1 shared course',
            'categories' => ['featured', 'solo', 'skill']
        ]
    ];

    /** @return list<array{id:string, label:string}> */
    public static function getCategories() : array{
        $available = [];
        foreach(self::CATEGORIES as $id => $label){
            if($id === 'all'){
                $available[] = ['id' => $id, 'label' => $label];
                continue;
            }
            foreach(self::GAMES as $game){
                if(in_array($id, $game['categories'], true)){
                    $available[] = ['id' => $id, 'label' => $label];
                    break;
                }
            }
        }
        return $available;
    }

    /** @return list<array<string, mixed>> */
    public static function getGames(string $category = 'all') : array{
        if(!array_key_exists($category, self::CATEGORIES)){
            return [];
        }
        $games = [];
        foreach(self::GAMES as $game){
            if($category === 'all' || in_array($category, $game['categories'], true)){
                $games[] = $game;
            }
        }
        return $games;
    }

    /** @return array<string, mixed>|null */
    public static function find(string $gameId) : ?array{
        return self::GAMES[$gameId] ?? null;
    }

    public static function getCategoryLabel(string $category) : ?string{
        return self::CATEGORIES[$category] ?? null;
    }
}
