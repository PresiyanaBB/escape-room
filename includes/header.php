<?php
session_start();
$isLogged = isset($_SESSION['user_id']);
$isAdmin = $isLogged && ($_SESSION['username'] === 'admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escape Room</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <header class="main-header">
        <nav class="main-nav">
            <div class="nav-brand">
                <a href="?page=dashboard">Escape Room</a>
            </div>
            <div class="nav-links">
                <?php if ($isLogged): ?>
                    <?php if ($isAdmin): ?>
                        <a href="?page=settings" class="nav-link">Settings</a>
                        <a href="?page=leaderboard" class="nav-link">Leaderboard</a>
                        <a href="?page=game" class="nav-link">Games</a>
                    <?php else: ?>
                        <a href="?page=dashboard" class="nav-link">Dashboard</a>
                        <a href="?page=game" class="nav-link">Games</a>
                        <a href="?page=leaderboard" class="nav-link">Leaderboard</a>
                        <a href="?page=team" class="nav-link">Team</a>
                    <?php endif; ?>
                    <a href="?page=logout" class="nav-link">Logout</a>
                <?php else: ?>
                    <a href="?page=login" class="nav-link">Login</a>
                    <a href="?page=register" class="nav-link">Register</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    <div class="content-wrapper"> 