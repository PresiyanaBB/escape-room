<?php
require_once __DIR__ . '/../db.php';

if (!isset($_FILES['game_file']) || $_FILES['game_file']['error'] !== UPLOAD_ERR_OK) {
    throw new Exception('Please select a valid file to import');
}

$jsonData = file_get_contents($_FILES['game_file']['tmp_name']);
$data = json_decode($jsonData, true);

if ($data === null) {
    throw new Exception('Failed to decode JSON: ' . json_last_error_msg());
}

if (empty($data['room'])) {
    throw new Exception('Please select a valid file to import.');
}

$room = $data['room'];

try {
    $db->beginTransaction();

    $existingRoom = $db->getRoomByName($room['name']);
    $roomId = $existingRoom
        ? $existingRoom['id']
        : $db->importRoom(
              $room['name'],
              $room['steps'],
              $room['timeForSolving'] ?? null
          );

    $importedGames = 0;
    $skippedGames  = 0;

    foreach ($room['games'] as $game) {
        if ($db->getGameByQuestion($roomId, $game['question'])) {
            $skippedGames++;
            continue;
        }
        $db->importGame(
            $roomId,
            $game['question'],
            $game['answer'],
            $game['hint'] ?? null
        );
        $importedGames++;
    }

    $db->commit();

    $_SESSION['import_message'] = sprintf(
        'Imported room "%s" (%d new games, %d skipped).',
        htmlspecialchars($room['name']),
        $importedGames,
        $skippedGames
    );

} catch (PDOException $e) {
    $db->rollBack();
    throw new Exception('Database error: ' . $e->getMessage());
}

