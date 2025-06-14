<?php
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ?page=login");
    exit;
}

$isAdmin = ($_SESSION['username'] === 'admin');

// Handle admin room removal
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_room_id'])) {
    $db->deleteRoom($_POST['remove_room_id']);     // трябва да имаш функция deleteRoom($id)
    header("Location: ?page=game");
    exit;
}

// Handle room selection first
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['room_id'])) {
    $_SESSION['selected_room_id'] = $_POST['room_id'];
    header("Location: ?page=game");
    exit;
}

// Handle quit game
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quit_game'])) {
    // Clear all game-related session variables
    unset($_SESSION['started_at']);
    unset($_SESSION['questions']);
    unset($_SESSION['total_questions']);
    unset($_SESSION['current_question']);
    unset($_SESSION['selected_room_id']);
    
    header("Location: ?page=game");
    exit;
}

function initializeGame($db, $user_id, $room_id) {
    $team_id = $db->getTeamForUser($user_id);
    if (!$team_id) {
        return ['error' => "Please join a team first."];
    }

    $room = $db->getRoomById($room_id);
    if (!$room) {
        return ['error' => "Room not found."];
    }

    $teamName = $db->getTeamName($team_id);
    return ['team_id' => $team_id, 'room' => $room, 'teamName' => $teamName];
}

function calculateTimeRemaining($timeForSolving, $startedAt) {
    list($h, $m, $s) = explode(':', $timeForSolving);
    $durationSeconds = ($h * 3600) + ($m * 60) + $s;
    
    $startTimestamp = strtotime($startedAt);
    $endTimestamp = $startTimestamp + $durationSeconds;
    $nowTimestamp = time();
    
    return [
        'remaining' => $endTimestamp - $nowTimestamp,
        'isExpired' => $nowTimestamp > $endTimestamp,
        'elapsed' => $nowTimestamp - $startTimestamp
    ];
}

function handleAnswer($db, $answer, $currentQuestion, $room_id, $team_id, $timeInfo) {
    if (strcasecmp(trim($answer), trim($currentQuestion['answer'])) === 0) {
        $_SESSION['current_question']++;
        
        if ($_SESSION['current_question'] >= $_SESSION['total_questions']) {
            $elapsedTimeStr = gmdate("H:i:s", $timeInfo['elapsed']);
            $db->updateLeaderboard($room_id, $team_id, $elapsedTimeStr);
            return [
                'message' => "Congratulations! You completed the game in $elapsedTimeStr.",
                'completed' => true
            ];
        }
        return ['message' => "Correct answer!", 'completed' => false];
    }
    return ['message' => "Wrong answer, try again.", 'completed' => false];
}

// Handle game selection
if (!isset($_SESSION['selected_room_id'])) {
    $rooms = $db->getAllRooms();
    ?>
    <main class="game-container">
        <header class="game-header">
            <h1>Select a Game</h1>
        </header>
        
        <div class="room-selection">
            <?php foreach ($rooms as $room): ?>
                <div class="room-card">
                    <h3><?= htmlspecialchars($room['name']) ?></h3>
                    <p>Steps: <?= htmlspecialchars($room['steps']) ?></p>
                    <p>Time: <?= htmlspecialchars($room['time_for_solving']) ?></p>
                    <?php if ($isAdmin): ?>
                        <form method="post" class="room-form">
                            <input type="hidden" name="remove_room_id" value="<?= $room['id'] ?>">
                            <button type="submit" class="submit-button danger"
                                onclick="return confirm('Delete this room permanently?');">
                                Remove Game
                            </button>
                        </form>
                    <?php else: ?>
                        <form method="post" class="room-form">
                            <input type="hidden" name="room_id" value="<?= $room['id'] ?>">
                            <button type="submit" class="submit-button">Start Game</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
    <?php
    exit;
}

// Initialize game state
$gameState = initializeGame($db, $_SESSION['user_id'], $_SESSION['selected_room_id']);
if (isset($gameState['error'])) {
    echo '<div class="error-message">' . htmlspecialchars($gameState['error']) . '</div>';
    exit;
}

$team_id = $gameState['team_id'];
$room = $gameState['room'];
$teamName = $gameState['teamName'];
$room_id = $room['id'];

// Initialize or get game session
if (!isset($_SESSION['started_at'])) {
    $_SESSION['started_at'] = date("Y-m-d H:i:s");
}

// Get questions if not already in session
if (!isset($_SESSION['questions'])) {
    $_SESSION['questions'] = $db->getQuestionsForRoom($room_id);
    $_SESSION['total_questions'] = count($_SESSION['questions']);
    $_SESSION['current_question'] = 0;
}

// Calculate time
$timeInfo = calculateTimeRemaining($room['time_for_solving'], $_SESSION['started_at']);

if ($timeInfo['isExpired']) {
    echo '<div class="game-over">Time\'s up! The game is over.</div>';
    // Clear only game-related session variables
    unset($_SESSION['started_at']);
    unset($_SESSION['questions']);
    unset($_SESSION['total_questions']);
    unset($_SESSION['current_question']);
    unset($_SESSION['selected_room_id']);
    header("Location: ?page=leaderboard");
    exit;
}

// Handle answer submission
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer'])) {
    $currentQuestion = $_SESSION['questions'][$_SESSION['current_question']];
    $result = handleAnswer($db, $_POST['answer'], $currentQuestion, $room_id, $team_id, $timeInfo);
    
    $message = $result['message'];
    if ($result['completed']) {
        // Store the congratulations message in session
        $_SESSION['congrats_message'] = $message;
        
        // Clear only game-related session variables
        unset($_SESSION['started_at']);
        unset($_SESSION['questions']);
        unset($_SESSION['total_questions']);
        unset($_SESSION['current_question']);
        unset($_SESSION['selected_room_id']);
        
        // Redirect immediately
        header("Location: ?page=leaderboard");
        exit;
    }
}

$currentQuestion = $_SESSION['questions'][$_SESSION['current_question']];
?>

<main class="game-container">
    <header class="game-header">
        <h1>Escape Room: <?= htmlspecialchars($room['name']) ?></h1>
        <h2>Team: <?= htmlspecialchars($teamName) ?></h2>
        <div class="timer-container">
            <span>Time left: </span>
            <span id="timer"></span>
        </div>
    </header>

    <section class="question-section">
        <div class="question-header">
            <h3>Question <?= $_SESSION['current_question'] + 1 ?> of <?= $_SESSION['total_questions'] ?></h3>
        </div>
        
        <div class="question-content">
            <p id="question-text"><?= htmlspecialchars($currentQuestion['question']) ?></p>

            <button id="showHintBtn" class="hint-button">Show Hint</button>
            <p id="hint-text" class="hint-text" style="display:none;"><?= htmlspecialchars($currentQuestion['hint']) ?></p>

            <form method="post" id="answerForm" class="answer-form">
                <label for="answer">Your answer:</label>
                <input type="text" name="answer" id="answer" autocomplete="off" required autofocus>
                <button type="submit">Submit Answer</button>
            </form>

            <form method="post" id="quitForm" class="quit-form">
                <input type="hidden" name="quit_game" value="1">
                <button type="button" id="quitButton" class="quit-button">Quit Game</button>
            </form>
            
            <?php if ($message): ?>
                <div id="message" class="notification-popup <?= 
                    strpos($message, 'Congratulations') !== false ? 'congrats' : 
                    (strpos($message, 'Correct') !== false ? 'success' : 'error') 
                ?>">
                    <div class="notification-content">
                        <span class="notification-icon">
                            <?= strpos($message, 'Congratulations') !== false ? '🎉' : 
                               (strpos($message, 'Correct') !== false ? '✓' : '✕') ?>
                        </span>
                        <span class="notification-text"><?= htmlspecialchars($message) ?></span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

</main>

<script>
let remaining = <?= $timeInfo['remaining'] ?>;
const timerEl = document.getElementById('timer');

function updateTimer() {
    if (remaining <= 0) {
        timerEl.textContent = "00:00:00";
        alert("Time's up! The game is over.");
        window.location.reload();
        return;
    }
    
    const h = Math.floor(remaining / 3600);
    const m = Math.floor((remaining % 3600) / 60);
    const s = remaining % 60;

    timerEl.textContent = 
        String(h).padStart(2, '0') + ":" +
        String(m).padStart(2, '0') + ":" +
        String(s).padStart(2, '0');

    remaining--;
}

updateTimer();
setInterval(updateTimer, 1000);

document.getElementById('showHintBtn').addEventListener('click', function() {
    document.getElementById('hint-text').style.display = 'block';
    this.style.display = 'none'; 
});

document.getElementById('answer').focus();

if (document.getElementById('message')) {
    setTimeout(() => {
        const message = document.getElementById('message');
        message.style.animation = 'slideOut 0.3s ease-out forwards';
        setTimeout(() => {
            message.remove();
        }, 300);
    }, 3000);
}

// Add quit game functionality
document.getElementById('quitButton').addEventListener('click', function() {
    if (confirm('Are you sure you want to quit the game? Your progress will not be saved.')) {
        document.getElementById('quitForm').submit();
    }
});
</script> 