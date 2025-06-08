<?php

require 'db.php'; 

$outputDir = __DIR__ . '/../export-data';
$outputFile = $outputDir . '/teams.json';

if (!is_dir($outputDir)) {
    mkdir($outputDir, 0777, true);
}

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
    if (file_put_contents($outputFile, $jsonString) === false) {
        throw new Exception("Failed to write to $outputFile");
    }

    echo "Export completed successfully: $outputFile\n";

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
