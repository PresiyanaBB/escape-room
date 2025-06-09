<?php
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ?page=login');
    exit;
}

$currentTeamId = $db->getTeamForUser($_SESSION['user_id']);
$currentTeamName = $currentTeamId ? $db->getTeamName($currentTeamId) : null;
?>

<main class="dashboard-container">
    <header class="dashboard-header">
        <h1>
            Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!
            <?php if ($currentTeamName): ?>
                Your current team is '<?= htmlspecialchars($currentTeamName) ?>'.
            <?php endif; ?>
        </h1>
    </header>

    <nav class="dashboard-navigation">
        <a href="?page=team" class="nav-link">Join a Team</a>
        <a href="?page=game" class="nav-link">Start Game</a>
        <a href="?page=leaderboard" class="nav-link">View Leaderboard</a>
    </nav>
</main>