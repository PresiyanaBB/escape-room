<?php

require 'db.php'; 

$outputDir = __DIR__ . '/../export-data';
$outputFile = $outputDir . '/games.json';

if (!is_dir($outputDir)) {
    mkdir($outputDir, 0777, true);
}

try {
    $rooms = $db->getAllRooms();
    $exportData = ['rooms' => []];

    foreach ($rooms as $room) {
        $games = $db->getGamesForRoom($room['id']);
        $leaderboards = $db->getLeaderboardForRoom($room['id']);

        $roomObj = [
            'name' => $room['name'],
            'steps' => (int)$room['steps'],
            'timeForSolving' => $room['time_for_solving'] ?: "00:00:00",
            'leaderboard' => [], 
            'games' => []
        ];

        foreach ($games as $game) {
            $roomObj['games'][] = [
                'question' => $game['question'],
                'answer' => $game['answer'],
                'hint' => $game['hint']
            ];
        }

        foreach ($leaderboards as $leaderboard) {
            $roomObj['leaderboard'][] = [
                'team_name' => $leaderboard['name'],
                'time' => $leaderboard['time']
            ];
        }
        $exportData['rooms'][] = ['room' => $roomObj];
    }

    $jsonString = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if (file_put_contents($outputFile, $jsonString) === false) {
        throw new Exception("Failed to write to $outputFile");
    }

    echo "Export completed successfully: $outputFile\n";

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
