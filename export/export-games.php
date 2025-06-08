<?php
// Prevent any output buffering
if (ob_get_level()) ob_end_clean();

// Set headers for JSON download
header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="games.json"');

require_once __DIR__ . '/../db.php';

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
    header('Content-Length: ' . strlen($jsonString));
    echo $jsonString;

} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => $e->getMessage()]);
}
