<?php

require '../db.php'; 

$outputDir = __DIR__ . '../../export-data';
$outputFile = $outputDir . '/games.json';

if (!is_dir($outputDir)) {
    mkdir($outputDir, 0777, true);
}

try {
    $roomsStmt = $pdo->query("SELECT id, name, steps, time_for_solving FROM rooms");
    $rooms = $roomsStmt->fetchAll(PDO::FETCH_ASSOC);

    $exportData = ['rooms' => []];

    $gamesStmt = $pdo->prepare("SELECT question, answer, hint FROM games WHERE room_id = :room_id ORDER BY id ASC");

    $leaderboardsStmt = $pdo->prepare("SELECT t.name, l.time FROM leaderboard as l 
    JOIN teams as t ON t.id = l.team_id WHERE room_id = :room_id ORDER BY l.time ASC LIMIT 5");

    foreach ($rooms as $room) {
        $gamesStmt->execute([':room_id' => $room['id']]);
        $games = $gamesStmt->fetchAll(PDO::FETCH_ASSOC);
        $leaderboardsStmt->execute([':room_id' => $room['id']]);
        $leaderboards = $leaderboardsStmt->fetchAll(PDO::FETCH_ASSOC);

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
