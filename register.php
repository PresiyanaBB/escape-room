<?php
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO participants (email, username, password) VALUES (?, ?, ?)");
    if ($stmt->execute([$email, $username, $password])) {
        header("Location: login.php");
    } else {
        echo "Username already exists.";
    }
}
?>

<form method="post">
    Email: <input name="email"><br>
    Username: <input name="username"><br>
    Password: <input name="password" type="password"><br>
    <button type="submit">Register</button>
</form>
