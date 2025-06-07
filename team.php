<?php
require 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $team = $_POST['team'];
    $stmt = $pdo->prepare("SELECT id FROM teams WHERE name = ?");
    $stmt->execute([$team]);
    $teamId = $stmt->fetchColumn();

    if (!$teamId) {
        $pdo->prepare("INSERT INTO teams (name) VALUES (?)")->execute([$team]);
        $teamId = $pdo->lastInsertId();
    }

    $pdo->prepare("INSERT IGNORE INTO team_participants (team_id, participant_id) VALUES (?, ?)")
        ->execute([$teamId, $_SESSION['user_id']]);

    echo "Joined team '$team'";
}
?>

<form method="post">
    Enter team name: <input name="team">
    <button type="submit">Join</button>
</form>
