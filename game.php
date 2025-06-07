<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) header("Location: login.php");

$user_id = $_SESSION['user_id'];

$teamStmt = $pdo->prepare("SELECT team_id FROM team_participants WHERE participant_id = ?");
$teamStmt->execute([$user_id]);
$team_id = $teamStmt->fetchColumn();

if (!$team_id) {
    echo "Please join a team first.";
    exit;
}

$room = $pdo->query("SELECT * FROM rooms LIMIT 1")->fetch();

if (!$room) {
    echo "No rooms available.";
    exit;
}

$room_id = $room['id'];
$timeForSolving = $room['time_for_solving']; // format hh:mm:ss

// Parse timeForSolving to DateInterval
list($h, $m, $s) = explode(':', $timeForSolving);
$duration = new DateInterval("PT{$h}H{$m}M{$s}S");

// Get team name
$teamName = $pdo->prepare("SELECT name FROM teams WHERE id = ?");
$teamName->execute([$team_id]);
$teamName = $teamName->fetchColumn();

// Check if leaderboard entry exists for this room and team
$startCheck = $pdo->prepare("SELECT * FROM leaderboard WHERE room_id = ? AND team_name = ?");
$startCheck->execute([$room_id, $teamName]);
$entry = $startCheck->fetch();

$startTime = $_SESSION['started_at'] ?? date("Y-m-d H:i:s");

$startTimeObj = new DateTime($startTime);
$endTime = clone $startTimeObj;
$endTime->add($duration);
$now = new DateTime();

if ($now > $endTime) {
    echo "Time's up!";
    exit;
}

// Show remaining time
$remaining = $now->diff($endTime);
echo "⏳ Time left: " . $remaining->format('%H:%I:%S') . "<br><br>";

// Fetch questions for this room
$questions = $pdo->prepare("SELECT * FROM games WHERE room_id = ?");
$questions->execute([$room_id]);

foreach ($questions as $q) {
    echo "<p><b>Q:</b> " . htmlspecialchars($q['question']) . "<br><i>Hint:</i> " . htmlspecialchars($q['hint']) . "</p>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Calculate completion time
    $completionInterval = $now->diff($startTimeObj);
    $completionTimeStr = $completionInterval->format('%H:%I:%S');

    // Update leaderboard completion time
    $update = $pdo->prepare("UPDATE leaderboard SET time = ? WHERE room_id = ? AND team_name = ?");
    $update->execute([$completionTimeStr, $room_id, $teamName]);

    echo "✅ Completed in: " . $completionTimeStr;
}
?>

<form method="post">
    <button type="submit">Submit all answers</button>
</form>
