<?php
require 'db.php';

$stmt = $pdo->query("SELECT g.name, l.team_name, l.completion_time
                    FROM leaderboard l JOIN games g ON g.id = l.game_id
                    ORDER BY l.completion_time");

foreach ($stmt as $row) {
    echo "{$row['name']} - {$row['team_name']} - {$row['completion_time']}<br>";
}
