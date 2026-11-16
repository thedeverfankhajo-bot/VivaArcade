<?php

declare(strict_types=1);

require_once __DIR__ . "/../src/VivaArcade/Storage/JsonPlayerStorage.php";

use VivaArcade\Storage\JsonPlayerStorage;

$checks = 0;
function storageCheck(bool $condition, string $message) : void{
    global $checks;
    ++$checks;
    if(!$condition){
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "vivaarcade-storage-" . bin2hex(random_bytes(6));
$uuid = "00000000-0000-4000-8000-000000000001";

try{
    $storage = new JsonPlayerStorage($directory);
    $profile = $storage->getProfile($uuid, "Tester");
    storageCheck($profile["dollar"] === 0 && $profile["level"] === 1, "new profile defaults");
    storageCheck($storage->completeParkour($uuid, "Tester", 42000, 50, 30, "test-session-1"), "first completion is credited");
    storageCheck(!$storage->completeParkour($uuid, "Tester", 42000, 50, 30, "test-session-1"), "duplicate completion is rejected");
    $updated = $storage->getProfile($uuid, "Tester");
    storageCheck($updated["dollar"] === 50 && $updated["xp"] === 30, "wallet and xp update exactly once");
    storageCheck($updated["stats"]["games"] === 1 && $updated["stats"]["wins"] === 1, "game counts update once");
    storageCheck($updated["stats"]["best_parkour_ms"] === 42000, "best time saved");
    storageCheck($storage->completeParkour($uuid, "Tester", 39000, 40, 30, "test-session-2"), "second unique completion is credited");
    $afterSecond = $storage->getProfile($uuid, "Tester");
    storageCheck($afterSecond["dollar"] === 90 && $afterSecond["stats"]["games"] === 2, "second transaction is applied");
    storageCheck($afterSecond["stats"]["best_parkour_ms"] === 39000, "best time improves");
    echo "PASS: {$checks} VivaArcade storage checks\n";
}finally{
    if(is_dir($directory)){
        foreach(glob($directory . DIRECTORY_SEPARATOR . "*") ?: [] as $file){
            if(is_file($file)){ unlink($file); }
        }
        rmdir($directory);
    }
}
