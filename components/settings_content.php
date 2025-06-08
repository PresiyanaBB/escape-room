<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: ?page=login");
    exit;
}

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'import_games':
                    require_once __DIR__ . '/../import/import-games.php';
                    $message = "Games imported successfully!";
                    break;
                case 'export_games':
                    require_once __DIR__ . '/../export/export-games.php';
                    $message = "Games exported successfully!";
                    break;
                case 'export_teams':
                    require_once __DIR__ . '/../export/export-teams.php';
                    $message = "Teams exported successfully!";
                    break;
                case 'export_users':
                    require_once __DIR__ . '/../export/export-users.php';
                    $message = "Users exported successfully!";
                    break;
                case 'export_leaderboard':
                    require_once __DIR__ . '/../export/export-leaderboard.php';
                    $message = "Leaderboard exported successfully!";
                    break;
            }
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>

<main>
    <h1>Settings</h1>
    
    <?php if ($message): ?>
        <div class="message success">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="error-message">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="settings-section">
        <h2>Import/Export Data</h2>
        
        <div class="settings-grid">
            <div class="settings-card">
                <h3>Games</h3>
                <form method="post" class="settings-form">
                    <input type="hidden" name="action" value="import_games">
                    <button type="submit" class="submit-button">Import Games</button>
                </form>
                <form method="post" class="settings-form">
                    <input type="hidden" name="action" value="export_games">
                    <button type="submit" class="submit-button">Export Games</button>
                </form>
            </div>

            <div class="settings-card">
                <h3>Teams</h3>
                <form method="post" class="settings-form">
                    <input type="hidden" name="action" value="export_teams">
                    <button type="submit" class="submit-button">Export Teams</button>
                </form>
            </div>

            <div class="settings-card">
                <h3>Users</h3>
                <form method="post" class="settings-form">
                    <input type="hidden" name="action" value="export_users">
                    <button type="submit" class="submit-button">Export Users</button>
                </form>
            </div>

            <div class="settings-card">
                <h3>Leaderboard</h3>
                <form method="post" class="settings-form">
                    <input type="hidden" name="action" value="export_leaderboard">
                    <button type="submit" class="submit-button">Export Leaderboard</button>
                </form>
            </div>
        </div>
    </div>
</main> 