<?php
// Destroy the session
session_destroy();

// Redirect to login page
header("Location: ?page=login");
exit;
?> 