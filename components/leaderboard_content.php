<?php
require 'db.php';

function displayLeaderboard($leaderboard) {
    if (empty($leaderboard)) {
        echo '<p class="no-records">No records found.</p>';
        return;
    }
    ?>
    <main class="leaderboard-container">
        <h1>Leaderboard</h1>
        
        <table class="leaderboard-table">
            <thead>
                <tr>
                    <th>Room</th>
                    <th>Team</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leaderboard as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['team_name']) ?></td>
                        <td><?= htmlspecialchars($row['time']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <nav class="leaderboard-navigation">
            <a href="?page=dashboard" class="nav-link">Back to Dashboard</a>
        </nav>
    </main>
    <?php
}

$leaderboard = $db->getLeaderboard();
displayLeaderboard($leaderboard);
?> 