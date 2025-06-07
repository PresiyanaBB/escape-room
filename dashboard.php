<?php
session_start();
if (!isset($_SESSION['user_id'])) header("Location: login.php");

echo "Welcome, " . $_SESSION['username'];
echo "<br><a href='team.php'>Join a Team</a>";
echo "<br><a href='game.php'>Start Game</a>";
echo "<br><a href='leaderboard.php'>View Leaderboard</a>";
?>
