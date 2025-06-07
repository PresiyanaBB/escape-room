<?php
$pdo = new PDO("mysql:host=localhost;dbname=escape_room;charset=utf8mb4", "escape_user", "escapepass");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>