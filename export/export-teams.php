<?php
// Prevent any output buffering
if (ob_get_level()) ob_end_clean();

// Set headers for JSON download
header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="teams.json"');

require_once __DIR__ . '/../db.php';

try {
    $teams = $db->getAllTeams();
    $exportData = ['teams' => []];

    foreach ($teams as $team) {
        $users = $db->getUsersForTeam($team['id']);

        $teamObj = [
            'name' => $team['name'],
            'users' => []
        ];

        foreach ($users as $user) {
            $teamObj['users'][] = [
                'username' => $user['username']
            ];
        }

        $exportData['teams'][] = ['team' => $teamObj];
    }

    $jsonString = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    header('Content-Length: ' . strlen($jsonString));
    echo $jsonString;

} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => $e->getMessage()]);
}
