<?php
require 'db.php';

$stmt = $pdo->query("SELECT r.name, t.name AS team_name, l.time
                    FROM leaderboard l JOIN rooms r ON r.id = l.room_id
                    JOIN teams t ON t.id = l.team_id
                    ORDER BY l.time");

foreach ($stmt as $row) {
    echo "{$row['name']} - {$row['team_name']} - {$row['time']}<br>";
}
