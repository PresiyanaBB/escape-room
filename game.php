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
$timeForSolving = $room['time_for_solving']; 

$teamNameStmt = $pdo->prepare("SELECT name FROM teams WHERE id = ?");
$teamNameStmt->execute([$team_id]);
$teamName = $teamNameStmt->fetchColumn();

if (!isset($_SESSION['started_at'])) {
    $_SESSION['started_at'] = date("Y-m-d H:i:s");
}

list($h, $m, $s) = explode(':', $timeForSolving);
$durationSeconds = ($h * 3600) + ($m * 60) + $s;

$startTimestamp = strtotime($_SESSION['started_at']);
$endTimestamp = $startTimestamp + $durationSeconds;
$nowTimestamp = time();

if ($nowTimestamp > $endTimestamp) {
    echo "<h2>⏰ Time's up! The game is over.</h2>";
    session_destroy();
    exit;
}

$questionsStmt = $pdo->prepare("SELECT * FROM games WHERE room_id = ? ORDER BY id ASC");
$questionsStmt->execute([$room_id]);
$questions = $questionsStmt->fetchAll(PDO::FETCH_ASSOC);

$totalQuestions = count($questions);

if (!isset($_SESSION['current_question'])) {
    $_SESSION['current_question'] = 0;
}

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer'])) {
    $currentIndex = $_SESSION['current_question'];
    $currentQuestion = $questions[$currentIndex];

    if (strcasecmp(trim($_POST['answer']), trim($currentQuestion['answer'])) === 0) {
        $message = "✅ Correct answer!";
        $_SESSION['current_question']++;

        if ($_SESSION['current_question'] >= $totalQuestions) {
            $elapsedSeconds = $nowTimestamp - $startTimestamp;
            $elapsedTimeStr = gmdate("H:i:s", $elapsedSeconds);

            $checkStmt = $pdo->prepare("SELECT id FROM leaderboard WHERE room_id = ? AND team_name = ?");
            $checkStmt->execute([$room_id, $teamName]);
            $existing = $checkStmt->fetchColumn();

            if ($existing) {
                $updateStmt = $pdo->prepare("UPDATE leaderboard SET time = ? WHERE id = ?");
                $updateStmt->execute([$elapsedTimeStr, $existing]);
            } else {
                $insertStmt = $pdo->prepare("INSERT INTO leaderboard (room_id, team_name, time) VALUES (?, ?, ?)");
                $insertStmt->execute([$room_id, $teamName, $elapsedTimeStr]);
            }

            echo "<h2>🎉 Congratulations! You completed the game in $elapsedTimeStr.</h2>";

            session_destroy();
            exit;
        }
    } else {
        $message = "❌ Wrong answer, try again.";
    }
}

$currentIndex = $_SESSION['current_question'];
$currentQuestion = $questions[$currentIndex];

$remainingSeconds = $endTimestamp - $nowTimestamp;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Escape Room Game</title>
</head>
<body>
    <h1>Escape Room: <?= htmlspecialchars($room['name']) ?></h1>
    <h3>Team: <?= htmlspecialchars($teamName) ?></h3>
    <div>
        Time left: <span id="timer"></span>
    </div>

    <hr>

    <div id="question-container">
        <p><b>Question <?= $currentIndex + 1 ?> of <?= $totalQuestions ?>:</b></p>
        <p id="question-text"><?= htmlspecialchars($currentQuestion['question']) ?></p>

        <button id="showHintBtn">Show Hint</button>
        <p id="hint-text" style="display:none; font-style: italic; color: gray;"><?= htmlspecialchars($currentQuestion['hint']) ?></p>

        <form method="post" id="answerForm">
            <label for="answer">Your answer:</label>
            <input type="text" name="answer" id="answer" autocomplete="off" required autofocus>
            <button type="submit">Submit Answer</button>
        </form>
        <p id="message"><?= $message ?></p>
    </div>

    <script>
    let remaining = <?= $remainingSeconds ?>;
    const timerEl = document.getElementById('timer');
    function updateTimer() {
        if (remaining <= 0) {
            timerEl.textContent = "00:00:00";
            alert("⏰ Time's up! The game is over.");
            window.location.reload();
            return;
        }
        let h = Math.floor(remaining / 3600);
        let m = Math.floor((remaining % 3600) / 60);
        let s = remaining % 60;

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
    </script>
</body>
</html>
