<?php
require 'db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ?page=dashboard");
    exit;
}

function handleLogin($db, $username, $password) {
    $user = $db->findUserByUsername($username);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        return true;
    }
    return false;
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (handleLogin($db, $_POST['username'], $_POST['password'])) {
        header("Location: ?page=dashboard");
        exit;
    } else {
        $error = "Invalid credentials.";
    }
}
?>

<main class="login-container">
    <h1>Login</h1>
    
    <form method="post" class="login-form">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit" class="submit-button">Login</button>
    </form>
    
    <?php if ($error): ?>
        <div class="error-message">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    
    <p class="register-link">
        Don't have an account? <a href="?page=register">Register here</a>
    </p>
</main> 