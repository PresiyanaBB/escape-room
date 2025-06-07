<?php

require '../db.php'; 

$outputDir = __DIR__ . '../../export-data';
$outputFile = $outputDir . '/teams.json';

if (!is_dir($outputDir)) {
    mkdir($outputDir, 0777, true);
}

try {
    $teamsStmt = $pdo->query("SELECT id, name FROM teams");
    $teams = $teamsStmt->fetchAll(PDO::FETCH_ASSOC);

    $exportData = ['teams' => []];

    $usersStmt = $pdo->prepare("SELECT u.username FROM team_users AS tu JOIN users u ON u.id = tu.user_id 
                                        JOIN teams t ON t.id = tu.team_id WHERE t.id = :team_id ORDER BY u.username ASC");

    foreach ($teams as $team) {
        $usersStmt->execute([':team_id' => $team['id']]);
        $users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

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
