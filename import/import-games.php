<?php

require_once __DIR__ . '/../db.php'; 

if (!isset($_FILES['games_file']) || $_FILES['games_file']['error'] !== UPLOAD_ERR_OK) {
    throw new Exception("Please select a valid file to import");
}

$jsonData = file_get_contents($_FILES['games_file']['tmp_name']);
$data = json_decode($jsonData, true);

if ($data === null) {
    throw new Exception("Failed to decode JSON: " . json_last_error_msg());
}

try {
    $db->beginTransaction();
    $importedRooms = 0;
    $importedGames = 0;
    $skippedGames = 0;

    foreach ($data['rooms'] as $roomEntry) {
        $room = $roomEntry['room'];
        
        // Check if room already exists
        $existingRoom = $db->getRoomByName($room['name']);
        
        if ($existingRoom) {
            $roomId = $existingRoom['id'];
        } else {
            // Import new room
            $roomId = $db->importRoom(
                $room['name'],
                $room['steps'],
                $room['timeForSolving'] ?? null
            );
            $importedRooms++;
        }

        // Import games for this room
        foreach ($room['games'] as $game) {
            // Check if game already exists for this room
            $existingGame = $db->getGameByQuestion($roomId, $game['question']);
            
            if ($existingGame) {
                $skippedGames++;
                continue; // Skip this game as it already exists
            }

            $db->importGame(
                $roomId,
                $game['question'],
                $game['answer'],
                $game['hint'] ?? null
            );
            $importedGames++;
        }
    }

    $db->commit();
    
    // Return success message with import statistics
    $message = sprintf(
        "Import completed successfully! Imported %d new rooms and %d new games. Skipped %d existing games.",
        $importedRooms,
        $importedGames,
        $skippedGames
    );
    
    $_SESSION['import_message'] = $message;
    header("Location: ?page=settings");
    exit;

} catch (PDOException $e) {
    $db->rollBack();
    throw new Exception("Database error: " . $e->getMessage());
}
