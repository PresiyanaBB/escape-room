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
                    if (!isset($_FILES['games_file']) || $_FILES['games_file']['error'] !== UPLOAD_ERR_OK) {
                        throw new Exception("Please select a valid file to import");
                    }
                    require_once __DIR__ . '/../import/import-games.php';
                    $message = "Games imported successfully!";
                    break;
                case 'export_games':
                    header("Location: export/export-games.php");
                    exit;
                case 'export_teams':
                    header("Location: export/export-teams.php");
                    exit;
                case 'export_users':
                    header("Location: export/export-users.php");
                    exit;
                case 'export_leaderboard':
                    header("Location: export/export-leaderboard.php");
                    exit;
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
    
    <?php if (isset($_SESSION['import_message'])): ?>
        <div class="message success">
            <?= htmlspecialchars($_SESSION['import_message']) ?>
        </div>
        <?php unset($_SESSION['import_message']); ?>
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
                <form method="post" class="settings-form" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="import_games">
                    <div class="file-input-container">
                        <input type="file" name="games_file" accept=".json" required id="games-file" class="file-input">
                        <label for="games-file" class="file-input-label">
                            <span class="file-input-text">Choose JSON file</span>
                            <span class="file-input-button">Browse</span>
                        </label>
                    </div>
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

<style>
.file-input-container {
    margin-bottom: 1rem;
}

.file-input {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    border: 0;
}

.file-input-label {
    display: flex;
    align-items: center;
    width: 100%;
    padding: 0.5rem;
    background: var(--primary-color);
    border: 1px solid var(--secondary-color);
    border-radius: var(--border-radius);
    cursor: pointer;
    transition: all 0.3s ease;
}

.file-input-label:hover {
    background: var(--secondary-color);
    transform: translateY(-2px);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

.file-input-text {
    flex: 1;
    color: var(--text-color);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.file-input-button {
    padding: 0.375rem 0.75rem;
    background: var(--accent-color);
    color: var(--text-color);
    border-radius: var(--border-radius);
    margin-left: 0.5rem;
    font-size: 0.875rem;
    transition: all 0.3s ease;
}

.file-input-button:hover {
    background: #2980b9;
    transform: translateY(-2px);
}

/* Show selected filename */
.file-input:not(:placeholder-shown) + .file-input-label .file-input-text {
    color: var(--text-color);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('games-file');
    const fileText = fileInput.nextElementSibling.querySelector('.file-input-text');
    
    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            fileText.textContent = this.files[0].name;
        } else {
            fileText.textContent = 'Choose JSON file';
        }
    });
});
</script> 