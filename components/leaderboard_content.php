<?php
require 'db.php';

function displayLeaderboard($leaderboard, $rooms, $teams, $selectedRoom = null, $selectedTeam = null) {
    ?>
    <main class="leaderboard-container">
        <?php if (isset($_SESSION['congrats_message'])): ?>
            <div id="message" class="notification-popup congrats">
                <div class="notification-content">
                    <span class="notification-icon">🎉</span>
                    <span class="notification-text"><?= htmlspecialchars($_SESSION['congrats_message']) ?></span>
                </div>
            </div>
            <?php unset($_SESSION['congrats_message']); ?>
        <?php endif; ?>
        
        <script>
        if (document.getElementById('message')) {
            setTimeout(() => {
                const message = document.getElementById('message');
                message.style.animation = 'slideOut 0.3s ease-out forwards';
                setTimeout(() => {
                    message.remove();
                }, 300);
            }, 3000);
        }
        </script>
        
        <h1>Leaderboard</h1>
        
        <div class="leaderboard-filters">
            <form method="get" class="filter-form">
                <input type="hidden" name="page" value="leaderboard">
                
                <div class="filter-group">
                    <label for="room">Filter by Game:</label>
                    <select name="room" id="room" class="filter-select">
                        <option value="">All Games</option>
                        <?php foreach ($rooms as $room): ?>
                            <option value="<?= $room['id'] ?>" <?= $selectedRoom == $room['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($room['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="team">Filter by Team:</label>
                    <select name="team" id="team" class="filter-select">
                        <option value="">All Teams</option>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?= $team['id'] ?>" <?= $selectedTeam == $team['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($team['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="submit-button">Apply Filters</button>
            </form>
        </div>
        
        <?php if (empty($leaderboard)) { ?>
            <style>
                p.no-records { display: block; }
                table.leaderboard-table { display: none; }
            </style>
        <?php } else { ?>
            <style>
                p.no-records { display: none; }
                table.leaderboard-table { display: table; }
            </style>
        <?php } ?>

        <p class="no-records">No records found.</p>
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

// Get filter parameters
$selectedRoom = isset($_GET['room']) ? (int)$_GET['room'] : null;
$selectedTeam = isset($_GET['team']) ? (int)$_GET['team'] : null;

// Get all rooms and teams for filters
$rooms = $db->getAllRooms();
$teams = $db->getAllTeams();

// Get filtered leaderboard
$leaderboard = $db->getLeaderboard($selectedRoom, $selectedTeam);
displayLeaderboard($leaderboard, $rooms, $teams, $selectedRoom, $selectedTeam);
?> 