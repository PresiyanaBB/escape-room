<?php
require 'db.php';

function handleTeamJoin($db, $teamName, $userId) {
    // First, remove user from any existing team
    $db->removeUserFromTeam($userId);
    
    // Then find or create the new team
    $teamId = $db->findTeamByName($teamName);
    if (!$teamId) {
        $teamId = $db->createTeam($teamName);
    }
    
    // Add user to the new team
    return $db->addUserToTeam($teamId, $userId);
}

function handleTeamQuit($db, $userId) {
    $db->removeUserFromTeam($userId);
}

// Get current team info
$currentTeamId = $db->getTeamForUser($_SESSION['user_id']);
$currentTeamName = $currentTeamId ? $db->getTeamName($currentTeamId) : null;

$message = "";
$messageType = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['quit'])) {
        if (!$currentTeamId) {
            $message     = 'You are not currently in a team.';
            $messageType = 'warning';
        } else {
            handleTeamQuit($db, $_SESSION['user_id']);
            $message     = "You have left team '" . htmlspecialchars($currentTeamName) . "'";
            $messageType = 'success';
            $currentTeamId   = null;
            $currentTeamName = null;
        }
    } elseif (isset($_POST['team'])) {
        if ($currentTeamId) {
            $message     = 'You are already in a team!';
            $messageType = 'warning';
        } elseif (handleTeamJoin($db, $_POST['team'], $_SESSION['user_id'])) {
            $message     = "Successfully joined team '" . htmlspecialchars($_POST['team']) . "'";
            $messageType = 'success';
            $currentTeamName = htmlspecialchars($_POST['team']);
            // обновяваме $currentTeamId за навигация, ако е нужно
            $currentTeamId   = $db->findTeamByName($_POST['team']);
        } else {
            $message     = 'Failed to join team. Please try again.';
            $messageType = 'error';
        }
    }
}
?>

<main class="team-container">
    <h1>Team Management</h1>
    
    <?php if ($currentTeamName): ?>
        <div class="current-team">
            <h2>Current Team: <?= htmlspecialchars($currentTeamName) ?></h2>
        </div>
    <?php endif; ?>
    
    <form method="post" class="team-form">
        <div class="form-group">
            <label for="team">Join Team:</label>
            <input type="text" id="team" name="team" required>
        </div>
        
        <button type="submit" class="submit-button">Join Team</button>
    </form>

    <?php if ($currentTeamName): ?>
        <form method="post" class="team-form quit-form">
            <input type="hidden" name="quit" value="1">
            <button type="submit" class="submit-button">Quit Team</button>
        </form>
    <?php endif; ?>
    
    <?php if ($message): ?>
        <div class="message <?= $messageType ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>
    
    <nav class="team-navigation">
        <a href="?page=game" class="nav-link">Start Game</a>
        <a href="?page=dashboard" class="nav-link">Back to Dashboard</a>
    </nav>
</main> 