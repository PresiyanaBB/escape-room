<?php

require 'db.php'; 

$jsonPath = __DIR__ . '/../import-data/games.json';

if (!file_exists($jsonPath)) {
    die("JSON file not found at $jsonPath");
}

$jsonData = file_get_contents($jsonPath);
$data = json_decode($jsonData, true);

if ($data === null) {
    die("Failed to decode JSON: " . json_last_error_msg());
}

try {
    $db->beginTransaction();

    foreach ($data['rooms'] as $roomEntry) {
        $room = $roomEntry['room'];

        // Import room using Database class method
        $roomId = $db->importRoom(
            $room['name'],
            $room['steps'],
            $room['timeForSolving'] ?? null
        );

        // Import games for this room
        foreach ($room['games'] as $game) {
            $db->importGame(
                $roomId,
                $game['question'],
                $game['answer'],
                $game['hint'] ?? null
            );
        }
    }

    $db->commit();
    echo "Import completed successfully.";

} catch (PDOException $e) {
    $db->rollBack();
    die("Database error: " . $e->getMessage());
}
