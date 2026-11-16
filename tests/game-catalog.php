<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/VivaArcade/Games/GameCatalog.php';

use VivaArcade\Games\GameCatalog;

$checks = 0;
function catalogCheck(bool $condition, string $message) : void{
    global $checks;
    ++$checks;
    if(!$condition){
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$categories = GameCatalog::getCategories();
$categoryIds = array_column($categories, 'id');
catalogCheck($categoryIds === ['all', 'featured', 'solo', 'skill'], 'only categories containing real games are exposed');
catalogCheck(GameCatalog::getCategoryLabel('all') === 'All Games', 'all-games category has a label');
catalogCheck(GameCatalog::getCategoryLabel('team') === null, 'unsupported category has no player-facing label');
catalogCheck(count(GameCatalog::getGames('all')) === 1, 'only the currently playable mode is listed');
catalogCheck(count(GameCatalog::getGames('featured')) === 1, 'featured games contains the live mode');
catalogCheck(count(GameCatalog::getGames('solo')) === 1, 'solo games contains the live mode');
catalogCheck(count(GameCatalog::getGames('skill')) === 1, 'skill games contains the live mode');
catalogCheck(GameCatalog::getGames('team') === [], 'unimplemented team games are not advertised');
catalogCheck(GameCatalog::getGames('multiplayer') === [], 'unimplemented multiplayer games are not advertised');

$game = GameCatalog::find('parkour-rush');
catalogCheck($game !== null && $game['name'] === 'Parkour Rush', 'live Parkour Rush descriptor is available');
catalogCheck($game !== null && $game['minimum_players'] === 1 && $game['maximum_players'] === 1, 'solo run player limits are accurate');
catalogCheck($game !== null && $game['queue_status'] === 'Direct Start (No Queue)', 'browser does not imply a matchmaking queue exists');
catalogCheck($game !== null && $game['arena_label'] === '1 shared course', 'browser discloses the shared course rather than claiming arena isolation');
catalogCheck($game !== null && preg_match('/^[\x20-\x7E]+$/', $game['description']) === 1, 'player-facing description is ASCII Finglish/English');
catalogCheck(GameCatalog::find('not-implemented') === null, 'unknown game IDs are rejected');

echo "PASS: {$checks} game catalog checks\n";
