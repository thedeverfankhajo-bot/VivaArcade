<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/VivaArcade/World/DistrictDirectory.php';

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

foreach($districts as $district){
    $id = $district['id'];
    $center = $district['x'] . ':' . $district['z'];

    worldCheck(!isset($ids[$id]), 'district identifiers are unique');
    worldCheck(!isset($centers[$center]), 'district centers are unique');
    worldCheck((bool) preg_match('/^[a-z0-9-]{3,48}$/', $id), 'district identifier is safe');
    worldCheck((bool) preg_match('/^[\x20-\x7E]+$/', $district['label']), 'district label is ASCII Finglish');
    worldCheck(in_array($district['color'], $allowedColors, true), 'district color is a valid Bedrock dye color name');

    $ids[$id] = true;
    $centers[$center] = true;

    $located = DistrictDirectory::locate((float) $district['x'], (float) $district['z']);
    worldCheck($located !== null && $located['id'] === $id, 'district center resolves to its own location');
}

$spawn = DistrictDirectory::locate(0.5, 0.5);
worldCheck($spawn !== null && $spawn['id'] === 'main-spawn-plaza', 'central spawn plaza is discoverable');
worldCheck(DistrictDirectory::locate(400.0, 400.0) === null, 'coordinates outside the arcade return no district');

echo "PASS: {$checks} VivaArcade world layout checks\n";
