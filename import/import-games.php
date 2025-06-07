<?php
// This script imports rooms and games from /data/games.json into the database

require '../db.php'; 

$jsonPath = __DIR__ . '../../import-data/games.json';

if (!file_exists($jsonPath)) {
    die("JSON file not found at $jsonPath");
}

$jsonData = file_get_contents($jsonPath);
$data = json_decode($jsonData, true);

if ($data === null) {
    die("Failed to decode JSON: " . json_last_error_msg());
}

try {
    $pdo->beginTransaction();

    $insertRoomStmt = $pdo->prepare("
        INSERT INTO rooms (name, steps, time_for_solving)
        VALUES (:name, :steps, :time_for_solving)
    ");

    $insertGameStmt = $pdo->prepare("
        INSERT INTO games (room_id, question, answer, hint)
        VALUES (:room_id, :question, :answer, :hint)
    ");

    foreach ($data['rooms'] as $roomEntry) {
        $room = $roomEntry['room'];


        $name = $room['name'];
        $steps = $room['steps'];
        $timeForSolving = $room['timeForSolving'] ?? null; 

 
        $timeForSolving = $timeForSolving ? $timeForSolving : null;

        $insertRoomStmt->execute([
            ':name' => $name,
            ':steps' => $steps,
            ':time_for_solving' => $timeForSolving
        ]);

        $roomId = $pdo->lastInsertId();

        foreach ($room['games'] as $game) {
            $question = $game['question'];
            $answer = $game['answer'];
            $hint = $game['hint'] ?? null;

            $insertGameStmt->execute([
                ':room_id' => $roomId,
                ':question' => $question,
                ':answer' => $answer,
                ':hint' => $hint
            ]);
        }
    }

    $pdo->commit();

    echo "Import completed successfully.";

} catch (PDOException $e) {
    $pdo->rollBack();
    die("Database error: " . $e->getMessage());
}
