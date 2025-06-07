<?php
// export.php
// Exports rooms and games from the DB to /export-data/games.json

require 'db.php'; // your PDO connection

$outputDir = __DIR__ . '/export-data';
$outputFile = $outputDir . '/games.json';

if (!is_dir($outputDir)) {
    mkdir($outputDir, 0777, true);
}

try {
    // Fetch all rooms
    $roomsStmt = $pdo->query("SELECT id, name, steps, time_for_solving FROM rooms");
    $rooms = $roomsStmt->fetchAll(PDO::FETCH_ASSOC);

    $exportData = ['rooms' => []];

    $gamesStmt = $pdo->prepare("SELECT question, answer, hint FROM games WHERE room_id = :room_id ORDER BY id ASC");

    foreach ($rooms as $room) {
        $gamesStmt->execute([':room_id' => $room['id']]);
        $games = $gamesStmt->fetchAll(PDO::FETCH_ASSOC);

        // Build room object matching your JSON structure
        $roomObj = [
            'name' => $room['name'],
            'steps' => (int)$room['steps'],
            'timeForSolving' => $room['time_for_solving'] ?: "00:00:00",
            'leaderboard' => [], // as per your input, always empty here
            'games' => []
        ];

        foreach ($games as $game) {
            $roomObj['games'][] = [
                'question' => $game['question'],
                'answer' => $game['answer'],
                'hint' => $game['hint']
            ];
        }

        $exportData['rooms'][] = ['room' => $roomObj];
    }

    // Save JSON file with pretty print
    $jsonString = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if (file_put_contents($outputFile, $jsonString) === false) {
        throw new Exception("Failed to write to $outputFile");
    }

    echo "Export completed successfully: $outputFile\n";

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
