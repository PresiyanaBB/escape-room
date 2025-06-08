<?php
require 'db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ?page=dashboard");
    exit;
}

function handleRegistration($db, $email, $username, $password) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    return $db->createUser($email, $username, $hashedPassword);
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (handleRegistration($db, $_POST['email'], $_POST['username'], $_POST['password'])) {
        header("Location: ?page=login");
        exit;
    } else {
        $error = "Username already exists.";
    }
}
?>

<main class="register-container">
    <h1>Register</h1>
    
    <form method="post" class="register-form">
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit" class="submit-button">Register</button>
    </form>
    
    <?php if ($error): ?>
        <div class="error-message">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    
    <p class="login-link">
        Already have an account? <a href="?page=login">Login here</a>
    </p>
</main> 