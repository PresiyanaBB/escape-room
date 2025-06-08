<?php
include 'includes/header.php';

$page = $_GET['page'] ?? 'dashboard';

switch ($page) {
    case 'login':
        include 'components/login_form.php';
        break;
    case 'register':
        include 'components/register_form.php';
        break;
    case 'game':
        include 'components/game_content.php';
        break;
    case 'leaderboard':
        include 'components/leaderboard_content.php';
        break;
    case 'team':
        include 'components/team_form.php';
        break;
    case 'logout':
        include 'components/logout_content.php';
        break;
    case 'settings':
        include 'components/settings_content.php';
        break;
    default:
        include 'components/dashboard_content.php';
}

include 'includes/footer.php';
?> 