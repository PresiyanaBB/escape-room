<?php
// Exports users from the DB to /export-data/users.json

require '../db.php'; 

$outputDir = __DIR__ . '../../export-data';
$outputFile = $outputDir . '/users.json';

if (!is_dir($outputDir)) {
    mkdir($outputDir, 0777, true);
}

try {
    $usersStmt = $pdo->query("SELECT id, email, username FROM users");
    $users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

    $exportData = ['users' => []];

    foreach ($users as $user) {

        $userObj = [
            'email' => $user['email'],
            'username' => $user['username'],
        ];

        $exportData['users'][] = ['user' => $userObj];
    }


    $jsonString = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if (file_put_contents($outputFile, $jsonString) === false) {
        throw new Exception("Failed to write to $outputFile");
    }

    echo "Export completed successfully: $outputFile\n";

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
