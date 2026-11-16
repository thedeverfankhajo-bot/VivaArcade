<?php

declare(strict_types=1);

namespace pocketmine\block\utils{
    enum DyeColor{
        case WHITE;
        case ORANGE;
        case MAGENTA;
        case LIGHT_BLUE;
        case YELLOW;
        case LIME;
        case PINK;
        case GRAY;
        case CYAN;
        case PURPLE;
        case BLUE;
        case BROWN;
        case GREEN;
        case RED;
        case BLACK;
    }
}

namespace{

require_once __DIR__ . '/../src/VivaArcade/World/DistrictDirectory.php';

use pocketmine\block\utils\DyeColor;
use VivaArcade\World\DistrictDirectory;

$checks = 0;
function worldCheck(bool $condition, string $message) : void{
    global $checks;
    ++$checks;
    if(!$condition){
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$districts = DistrictDirectory::all();
worldCheck(count($districts) === 19, 'the arcade directory contains 19 named districts');

$ids = [];
$centers = [];
$allowedColors = [
    'white', 'orange', 'magenta', 'light_blue', 'yellow', 'lime', 'pink',
    'gray', 'cyan', 'purple', 'blue', 'brown', 'green', 'red', 'black'
];

$colorCases = [
    'white' => DyeColor::WHITE,
    'orange' => DyeColor::ORANGE,
    'magenta' => DyeColor::MAGENTA,
    'light_blue' => DyeColor::LIGHT_BLUE,
    'yellow' => DyeColor::YELLOW,
    'lime' => DyeColor::LIME,
    'pink' => DyeColor::PINK,
    'gray' => DyeColor::GRAY,
    'cyan' => DyeColor::CYAN,
    'purple' => DyeColor::PURPLE,
    'blue' => DyeColor::BLUE,
    'brown' => DyeColor::BROWN,
    'green' => DyeColor::GREEN,
    'red' => DyeColor::RED,
    'black' => DyeColor::BLACK
];

foreach($districts as $district){
    $id = $district['id'];
    $center = $district['x'] . ':' . $district['z'];

    worldCheck(!isset($ids[$id]), 'district identifiers are unique');
    worldCheck(!isset($centers[$center]), 'district centers are unique');
    worldCheck((bool) preg_match('/^[a-z0-9-]{3,48}$/', $id), 'district identifier is safe');
    worldCheck((bool) preg_match('/^[\x20-\x7E]+$/', $district['label']), 'district label is ASCII Finglish');
    worldCheck(in_array($district['color'], $allowedColors, true), 'district color is a valid Bedrock dye color name');
    worldCheck(DistrictDirectory::toDyeColor($district['color']) === $colorCases[$district['color']], 'district color maps to its API-5 DyeColor enum case');

    $ids[$id] = true;
    $centers[$center] = true;

    $located = DistrictDirectory::locate((float) $district['x'], (float) $district['z']);
    worldCheck($located !== null && $located['id'] === $id, 'district center resolves to its own location');
}

$spawn = DistrictDirectory::locate(0.5, 0.5);
worldCheck($spawn !== null && $spawn['id'] === 'main-spawn-plaza', 'central spawn plaza is discoverable');
worldCheck(DistrictDirectory::locate(400.0, 400.0) === null, 'coordinates outside the arcade return no district');

$invalidColorRejected = false;
try{
    DistrictDirectory::toDyeColor('not-a-dye');
}catch(\InvalidArgumentException){
    $invalidColorRejected = true;
}
worldCheck($invalidColorRejected, 'unknown color keys are rejected safely');

echo "PASS: {$checks} VivaArcade world layout checks\n";
}
