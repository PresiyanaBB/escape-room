<?php
require 'db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ?page=dashboard");
    exit;
}

function handleRegistration($db, $email, $username, $password) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $user_username = $db->findUserByUsername($username);
    if ($user_username) {
        return -1;
    }
    $user_email = $db->findUserByEmail($email);
    if ($user_email) {
        return -2;
    }
    return $db->createUser($email, $username, $hashedPassword);
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $result = handleRegistration($db, $_POST['email'], $_POST['username'], $_POST['password']);
    if ($result === -1) {
        $error = "Username already exists.";
    } else if ($result === -2) {
        $error = "Email already exists.";
    } else {
        header("Location: ?page=login");
        exit;
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